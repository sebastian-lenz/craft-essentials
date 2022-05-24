<?php

namespace lenz\craft\essentials\structs\structure;

use ArrayIterator;
use craft\base\Element;
use craft\base\ElementInterface;
use IteratorAggregate;
use Yii;

/**
 * Class AbstractStructureItem
 * @template T of AbstractStructure
 */
abstract class AbstractStructureItem implements IteratorAggregate
{
  /**
   * @var int
   */
  public int $id = 0;

  /**
   * @var int|null
   */
  public ?int $nestedLevel = null;

  /**
   * @var int|null
   */
  public ?int $nestedLft = null;

  /**
   * @var int|null
   */
  public ?int $nestedRgt = null;

  /**
   * @var T
   */
  protected AbstractStructure $_collection;


  /**
   * AbstractStructureItem constructor.
   *
   * @param T $collection
   * @param ElementInterface|array $config
   */
  public function __construct(AbstractStructure $collection, mixed $config) {
    $this->_collection = $collection;

    if ($config instanceof ElementInterface) {
      $this->setElement($config);
    } elseif (is_array($config)) {
      Yii::configure($this, $config);
    }
  }

  /**
   * @param bool $includeSelf
   * @return static[]
   */
  public function getAncestors(bool $includeSelf = false): array {
    $ancestors = $this->_collection->getAncestors($this);
    if ($includeSelf) {
      $ancestors[] = $this;
    }

    return $ancestors;
  }

  /**
   * @return static[]
   */
  public function getChildren(): array {
    return $this->_collection->getChildren($this);
  }

  /**
   * @return T
   */
  public function getCollection(): AbstractStructure {
    return $this->_collection;
  }

  /**
   * @return static[]
   * @noinspection PhpUnused (Public API)
   */
  public function getDescendants(): array {
    return $this->_collection->getDescendants($this);
  }

  /**
   * @inheritDoc
   */
  public function getIterator(): ArrayIterator {
    return new ArrayIterator($this->getChildren());
  }

  /**
   * @return static|null
   */
  public function getParent(): ?static {
    return $this->_collection->getParent($this);
  }

  /**
   * @return bool
   * @noinspection PhpUnused (Public API)
   */
  public function hasChildren(): bool {
    return count($this->getChildren()) > 0;
  }

  /**
   * @return bool
   * @noinspection PhpUnused (Public API)
   */
  public function hasParent(): bool {
    return !is_null($this->getParent());
  }


  // Protected methods
  // -----------------

  /**
   * @param ElementInterface $element
   */
  protected function setElement(ElementInterface $element) {
    $this->id = intval($element->getId());

    if ($element instanceof Element) {
      $this->nestedLevel = intval($element->level);
      $this->nestedLft   = intval($element->lft);
      $this->nestedRgt   = intval($element->rgt);
    }
  }


  // Static methods
  // --------------

  /**
   * @param T $collection
   * @param ElementInterface[] $elements
   * @return static[]
   */
  static public function fromElements(AbstractStructure $collection, array $elements): array {
    $result = [];
    foreach ($elements as $element) {
      $result[] = new static($collection, $element);
    }

    return $result;
  }
}
