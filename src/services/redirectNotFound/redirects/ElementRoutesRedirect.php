<?php

namespace lenz\craft\essentials\services\redirectNotFound\redirects;

use craft\base\ElementInterface;
use lenz\craft\essentials\services\redirectNotFound\utils\ElementRoute;

/**
 * Interface ElementRoutesRedirect
 */
interface ElementRoutesRedirect
{
  /**
   * @param ElementRoute $route
   * @return void
   */
  public function delete(ElementRoute $route): void;

  /**
   * @param ElementInterface $element
   * @return ElementRoute[]
   */
  public function getElementRoutes(ElementInterface $element): array;
}
