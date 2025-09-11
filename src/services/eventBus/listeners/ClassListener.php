<?php

namespace lenz\craft\essentials\services\eventBus\listeners;

use lenz\craft\essentials\services\eventBus\On;
use yii\base\Event;

/**
 * Class ClassListener
 */
readonly class ClassListener extends AbstractListener
{
  /**
   * @param On $decorator
   * @param string $className
   */
  public function __construct(
    On $decorator,
    public string $className
  ) {
    parent::__construct($decorator);
  }

  /**
   * @inheritDoc
   */
  public function register(): void {
    Event::on($this->class, $this->name, $this->className, $this->data, $this->append);
  }

  /**
   * @inheritDoc
   */
  public function toCode(): array {
    $className = $this->className;

    return [
      $this->writeOnCall("$className::class"),
    ];
  }
}
