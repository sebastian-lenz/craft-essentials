<?php

namespace lenz\craft\essentials\services\events;

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
      'use lenz\craft\essentials\services\events\Events;',
      'use yii\base\Event;',
      ''
    ];



    foreach ($listeners as $listener) {
      array_push($lines, ...$listener->toCode());
    }

    return implode("\n", $lines);
  }
}
