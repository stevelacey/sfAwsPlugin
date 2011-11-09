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
  private $_bucket;
  private $_acl;
  
  public function __construct($params) {
    $this->_S3     = new AmazonS3($params['access_key'], $params['secret_key']);
    $this->_bucket = $params['bucket'];
    $this->_acl = $params['acl'];
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
  
  public function createObject($file, $opts) {
    $default_opts = array( 'acl' => $this->_acl );
    if (isset($opts['bucket'])) {
      $bucket = $opts['bucket'];
      unset($opts['bucket']);
    } else {
      $bucket = $this->_bucket;
    } 
    $opts = array_merge($default_opts, $opts);
    return $this->_S3->create_object($bucket, $file, $opts);
  }
  
  public function validateAcl($file, $acl = false) {
    if (!$acl) {
      $acl = $this->_acl;
    }
    $current_acl = $this->getObjectAcl($file);
    foreach ($current_acl->body->AccessControlList->Grant as $current_grant) {
      $grantee = (string)$current_grant->Grantee->ID;
      $perm = (string)$current_grant->Permission;
      foreach ($acl as $key=>$value) {
        if ($value['id'] == $grantee && $value['permission'] == $perm) {
          unset($acl[$key]);
        }
      }
    }
    return empty($acl);
  }
  
  
}