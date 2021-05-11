<?php

namespace lenz\craft\essentials\services;

use Craft;
use craft\events\RegisterCpNavItemsEvent;
use craft\web\Application;
use craft\web\twig\variables\Cp;
use yii\base\BaseObject;
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
  public function __construct() {
    parent::__construct();

    if (Craft::$app->getRequest()->getIsCpRequest()) {
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
        Application::EVENT_BEFORE_ACTION,
        function() {
          $request = Craft::$app->getRequest();
          if ($request->getSegment(1) == 'dashboard') {
            $entries = (new Cp())->nav();
            Craft::$app->getResponse()->redirect(reset($entries)['url']);
          }
        }
      );
    }
  }


  // Static methods
  // --------------

  /**
   * @return RemoveDashboard
   */
  public static function getInstance(): RemoveDashboard {
    if (!isset(self::$_instance)) {
      self::$_instance = new RemoveDashboard();
    }

    return self::$_instance;
  }
}
