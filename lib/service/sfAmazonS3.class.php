<?php

/**
 * sfAmazonS3 class
 * 
 * @package     sfAwsPlugin
 * @subpackage  service
 * @author      Steve Lacey <steve@stevelacey.net>
 * @version     SVN: $Id$
 */
class sfAmazonS3 {
  private $_S3;
  private $_params;
  private $_bucket;
  
  public function __construct($params) {
    $this->_S3     = new AmazonS3($params['access_key'], $params['secret_key']);
    $this->_params = $params;
    $this->_bucket = $params['bucket'];
  }
  
  public function __call($name, $arguments) {
    $method = sfInflector::underscore($name);

    $reflection = new ReflectionMethod(get_class($this->_S3), $method);

    if (count($arguments) < $reflection->getNumberOfParameters()) {
      if (current($reflection->getParameters())->name == 'bucket') {
        array_unshift($arguments, $this->_bucket);
      }
    }

    return call_user_func_array(array($this->_S3, $method), $arguments);
  }
}