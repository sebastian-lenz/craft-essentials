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
  private mixed $_command;


  /**
   * OptiPng constructor.
   */
  public function __construct() {
    $this->_command = $_ENV['CMD_OPTIPNG'] ?? null;
  }

  /**
   * @inheritDoc
   */
  function canCompress(string $format): bool {
    return !is_null($this->_command) && $format == 'png';
  }

  /**
   * @inheritDoc
   */
  function compress(string $fileName): bool {
    shell_exec(escapeshellcmd(
      implode(' ', array_map('escapeshellarg', [
        $this->_command,
        $fileName,
      ]))
    ));

    return true;
  }
}
