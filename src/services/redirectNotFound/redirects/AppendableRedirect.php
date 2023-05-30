<?php

namespace lenz\craft\essentials\services\redirectNotFound\redirects;

use craft\base\ElementInterface;

/**
 * Interface AppendableRedirect
 */
interface AppendableRedirect
{
  /**
   * @param string $origin
   * @param ElementInterface $target
   * @return void
   */
  public function append(string $origin, ElementInterface $target): void;
}
