<?php

namespace lenz\craft\essentials\services\imageCompressor\compressors;

/**
 * Class OptiPng
 */
class OptiPng extends AbstractCompressor
{
  /**
   * @var string|null
   */
  private $_command;


  /**
   * OptiPng constructor.
   */
  public function __construct() {
    $this->_command = array_key_exists('CMD_OPTIPNG', $_ENV)
      ? $_ENV['CMD_OPTIPNG']
      : null;
  }

  /**
   * @inheritDoc
   */
  function canCompress(string $format) {
    return !is_null($this->_command) && in_array($format, ['png']);
  }

  /**
   * @inheritDoc
   */
  function compress(string $fileName) {
    $result = shell_exec($cmd = escapeshellcmd(
      implode(' ', array_map('escapeshellarg', [
        $this->_command,
        $fileName,
      ]))
    ));

    return true;
  }
}
