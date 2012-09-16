# sfAwsPlugin

A basic Symfony1 plugin wrapping the AWS SDK for PHP.

All functionality from the SDK should be available via magic methods on the sf classes provided. Some methods have been overloaded to tie them directly to the Symfony config and remove unnecessary passing around of credentials.

With regards to S3 and CloudFront, the plugin loosely follows the convention that you will probably have 1 bucket and 1 distribution per environment.

If you're finding the overloads at all restrictive or want to wrap more of the AWS services, feel free to do so and submit a pull request, all that I ask is that backwards compatibility is maintained wherever possible.

## Example config

    all:
      aws:
        access_key:
        secret_key:
        canonical_user_id:
        canonical_user_id:
        cloudfront_private_key_id:
        cloudfront_private_key_pem: |
          -----BEGIN RSA PRIVATE KEY-----
          ...
          -----END RSA PRIVATE KEY-----

        cfurl_default_validity: 1200 # 20 mins
        cfurl_use_https: false

        bucket_private_folders:
          - video

    live:
      aws:
        bucket:
        distribution_id:
        distribution_domain: xxxxxx.cloudfront.net
        cloudfront_origin_access_identity_id:
        cloudfront_origin_access_identity_canonical_user:

## Example usage

    $aws = sfContext::getInstance()->getAWS();

    $cloudfront = $aws->getCloudfront();

    $cloudfront->getPrivateObjectUrl(...);
    $cloudfront->createInvalidation($paths);