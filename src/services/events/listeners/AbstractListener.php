<?php

namespace lenz\craft\essentials\services\events\listeners;

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
   * @param ReflectionAttribute $attribute
   */
  public function __construct(ReflectionAttribute $attribute) {
    [$class, $name] = $attribute->getArguments();
    $this->class = $class;
    $this->name = $name;
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
