<?php

/**
 * s3ValidatorFile validates an uploaded file.
 *
 * @package    symfony
 * @subpackage validator
 * @author     Wired Media
 * @version    SVN: $Id: s3ValidatorFile.class.php 23951 2009-11-14 20:44:22Z FabianLange $
 */
class s3ValidatorFile extends sfValidatorFile {

  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * max_size:             The maximum file size in bytes (cannot exceed upload_max_filesize in php.ini)
   *  * mime_types:           Allowed mime types array or category (available categories: web_images)
   *  * mime_type_guessers:   An array of mime type guesser PHP callables (must return the mime type or null)
   *  * mime_categories:      An array of mime type categories (web_images is defined by default)
   *  * path:                 The path where to save the file - as used by the sfValidatedFile class (optional)
   *  * validated_file_class: Name of the class that manages the cleaned uploaded file (optional)
   *
   * There are 3 built-in mime type guessers:
   *
   *  * guessFromFileinfo:        Uses the finfo_open() function (from the Fileinfo PECL extension)
   *  * guessFromMimeContentType: Uses the mime_content_type() function (deprecated)
   *  * guessFromFileBinary:      Uses the file binary (only works on *nix system)
   *
   * Available error codes:
   *
   *  * max_size
   *  * mime_types
   *  * partial
   *  * no_tmp_dir
   *  * cant_write
   *  * extension
   *
   * @param array $options   An array of options
   * @param array $messages  An array of error messages
   *
   * @see sfValidatorBase
   */
  protected function configure($options = array(), $messages = array()) {
    parent::configure();
    
    $this->addOption('acl', AmazonS3::ACL_PRIVATE);
    $this->addOption('validated_file_class', 's3ValidatedFile');
  }

  protected function getMimeType($file, $fallback) {
    $guessers = $this->getOption('mime_type_guessers');
    
    for ($i = 0; $i < count($guessers) && !isset($guess); $i++) {
      $method = $guessers[$i];
      $type = call_user_func($method, $file);

      if (!empty($type)) {
        $guess = strtolower($type);
      }
    }
    
    $fallback = strtolower($fallback);
    
    // libmagic incorrectly returns mimetype for webm videos
    if ($fallback == 'video/webm') {
      $guess = $fallback;
    } else if (isset($guess)) {
      switch ($guess) {
        case 'application/octet-stream':
        case 'text/plain':
          $guess = 'video/webm';
      }
    }
    
    return isset($guess) ? $guess : $fallback;
  }

  /**
   * This validator always returns a sfValidatedFile object.
   *
   * The input value must be an array with the following keys:
   *
   *  * tmp_name: The absolute temporary path to the file
   *  * name:     The original file name (optional)
   *  * type:     The file content type (optional)
   *  * error:    The error code (optional)
   *  * size:     The file size in bytes (optional)
   *
   * @see sfValidatorBase
   */
  protected function doClean($value) {
    if (!is_array($value) || !isset($value['tmp_name'])) {
      throw new sfValidatorError($this, 'invalid', array('value' => (string) $value));
    }

    if (!isset($value['name'])) {
      $value['name'] = '';
    }

    if (!isset($value['error'])) {
      $value['error'] = UPLOAD_ERR_OK;
    }

    if (!isset($value['size'])) {
      $value['size'] = filesize($value['tmp_name']);
    }

    if (!isset($value['type'])) {
      $value['type'] = 'application/octet-stream';
    }

    switch ($value['error']) {
      case UPLOAD_ERR_INI_SIZE:
        $max = ini_get('upload_max_filesize');
        if ($this->getOption('max_size')) {
          $max = min($max, $this->getOption('max_size'));
        }
        throw new sfValidatorError($this, 'max_size', array('max_size' => $max, 'size' => (int) $value['size']));
      case UPLOAD_ERR_FORM_SIZE:
        throw new sfValidatorError($this, 'max_size', array('max_size' => 0, 'size' => (int) $value['size']));
      case UPLOAD_ERR_PARTIAL:
        throw new sfValidatorError($this, 'partial');
      case UPLOAD_ERR_NO_TMP_DIR:
        throw new sfValidatorError($this, 'no_tmp_dir');
      case UPLOAD_ERR_CANT_WRITE:
        throw new sfValidatorError($this, 'cant_write');
      case UPLOAD_ERR_EXTENSION:
        throw new sfValidatorError($this, 'extension');
    }

    // check file size
    if ($this->hasOption('max_size') && $this->getOption('max_size') < (int) $value['size']) {
      throw new sfValidatorError($this, 'max_size', array('max_size' => $this->getOption('max_size'), 'size' => (int) $value['size']));
    }

    $mimeType = $this->getMimeType((string) $value['tmp_name'], (string) $value['type']);

    // check mime type
    if ($this->hasOption('mime_types')) {
      $mimeTypes = is_array($this->getOption('mime_types')) ? $this->getOption('mime_types') : $this->getMimeTypesFromCategory($this->getOption('mime_types'));
      if (!in_array($mimeType, array_map('strtolower', $mimeTypes))) {
        throw new sfValidatorError($this, 'mime_types', array('mime_types' => $mimeTypes, 'mime_type' => $mimeType));
      }
    }

    $class = $this->getOption('validated_file_class');

    return new $class($value['name'], $mimeType, $value['tmp_name'], $value['size'], $this->getOption('path'), $this->getOption('acl'));
  }
}