<?php

/**
 * sfAws main class
 * 
 * @package     sfAwsPlugin
 * @subpackage lib
 * @author      Steve Lacey <steve@stevelacey.net>
 * @version     SVN: $Id$
 */
class sfAws {
  private $_prefix         = 'sfAmazon';
  private $_services       = array();
  private $_service_params;

  
  public function __construct($service_params = array()) {
    $this->_service_params = $service_params;
  }
  
  public function __call($name, $arguments) {
    if (substr($name, 0, 3) == 'get') {
      $var = substr($name, 3);

      if (!isset($this->_services[$var])) {
        $class = $this->_prefix.$var;
        $this->_services[$var] = new $class($this->_service_params);
      }

      return $this->_services[$var];
    }
  }
}