<?php

namespace lenz\craft\essentials\services\eventBus\listeners;

use lenz\craft\essentials\services\eventBus\On;

/**
 * Class AbstractListener
 */
abstract readonly class AbstractListener
{
  /**
   * @var bool
   */
  public bool $append;

  /**
   * @var string
   */
  public string $class;

  /**
   * @var mixed
   */
  public mixed $data;

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
    $this->data = $decorator->data;
    $this->append = $decorator->append;
  }

  /**
   * @return void
   */
  abstract public function register(): void;

  /**
   * @return string[]
   */
  abstract public function toCode(): array;


  // Protected methods
  // -----------------

  /**
   * @param string $handler
   * @return string
   */
  protected function writeOnCall(string $handler): string {
    $args = [
      var_export($this->class, true),
      var_export($this->name, true),
      $handler
    ];

    if (!is_null($this->data) || $this->append !== true) {
      $args[] = var_export($this->data, true);
    }

    if ($this->append !== true) {
      $args[] = var_export($this->append, true);
    }

    return 'Event::on(' . implode(', ', $args) . ');';
  }
}
