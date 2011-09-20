<?php

require_once('SOAP/Disco.php');

class Maarch_SOAP_DISCO_Server extends SOAP_DISCO_Server
{
    public function __construct() 
    {
        call_user_func_array(array(parent, 'SOAP_DISCO_Server'), 
                             func_get_args());
        $this->host = array_key_exists('HTTP_X_FORWARDED_HOST', $_SERVER) 
                           ? $_SERVER['HTTP_X_FORWARDED_HOST']
                           : $_SERVER['HTTP_HOST'];
    }
    
    private function selfUrl()
    {
        $rootUri = self::_getRootUri();
        $protocol = (array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] == 'on')
            ? 'https://' : 'http://' ;
        $selfFilename = basename(__file__);
        return $protocol . $this->host . $rootUri;
    }
    
    private static function _getRootUri()
    {
        $rootUri = array_key_exists('HTTP_X_BASE_URL', $_SERVER)
            ? str_replace($_SERVER['HTTP_X_BASE_URL'], '', 
                          $_SERVER['SCRIPT_NAME'])
            : $_SERVER['SCRIPT_NAME'];
        $endPos = strlen($rootUri) - strrpos($rootUri, '/');
        $rootUri = substr($rootUri, 0, $endPos);
        return $rootUri . '.php';
    }
    
    public function _generate_WSDL()
    {
        parent::_generate_WSDL();

        $this->_wsdl['definitions']['service']['port']['soap:address']['attr']['location'] = 
            $this->selfUrl();
        
        $this->_generate_WSDL_XML();
    }
    
    public function _generate_DISCO()
    {
        parent::_generate_DISCO();
        
        $this->_disco['disco:discovery']['scl:contractRef']['attr']['ref'] =
            $this->selfUrl() . '?wsdl';

        // generate disco xml
        $this->_generate_DISCO_XML($this->_disco);
    }
}
