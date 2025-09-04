<?php

namespace lenz\craft\essentials\services\eventBus\listeners;

use lenz\craft\essentials\services\eventBus\On;
use ReflectionAttribute;

/**
 * Class AbstractListener
 */
abstract readonly class AbstractListener
{
  /**
   * @var string
   */
  public string $class;

  /**
   * @var string
   */
  public string $name;


  /**
   * @param On $decorator
   */
  public function __construct(On $decorator) {
    $this->class = $decorator->class;
    $this->name = $decorator->name;
  }

  /**
   * @return void
   */
  abstract public function register(): void;

  /**
   * @return string[]
   */
  abstract public function toCode(): array;
}
