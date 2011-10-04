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
  private $S3;
  
  public function __construct() {
    $this->S3 = new AmazonS3();
  }
  
  public function __call($name, $arguments) {
    /* rudementary, more methods accept bucket as first param, change to use reflection */
    if (strstr($name, 'createObject') || strstr($name, 'getObject')) {
      array_unshift($arguments, sfConfig::get('app_aws_bucket'));
    }

    return call_user_func_array(array($this->S3, sfInflector::underscore($name)), $arguments);
  }
}