<?php

namespace lenz\craft\essentials\services\cp;

use Craft;
use craft\events\DefineFieldLayoutElementsEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterCpNavItemsEvent;
use craft\models\FieldLayout;
use craft\services\Fields;
use craft\services\Utilities;
use craft\web\Application;
use craft\web\twig\variables\Cp;
use lenz\craft\essentials\fields\seo\SeoField;
use lenz\craft\essentials\Plugin;
use lenz\craft\essentials\services\eventBus\On;
use yii\base\Module;

/**
 * Class CpHelpers
 */
class CpHelpers
{
  /**
   * @return void
   */
  #[On(Application::class, Module::EVENT_BEFORE_ACTION)]
  static public function onBeforeAction(): void {
    $request = Craft::$app->getRequest();

    if ($request->getSegment(1) == 'dashboard') {
      $entries = (new Cp())->nav();
      Craft::$app->getResponse()->redirect(reset($entries)['url']);
    }
  }

  /**
   * @param DefineFieldLayoutElementsEvent $event
   * @return void
   */
  #[On(FieldLayout::class, FieldLayout::EVENT_DEFINE_UI_ELEMENTS)]
  static public function onDefineUiElements(DefineFieldLayoutElementsEvent $event): void {
    $event->elements[] = elements\Column::class;
  }

  /**
   * @param RegisterCpNavItemsEvent $event
   * @return void
   */
  #[On(Cp::class, Cp::EVENT_REGISTER_CP_NAV_ITEMS)]
  static public function onRegisterCpNavItems(RegisterCpNavItemsEvent $event): void {
    for ($index = 0; $index < count($event->navItems); $index++) {
      if ($event->navItems[$index]['url'] == 'dashboard') {
        array_splice($event->navItems, $index, 1);
        break;
      }
    }
  }

  /**
   * @param RegisterComponentTypesEvent $event
   * @return void
   */
  #[On(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES)]
  static public function onRegisterFieldTypes(RegisterComponentTypesEvent $event): void {
    $event->types[] = SeoField::class;
  }

  /**
   * @param RegisterComponentTypesEvent $event
   * @return void
   */
  #[On(Utilities::class, Utilities::EVENT_REGISTER_UTILITIES)]
  static public function onRegisterUtilities(RegisterComponentTypesEvent $event): void {
    if (count(Plugin::getInstance()->getSettings()->iconClasses) > 0) {
      $event->types[] = IconUtility::class;
    }
  }
}
