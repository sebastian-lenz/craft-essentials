<?php

namespace lenz\craft\essentials\structs\structure;

use ArrayIterator;
use craft\base\ElementInterface;
use craft\elements\db\ElementQuery;
use IteratorAggregate;

/**
 * Class AbstractStructure
 * @template T of AbstractStructureItem
 */
abstract class AbstractStructure implements IteratorAggregate
{
  /**
   * @var T[]
   */
  protected array $_items = [];

  /**
   * @var class-string<T>
   */
  const ITEM_CLASS = AbstractStructureItem::class;


  /**
   * AbstractMenu constructor.
   *
   * @param ElementQuery|null $query
   */
  public function __construct(ElementQuery $query = null) {
    if (!is_null($query)) {
      $this->_items = $this->createItemsFromQuery($query);
    }
  }

  /**
   * @param T $parent
   * @param T $item
   * @return T
   * @noinspection PhpUnused (Public API)
   */
  public function attachTo(AbstractStructureItem $parent, AbstractStructureItem $item): AbstractStructureItem {
    $insertIndex = -1;
    $children = $parent->getChildren();

    if (count($children)) {
      $lastChild = $children[count($children) - 1];
      $insertAt = $lastChild->nestedRgt;
      foreach ($this->_items as $index => $existing) {
        if ($existing === $lastChild) $insertIndex = $index + 1;
        if ($existing->nestedLft > $insertAt) $existing->nestedLft += 2;
        if ($existing->nestedRgt > $insertAt) $existing->nestedRgt += 2;
      }
    } else {
      $insertAt = $parent->nestedLft;
      foreach ($this->_items as $index => $existing) {
        if ($existing->nestedLft == $insertAt) $insertIndex = $index + 1;
        if ($existing->nestedLft > $insertAt) $existing->nestedLft += 2;
        if ($existing->nestedRgt > $insertAt) $existing->nestedRgt += 2;
      }
    }

    $item->nestedLevel = $parent->nestedLevel + 1;
    $item->nestedLft = $insertAt + 1;
    $item->nestedRgt = $insertAt + 2;

    if ($insertIndex == -1) {
      $this->_items[] = $item;
    } else {
      array_splice($this->_items, $insertIndex, 0, [$item]);
    }

    return $item;
  }

  /**
   * @return T[]
   */
  public function getAll(): array {
    return $this->_items;
  }

  /**
   * @param ElementInterface|T|int $elementOrId
   * @return T[]
   */
  public function getAncestors(mixed $elementOrId): array {
    $child = $this->getById($elementOrId);
    if (is_null($child)) {
      return [];
    }

    return $this->filter(function(AbstractStructureItem $item) use ($child) {
      return (
        $item->nestedLft < $child->nestedLft &&
        $item->nestedRgt > $child->nestedRgt
      );
    });
  }

  /**
   * @param ElementInterface|T|int $elementOrId
   * @return T|null
   */
  public function getById(mixed $elementOrId): ?AbstractStructureItem {
    if ($elementOrId instanceof AbstractStructureItem) {
      return $elementOrId;
    }

    $id = self::normalizeId($elementOrId);
    foreach ($this->_items as $item) {
      if ($item->id == $id) return $item;
    }

    return null;
  }

  /**
   * @param ElementInterface|T|int $elementOrId
   * @return T[]
   */
  public function getChildren(mixed $elementOrId): array {
    $parent = $this->getById($elementOrId);
    if (is_null($parent)) {
      return [];
    }

    return $this->filter(function(AbstractStructureItem $item) use ($parent) {
      return (
        $item->nestedLft > $parent->nestedLft &&
        $item->nestedRgt < $parent->nestedRgt &&
        $item->nestedLevel === $parent->nestedLevel + 1
      );
    });
  }

  /**
   * @param ElementInterface|T|int $elementOrId
   * @return T[]
   */
  public function getDescendants(mixed $elementOrId): array {
    $parent = $this->getById($elementOrId);
    if (is_null($parent)) {
      return [];
    }

    return $this->filter(function(AbstractStructureItem $item) use ($parent) {
      return (
        $item->nestedLft > $parent->nestedLft &&
        $item->nestedRgt < $parent->nestedRgt
      );
    });
  }

  /**
   * @inheritDoc
   */
  public function getIterator(): ArrayIterator {
    return new ArrayIterator($this->getRootItems());
  }

  /**
   * @param ElementInterface|T|int $elementOrId
   * @return T|null
   */
  public function getParent(mixed $elementOrId): ?AbstractStructureItem {
    $child = $this->getById($elementOrId);
    if (is_null($child)) {
      return null;
    }

    foreach ($this->_items as $item) {
      if (
        $item->nestedLft < $child->nestedLft &&
        $item->nestedRgt > $child->nestedRgt &&
        $item->nestedLevel === $child->nestedLevel - 1
      ) {
        return $item;
      }
    }

    return null;
  }

  /**
   * @return T[]
   */
  public function getRootItems(): array {
    return $this->filter(function(AbstractStructureItem $item) {
      return $item->nestedLevel === 1;
    });
  }


  // Protected methods
  // -----------------

  /**
   * @param ElementQuery $query
   * @return T[]
   */
  protected function createItemsFromQuery(ElementQuery $query): array {
    $elements = $query->all();
    $itemClass = static::ITEM_CLASS;
    $items = [];

    foreach ($elements as $element) {
      $item = new $itemClass($this, $element);
      $items[] = $item;
    }

    return $items;
  }

  /**
   * @param callable $callback
   * @return T[]
   */
  protected function filter(callable $callback): array {
    return array_values(array_filter($this->_items, $callback));
  }


  // Static methods
  // --------------

  /**
   * @param ElementInterface|T|int $elementOrId
   * @return int
   */
  static function normalizeId(mixed $elementOrId): int {
    if ($elementOrId instanceof ElementInterface) {
      return intval($elementOrId->getId());
    } elseif ($elementOrId instanceof AbstractStructureItem) {
      return $elementOrId->id;
    } else {
      return intval($elementOrId);
    }
  }
}
