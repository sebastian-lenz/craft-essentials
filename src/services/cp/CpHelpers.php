<?php

namespace lenz\craft\essentials\services\cp;

use Craft;
use craft\events\DefineFieldLayoutElementsEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterCpNavItemsEvent;
use craft\models\FieldLayout;
use craft\services\Utilities;
use craft\web\Application;
use craft\web\twig\variables\Cp;
use lenz\craft\essentials\Plugin;
use lenz\craft\essentials\services\AbstractService;
use yii\base\Event;
use yii\base\Module;

/**
 * Class CpHelpers
 */
class CpHelpers extends AbstractService
{
  /**
   * RemoveDashboard constructor.
   */
  public function __construct() {
    parent::__construct();

    Event::on(
      Cp::class,
      Cp::EVENT_REGISTER_CP_NAV_ITEMS,
      function(RegisterCpNavItemsEvent $event) {
        for ($index = 0; $index < count($event->navItems); $index++) {
          if ($event->navItems[$index]['url'] == 'dashboard') {
            array_splice($event->navItems, $index, 1);
            break;
          }
        }
      }
    );

    Event::on(
      Application::class,
      Module::EVENT_BEFORE_ACTION,
      function() {
        $request = Craft::$app->getRequest();
        if ($request->getSegment(1) == 'dashboard') {
          $entries = (new Cp())->nav();
          Craft::$app->getResponse()->redirect(reset($entries)['url']);
        }
      }
    );

    Event::on(
      Utilities::class,
      Utilities::EVENT_REGISTER_UTILITIES,
      function(RegisterComponentTypesEvent $event) {
        if (count(Plugin::getInstance()->getSettings()->iconClasses) > 0) {
          $event->types[] = IconUtility::class;
        }
      }
    );

    Event::on(
      FieldLayout::class,
      FieldLayout::EVENT_DEFINE_UI_ELEMENTS,
      function(DefineFieldLayoutElementsEvent $event) {
        $event->elements[] = elements\Column::class;
      }
    );
  }
}
