<?php

namespace lenz\craft\essentials\services\disabledLanguages;

use Craft;
use craft\base\Element;
use craft\elements\Entry;
use craft\events\SetElementRouteEvent;
use lenz\craft\essentials\Plugin;
use lenz\craft\essentials\services\eventBus\On;
use yii\base\Component;
use yii\base\Event;
use yii\web\NotFoundHttpException;

/**
 * Class DisableLanguages
 */
class DisabledLanguages extends Component
{
  /**
   * Languages constructor.
   */
  public function __construct() {
    parent::__construct();

    if ($this->hasDisabledLanguages()) {
      Event::on(
        Element::class,
        Element::EVENT_SET_ROUTE,
        [$this, 'onElementSetRoute']
      );
    }
  }

  /**
   * @return string[]
   */
  public function getDisabledLanguages(): array {
    return Plugin::getInstance()
      ->getSettings()
      ->disabledLanguages;
  }

  /**
   * @return bool
   */
  public function hasDisabledLanguages(): bool {
    return count($this->getDisabledLanguages()) > 0;
  }

  /**
   * @param string $language
   * @return bool
   */
  public function isLanguageDisabled(string $language): bool {
    return in_array($language, $this->getDisabledLanguages());
  }

  /**
   * @param SetElementRouteEvent $event
   * @throws NotFoundHttpException
   */
  #[On(Element::class, Element::EVENT_SET_ROUTE, [self::class, 'requiresHandler'])]
  public function onElementSetRoute(SetElementRouteEvent $event): void {
    /** @var Entry $entry */
    $entry = $event->sender;

    if (
      $this->isLanguageDisabled($entry->site->language) &&
      Craft::$app->getUser()->isGuest
    ) {
      throw new NotFoundHttpException();
    }
  }


  // Static methods
  // --------------

  /**
   * @return DisabledLanguages
   */
  public static function getInstance(): DisabledLanguages {
    return Plugin::getInstance()->disabledLanguages;
  }

  /**
   * @return bool
   */
  static public function requiresHandler(): bool {
    return self::getInstance()->hasDisabledLanguages();
  }
}
