<?php

namespace lenz\craft\essentials\structs\menu;

use Craft;
use lenz\craft\essentials\structs\structure\AbstractStructure;
use lenz\craft\utils\elementCache\ElementCache;

/**
 * Class AbstractMenu
 *
 * @phpstan-template T of AbstractMenuItem
 * @phpstan-extends AbstractStructure<T>
 */
abstract class AbstractMenu extends AbstractStructure
{
  /**
   * @phpstan-var T[]
   */
  protected array $_breadcrumbs;

  /**
   * @phpstan-var T|null
   */
  protected ?AbstractMenuItem $_current;

  /**
   * @phpstan-var static
   */
  static protected AbstractMenu $_instance;

  /**
   * @phpstan-var string
   */
  const ITEM_CLASS = AbstractMenuItem::class;


  /**
   * @phpstan-param int|string $type
   * @phpstan-return T[]
   * @noinspection PhpUnused (Public API)
   */
  public function getAllByType(int|string $type): array {
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
   * @phpstan-param int|string $type
   * @phpstan-return T|null
   * @noinspection PhpUnused (Public API)
   */
  public function getByType(int|string $type): ?AbstractMenuItem {
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
   * @phpstan-return T[]
   * @noinspection PhpUnused (Public API)
   */
  public function getBreadcrumbs(): array {
    return $this->_breadcrumbs;
  }

  /**
   * @phpstan-return T|null
   */
  public function getCurrent(): ?AbstractMenuItem {
    return $this->_current;
  }


  // Protected methods
  // -----------------

  /**
   * @phpstan-return T|null
   */
  protected function findCurrent(): ?AbstractMenuItem {
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
  protected function init(): void {
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
   * @phpstan-return static
   */
  static function getInstance(): AbstractMenu {
    if (!isset(static::$_instance)) {
      static::$_instance = ElementCache::withLanguage(static::class, function() {
        return new static(null);
      });

      static::$_instance->init();
    }

    return static::$_instance;
  }
}
