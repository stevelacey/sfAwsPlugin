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
  
  public function createObject($file, $options) {
    if (array_key_exists('acl', $options)) {
      if ($options['acl'] == AmazonS3::ACL_PRIVATE) {
        $acl = $this->_acl;
      } else {
        $acl = $options['acl'];
      }
      
      unset($options['acl']);
    } else {
      $acl = AmazonS3::ACL_PUBLIC;
    }
    
    if (array_key_exists('bucket', $options)) {
      $bucket = $options['bucket'];
      
      unset($options['bucket']);
    } else {
      $bucket = $this->_bucket;
    }
    
    $response = $this->_S3->create_object($bucket, $file, $options);
    
    if ($response->isOK()) {
      $this->setObjectAcl($file, $acl);
    }
    
    return $response;
  }
  
  public function validateAcl($file, $acl = false) {
    if (!$acl) {
      $acl = $this->_acl;
    }
    
    $current_acl = $this->getObjectAcl($file);
    
    foreach ($current_acl->body->AccessControlList->Grant as $grant) {
      $grantee = (string) $grant->Grantee->ID;
      $permission = (string) $grant->Permission;
      
      foreach ($acl as $key => $value) {
        if ($value['id'] == $grantee && $value['permission'] == $permission) {
          unset($acl[$key]);
        }
      }
    }
    
    return empty($acl);
  }
}