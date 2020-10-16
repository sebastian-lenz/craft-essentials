<?php

namespace lenz\craft\essentials\services\imageCompressor\compressors;

/**
 * Class SvgCleaner
 */
class SvgCleaner extends AbstractCompressor
{
  /**
   * @var string|null
   */
  private $_command;


  /**
   * OptiPng constructor.
   */
  public function __construct() {
    $this->_command = array_key_exists('CMD_SVGCLEANER', $_ENV)
      ? $_ENV['CMD_SVGCLEANER']
      : null;
  }

  /**
   * @inheritDoc
   */
  function canCompress(string $format) {
    return !is_null($this->_command) && in_array($format, ['svg']);
  }

  /**
   * @inheritDoc
   */
  function compress(string $fileName) {
    shell_exec($cmd = escapeshellcmd(
      implode(' ', array_map('escapeshellarg', [
        $this->_command,
        $fileName,
        $fileName,
      ]))
    ));

    return true;
  }
}
