<?php

/**
 * sfAmazonS3 class
 * 
 * @package     sfAwsPlugin
 * @subpackage  service
 * @author      Steve Lacey <steve@stevelacey.net>
 * @version     SVN: $Id$
 */
class sfAmazonCloudfront {
  private $_cloudfront;
  private $_remote_ip;
  private $_default_validity;
  private $_https;
  
  public function __construct($params) {
    $this->_cloudfront        = new AmazonCloudFront($params['access_key'], $params['secret_key']);
    $this->_remote_ip        = $params['remote_ip'];
    $this->_default_validity = $params['default_validity']; //sfConfig::get('app_aws_cfurl_default_validity');
    $this->_https            = $params['use_https']; //sfConfig::get('app_aws_cfurl_use_https');
    $this->_domain         = $params['distribution_domain'];
  }
  
  public function __call($name, $arguments) {
    $method = sfInflector::underscore($name);
    return call_user_func_array(array($this->_cloudfront, $method), $arguments);
  }
  
  public function getPrivateObjectUrl($filename, $expires = false, $opts = array()) {
    if (!$expires) {
      if (isset($opts['validity'])) {
        $expires = time() + $opts['validity'];
      } else {
        $expires = time() + $this->_default_validity;
      } 
    }
    if (isset($opts['distribution_domain'])) {
      $domain = $opts['distribution_domain'];
      unset($opts['distribution_domain']);
    } else {
      $domain = $this->_domain;
    }
    unset($opts['validity']);
    $default_opts = array(
      'IPAddress' => $this->_remote_ip,
      'Secure' => $this->_https,
    );
    $opts = array_merge($default_opts, $opts);
    
    // fix ip for local dev
    if (isset($opts['IPAddress']) && strpos($opts['IPAddress'], '192.168') !== false) {
      unset($opts['IPAddress']);
    }    
    return $this->_cloudfront->get_private_object_url($domain, $filename, $expires, $opts);
  }
  
}