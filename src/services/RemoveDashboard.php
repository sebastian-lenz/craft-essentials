<?php

namespace lenz\craft\essentials\services;

use Craft;
use craft\events\RegisterCpNavItemsEvent;
use craft\web\twig\variables\Cp;
use yii\base\Component;
use yii\base\Event;

/**
 * Class RemoveDashboard
 */
class RemoveDashboard extends Component
{
  /**
   * @var RemoveDashboard
   */
  static private $_instance;


  /**
   * RemoveDashboard constructor.
   */
  private function __construct() {
    parent::__construct();

    $request = Craft::$app->getRequest();
    if ($request->getIsCpRequest()) {
      if ($request->getSegment(1) == 'dashboard') {
        Craft::$app->getResponse()->redirect('entries');
      }

      Event::on(Cp::class, Cp::EVENT_REGISTER_CP_NAV_ITEMS, [$this, 'onRegisterCpNavItems']);
    }
  }

  /**
   * @param RegisterCpNavItemsEvent $event
   */
  public function onRegisterCpNavItems(RegisterCpNavItemsEvent $event) {
    for ($index = 0; $index < count($event->navItems); $index++) {
      if ($event->navItems[$index]['url'] == 'dashboard') {
        unset($event->navItems[$index]);
        break;
      }
    }
  }


  // Static methods
  // --------------

  /**
   * @return RemoveDashboard
   */
  public static function getInstance() {
    if (!isset(self::$_instance)) {
      self::$_instance = new RemoveDashboard();
    }

    return self::$_instance;
  }
}
