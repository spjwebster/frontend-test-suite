<?php

require_once('TheCodeTrainBaseValidator.php');

/**
 * Validates HTML.  Full HTML documents are validated against whatever
 * doctype they are sent with, whereas HTML chunks are validated against
 * HTML 4.01 Strict.
 *
 * @author  Neil Crosby <neil@neilcrosby.com>
 * @license Creative Commons Attribution-Share Alike 3.0 Unported 
 *          http://creativecommons.org/licenses/by-sa/3.0/
 **/
class TheCodeTrainHtmlValidator extends TheCodeTrainBaseValidator {
    
    const HTML_DOCUMENT = 5;
    const HTML_CHUNK    = 10;
    
    const POSITION_BODY = 0;
    const POSITION_HEAD = 1;
    
    protected $errorPointer = array('envBody', 'mmarkupvalidationresponse', 'mresult', 'merrors');

    /**
     * Validates an HTML chunk or full document.
     *
     * @param html Some HTML to validate.
     * @param type Either HTML_DOCUMENT or HTML_CHUNK
     *
     * @return boolean, or NO_VALIDATOR_RESPONSE if the chosen validator was
     *         not able to be reached.
     **/
    public function isValid($html, $aOptions = array()) {
        $doctype  = isset($aOptions['doctype']) ? $aOptions['doctype'] : null;
        $section  = isset($aOptions['document_section']) ? $aOptions['document_section'] : null;
        $position = isset($aOptions['document_section_position']) ? $aOptions['document_section_position'] : TheCodeTrainHtmlValidator::POSITION_BODY;

        if ( self::FILE_IDENTIFIER == mb_substr( $html, 0, mb_strlen(self::FILE_IDENTIFIER)) ) {
            // load from file instead of just using the given string
            $file = mb_substr( $html, mb_strlen(self::FILE_IDENTIFIER));
            $html = file_get_contents($file);
        }
        
        if ( TheCodeTrainHtmlValidator::HTML_CHUNK == $section ) {
            if ( TheCodeTrainHtmlValidator::POSITION_HEAD == $position ) {
                $html = <<< HTML
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
$html
</head>
<body>
<p>Empty body</p>
</body></html>
HTML;
            } else {
                $html = <<< HTML
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head><title>title</title></head>
<body>
$html
</body></html>
HTML;
            }
        }
        
        $result = $this->getCurlResponse(
            $this->validationUrl,
            array('post'=>array('fragment' => $html,'output' => 'soap12'))
        );

        $this->lastResult = $result;

        if ( !$result ) {
            return self::NO_VALIDATOR_RESPONSE;
        }
        
        if (strpos( $result, "<m:validity>true</m:validity>" )) {
            return true;
        }
        
        return false;
    }
}