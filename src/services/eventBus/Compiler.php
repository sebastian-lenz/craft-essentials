<?php

namespace lenz\craft\essentials\services\eventBus;

/**
 * Class Compiler
 */
class Compiler
{
  /**
   * @param listeners\AbstractListener[] $listeners
   * @return string
   */
  static public function compile(array $listeners): string {
    $lines = [
      '<?php',
      '',
      'use lenz\craft\essentials\services\eventBus\EventBus;',
      'use yii\base\Event;',
      ''
    ];

    foreach ($listeners as $listener) {
      array_push($lines, ...$listener->toCode());
    }

    return implode("\n", $lines);
  }
}
