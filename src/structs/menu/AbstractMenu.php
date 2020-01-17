<?php

namespace lenz\craft\essentials\structs\menu;

use Craft;
use lenz\craft\essentials\structs\structure\AbstractStructure;
use lenz\craft\essentials\structs\structure\AbstractStructureItem;
use lenz\craft\utils\elementCache\ElementCache;

/**
 * Class Menu
 *
 * @property AbstractMenuItem[] $_items
 */
abstract class AbstractMenu extends AbstractStructure
{
  /**
   * @var AbstractMenuItem[]
   */
  protected $_breadcrumbs;

  /**
   * @var AbstractMenuItem|null
   */
  protected $_current;

  /**
   * @var AbstractMenu
   */
  static protected $_instance;

  /**
   * @var string
   */
  const ITEM_CLASS = AbstractMenuItem::class;


  /**
   * @param int|string $type
   * @return AbstractMenuItem[]|AbstractStructureItem[]
   */
  public function getAllByType($type) {
    $isTypeId = is_numeric($type);

    return $this->filter(function(AbstractMenuItem $item) use ($isTypeId, $type) {
      if ($isTypeId) {
        return $item->typeId == $type;
      } else {
        return $item->typeHandle == $type;
      }
    });
  }

  /**
   * @param int|string $type
   * @return AbstractMenuItem|null
   */
  public function getByType($type) {
    $isTypeId = is_numeric($type);

    foreach ($this->_items as $item) {
      if ($isTypeId) {
        if ($item->typeId == $type) {
          return $item;
        }
      } elseif ($item->typeHandle == $type) {
        return $item;
      }
    }

    return null;
  }

  /**
   * @return AbstractMenuItem[]
   */
  public function getBreadcrumbs() {
    return $this->_breadcrumbs;
  }

  /**
   * @return AbstractMenuItem|null
   */
  public function getCurrent() {
    return $this->_current;
  }


  // Protected methods
  // -----------------

  /**
   * @return AbstractMenuItem|null
   */
  protected function findCurrent() {
    $element = Craft::$app->getUrlManager()
      ->getMatchedElement();

    if (!$element) {
      return null;
    }

    foreach ($this->_items as $item) {
      if ($item->id == $element->getId()) {
        return $item;
      }
    }

    return null;
  }

  /**
   * Initializes the menu after loading.
   */
  protected function init() {
    $current = $this->findCurrent();
    $breadcrumbs = is_null($current)
      ? []
      : $current->getAncestors(true);

    foreach ($breadcrumbs as $item) {
      $item->isActive = true;
    }

    $this->_breadcrumbs = $breadcrumbs;
    $this->_current = $current;
  }


  // Static methods
  // --------------

  /**
   * @return static
   */
  static function getInstance() {
    if (!isset(static::$_instance)) {
      static::$_instance = ElementCache::withLanguage(self::class, function() {
        return new static(null);
      });

      static::$_instance->init();
    }

    return static::$_instance;
  }
}
