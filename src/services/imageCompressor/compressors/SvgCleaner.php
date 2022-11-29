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
  private mixed $_command;


  /**
   * SvgCleaner constructor.
   */
  public function __construct() {
    $this->_command = $_ENV['CMD_SVGCLEANER'] ?? null;
  }

  /**
   * @inheritDoc
   */
  function canCompress(string $format): bool {
    return !is_null($this->_command) && $format == 'svg';
  }

  /**
   * @inheritDoc
   */
  function compress(string $fileName): bool {
    shell_exec(escapeshellcmd(
      implode(' ', array_map('escapeshellarg', [
        $this->_command,
        $fileName,
        $fileName,
      ]))
    ));

    return true;
  }
}
