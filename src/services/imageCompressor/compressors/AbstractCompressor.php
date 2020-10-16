<?php

namespace lenz\craft\essentials\services\imageCompressor\compressors;

/**
 * Class AbstractCompressor
 */
abstract class AbstractCompressor
{
  /**
   * 'jpg', 'jpeg', 'gif', 'png', 'svg', 'webp'
   * @param string $format
   * @return bool
   */
  abstract function canCompress(string $format);

  /**
   * @param string $fileName
   * @return bool
   */
  abstract function compress(string $fileName);


  // Static methods
  // --------------

  /**
   * @return array
   */
  static public function createCompressors() {
    return [
      new JpegOptim(),
      new OptiPng(),
      new SvgCleaner(),
    ];
  }
}
