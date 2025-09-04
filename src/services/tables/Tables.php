<?php

namespace lenz\craft\essentials\services\tables;

use Craft;
use craft\events\RegisterCpNavItemsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\twig\variables\Cp;
use craft\web\UrlManager;
use InvalidArgumentException;
use lenz\craft\essentials\Plugin;
use lenz\craft\essentials\services\AbstractService;
use lenz\craft\essentials\services\eventBus\On;

/**
 * Class Tables
 */
class Tables extends AbstractService
{
  /**
   * @var AbstractTable[]
   */
  private array $_tables = [];


  /**
   * @param string $name
   * @return AbstractTable
   */
  public function getTable(string $name): AbstractTable {
    if (!array_key_exists($name, $this->_tables)) {
      $table = new $name();
      if (!($table instanceof AbstractTable)) {
        throw new InvalidArgumentException('Invalid data table class ' . $name);
      }

      $this->_tables[$name] = $table;
    }

    return $this->_tables[$name];
  }

  /**
   * @return AbstractTable[]
   */
  public function getAllTables(): array {
    foreach (Plugin::getInstance()->getSettings()->dataTables as $name) {
      self::getTable($name);
    }

    return $this->_tables;
  }


  // Static methods
  // --------------

  /**
   * @param RegisterUrlRulesEvent $event
   * @return void
   */
  #[On(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, [self::class, 'requiresHandler'])]
  static public function onRegisterUrlRules(RegisterUrlRulesEvent $event): void {
    $event->rules += [
      'tables' => 'lenz-craft-essentials/tables/index',
      'tables/view' => 'lenz-craft-essentials/tables/view',
    ];
  }

  /**
   * @param RegisterCpNavItemsEvent $event
   * @return void
   */
  #[On(Cp::class, Cp::EVENT_REGISTER_CP_NAV_ITEMS, [self::class, 'requiresHandler'])]
  static public function onRegisterCpNavItems(RegisterCpNavItemsEvent $event): void {
    array_splice($event->navItems, 2, 0, [[
      'url'      => 'tables',
      'label'    => Craft::t('lenz-craft-essentials', 'Tables'),
      'fontIcon' => 'list'
    ]]);
  }

  /**
   * @return bool
   */
  static public function requiresHandler(): bool {
    return count(Plugin::getInstance()->getSettings()->dataTables);
  }
}
