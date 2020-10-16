<?php

namespace lenz\craft\essentials\services\imageCompressor\jobs;

use craft\queue\BaseJob;
use lenz\craft\essentials\services\imageCompressor\compressors\AbstractCompressor;

/**
 * Class CompressJob
 */
abstract class AbstractJob extends BaseJob
{
  /**
   * AbstractJob constructor.
   * @param array $config
   */
  public function __construct($config = []) {
    parent::__construct($config);

    if (empty($this->description)) {
      $name = pathinfo($this->getFileName(), PATHINFO_BASENAME);
      $this->description = "Compress `{$name}`";
    }
  }

  /**
   * @inheritDoc
   */
  public function execute($queue) {
    $fileName = $this->getFileName();
    $format = $this->getFormat();

    if (is_null($fileName) || is_null($format)) {
      return;
    }

    $compressors = AbstractCompressor::createCompressors();
    foreach ($compressors as $compressor) {
      if (
        $compressor->canCompress(mb_strtolower($format)) &&
        $compressor->compress($fileName)
      ) {
        break;
      }
    }
  }


  // Protected methods
  // -----------------

  /**
   * @return string|null
   */
  abstract protected function getFileName();

  /**
   * @return string|null
   */
  abstract protected function getFormat();
}
