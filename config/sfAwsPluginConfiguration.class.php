<?php

/**
 * sfAmazonS3Plugin configuration.
 * 
 * @package     sfAmazonS3Plugin
 * @subpackage  config
 * @author      Joshua Estes <Joshua.Estes@ScenicCityLabs.com>
 * @version     SVN: $Id: PluginConfiguration.class.php 17207 2009-04-10 15:36:26Z Kris.Wallsmith $
 */
class sfAwsPluginConfiguration extends sfPluginConfiguration {
  const VERSION = '1.0.0-DEV';

  /**
   * @see sfPluginConfiguration
   */
  public function initialize() {
    /**
     * Amazon Web Services Key. Found in the AWS Security Credentials. You can also pass this value as the first
     * parameter to a service constructor.
     */
    defined('AWS_KEY') || define('AWS_KEY', sfConfig::get('app_aws_access_key'));

    /**
     * Amazon Web Services Secret Key. Found in the AWS Security Credentials. You can also pass this value as
     * the second parameter to a service constructor.
     */
    defined('AWS_SECRET_KEY') || define('AWS_SECRET_KEY', sfConfig::get('app_aws_secret_key'));

    /**
     * Amazon Account ID without dashes. Used for identification with Amazon EC2. Found in the AWS Security
     * Credentials.
     */
    defined('AWS_ACCOUNT_ID') || define('AWS_ACCOUNT_ID', '');

    /**
     * Your CanonicalUser ID. Used for setting access control settings in AmazonS3. Found in the AWS Security
     * Credentials.
     */
    defined('AWS_CANONICAL_ID') || define('AWS_CANONICAL_ID', '');

    /**
     * Your CanonicalUser DisplayName. Used for setting access control settings in AmazonS3. Found in the AWS
     * Security Credentials (i.e. "Welcome, AWS_CANONICAL_NAME").
     */
    defined('AWS_CANONICAL_NAME') || define('AWS_CANONICAL_NAME', '');

    /**
     * Determines which Cerificate Authority file to use.
     *
     * A value of boolean `false` will use the Certificate Authority file available on the system. A value of
     * boolean `true` will use the Certificate Authority provided by the SDK. Passing a file system path to a
     * Certificate Authority file (chmodded to `0755`) will use that.
     *
     * Leave this set to `false` if you're not sure.
     */
    defined('AWS_CERTIFICATE_AUTHORITY') || define('AWS_CERTIFICATE_AUTHORITY', false);

    /**
     * This option allows you to configure a preferred storage type to use for caching by default. This can
     * be changed later using the set_cache_config() method.
     *
     * Valid values are: `apc`, `xcache`, a DSN-style string such as `pdo.sqlite:/sqlite/cache.db`, a file
     * system path such as `./cache` or `/tmp/cache/`, or a serialized array for memcached configuration.
     *
     * serialize(array(
     * 	array(
     * 		'host' => '127.0.0.1',
     * 		'port' => '11211'
     * 	),
     * 	array(
     * 		'host' => '127.0.0.2',
     * 		'port' => '11211'
     * 	)
     * ));
     */
    defined('AWS_DEFAULT_CACHE_CONFIG') || define('AWS_DEFAULT_CACHE_CONFIG', '');

    /**
     * 12-digit serial number taken from the Gemalto device used for Multi-Factor Authentication. Ignore this
     * if you're not using MFA.
     */
    defined('AWS_MFA_SERIAL') || define('AWS_MFA_SERIAL', '');

    /**
     * Amazon CloudFront key-pair to use for signing private URLs. Found in the AWS Security Credentials. This
     * can be set programmatically with <AmazonCloudFront::set_keypair_id()>.
     */
    defined('AWS_CLOUDFRONT_KEYPAIR_ID') || define('AWS_CLOUDFRONT_KEYPAIR_ID', '');

    /**
     * The contents of the *.pem private key that matches with the CloudFront key-pair ID. Found in the AWS
     * Security Credentials. This can be set programmatically with <AmazonCloudFront::set_private_key()>.
     */
    defined('AWS_CLOUDFRONT_PRIVATE_KEY_PEM') || define('AWS_CLOUDFRONT_PRIVATE_KEY_PEM', '');

    /**
     * Set the value to true to enable autoloading for classes not prefixed with "Amazon" or "CF". If enabled,
     * load `sdk.class.php` last to avoid clobbering any other autoloaders.
     */
    defined('AWS_ENABLE_EXTENSIONS') || define('AWS_ENABLE_EXTENSIONS', 'false');
    
    $this->dispatcher->connect('context.load_factories', array($this, 'configureAmazonS3'));
  }

  public function configureAmazonS3(sfEvent $event) {
    $event->getSubject()->setS3(new AmazonS3);
  }
}