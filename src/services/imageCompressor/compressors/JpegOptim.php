<?php

namespace lenz\craft\essentials\services\imageCompressor\compressors;

/**
 * Class JpegOptim
 */
class JpegOptim extends AbstractCompressor
{
  /**
   * @var string|null
   */
  private mixed $_command;


  /**
   * JpegOptim constructor.
   */
  public function __construct() {
    $this->_command = $_ENV['CMD_JPEGOPTIM'] ?? null;
  }

  /**
   * @inheritDoc
   */
  function canCompress(string $format): bool {
    return !is_null($this->_command) && in_array($format, ['jpg', 'jpeg']);
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
