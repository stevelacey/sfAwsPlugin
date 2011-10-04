<?php

/**
 * sfAws main class
 * 
 * @package     plugins
 * @subpackage  sfAws
 * @author      Steve Lacey <steve@stevelacey.net>
 * @version     SVN: $Id$
 */
class sfAws {
  private $prefix = 'sfAmazon';
  private $services = array();
  
  public function __call($name, $arguments) {
    if (substr($name, 0, 3) == 'get') {
      $var = substr($name, 3);

      if (!isset($this->services[$var])) {
        $class = $this->prefix.$var;
        $this->services[$var] = new $class;
      }

      return $this->services[$var];
    }
  }
}