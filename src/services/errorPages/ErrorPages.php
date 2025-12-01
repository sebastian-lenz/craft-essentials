<?php

namespace lenz\craft\essentials\services\errorPages;

use craft\elements\Entry;
use craft\events\ElementEvent;
use craft\services\Elements;
use lenz\craft\essentials\Plugin;
use lenz\craft\essentials\services\eventBus\On;
use yii\base\Event;

/**
 * Class ErrorPages
 */
class ErrorPages
{
  const TYPE_NONE = 'none';
  const TYPE_ERROR = 'error';
  const TYPE_DEPENDENCY = 'dependency';

  const EVENT_FIND_PROCESSING_TYPE = 'findProcessingType';


  /**
   * @param Entry $element
   * @return string
   */
  static function getProcessingType(Entry $element): string {
    $type = self::TYPE_NONE;
    if (in_array($element->slug, Plugin::getInstance()->getSettings()->errorSlugs)) {
      $type = self::TYPE_ERROR;
    } elseif ($element->getSection()->type == \craft\models\Section::TYPE_SINGLE) {
      $type = self::TYPE_DEPENDENCY;
    }

    if (Event::hasHandlers(self::class, self::EVENT_FIND_PROCESSING_TYPE)) {
      $event = new ElementEvent([
        'data' => $type,
        'element' => $element,
      ]);

      Event::trigger(self::class, self::EVENT_FIND_PROCESSING_TYPE, $event);
      $type = $event->data;
    }

    return $type;
  }

  /**
   * @param ElementEvent $event
   * @return void
   */
  #[On(Elements::class, Elements::EVENT_AFTER_SAVE_ELEMENT, [self::class, 'requiresHandler'])]
  static function onAfterElementSave(ElementEvent $event): void {
    $element = $event->element;
    if (
      $element->propagating ||
      $element->getIsDraft() ||
      !$element->getIsCanonical() ||
      !($element instanceof Entry)
    ) {
      return;
    }

    match (self::getProcessingType($element)) {
      self::TYPE_ERROR => \Craft::$app->getQueue()->push(new ExportErrorPageJob([
        'elementId' => $element->id,
        'siteId' => $element->siteId,
      ])),
      self::TYPE_DEPENDENCY => \Craft::$app->getQueue()->push(new ExportErrorPageJob([
        'siteId' => $element->siteId,
      ])),
      default => null,
    };
  }

  /**
   * @return bool
   */
  static function requiresHandler(): bool {
    $settings = Plugin::getInstance()->getSettings();
    return count($settings->errorSlugs) > 0;
  }
}
