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
  private $prefix = 'Amazon';
  private $S3;
  
  public function __call($name, $arguments) {
    if (substr($name, 0, 3) == 'get') {
      $var = substr($name, 3);
      
      if (!$this->$var) {
        $class = $this->prefix.$var;
        $this->$var = new $class;
      }
      
      return $this->$var;
    }
  }
}