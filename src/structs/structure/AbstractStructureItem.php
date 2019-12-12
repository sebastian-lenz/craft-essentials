<?php

namespace lenz\craft\essentials\structs\structure;

use ArrayIterator;
use craft\base\Element;
use craft\base\ElementInterface;
use IteratorAggregate;
use Yii;

/**
 * Class AbstractStructureItem
 */
abstract class AbstractStructureItem implements IteratorAggregate
{
  /**
   * @var int
   */
  public $id;

  /**
   * @var int|null
   */
  public $nestedLevel = null;

  /**
   * @var int|null
   */
  public $nestedLft = null;

  /**
   * @var int|null
   */
  public $nestedRgt = null;

  /**
   * @var AbstractStructure
   */
  protected $_collection;


  /**
   * AbstractStructureItem constructor.
   *
   * @param AbstractStructure $collection
   * @param ElementInterface|array $config
   */
  public function __construct(AbstractStructure $collection, $config) {
    $this->_collection = $collection;

    if ($config instanceof ElementInterface) {
      $this->setElement($config);
    } elseif (is_array($config)) {
      Yii::configure($this, $config);
    }
  }

  /**
   * @param bool $includeSelf
   * @return AbstractStructureItem[]
   */
  public function getAncestors($includeSelf = false) {
    $ancestors = $this->_collection->getAncestors($this);
    if ($includeSelf) {
      $ancestors[] = $this;
    }

    return $ancestors;
  }

  /**
   * @return AbstractStructureItem[]
   */
  public function getChildren() {
    return $this->_collection->getChildren($this);
  }

  /**
   * @return AbstractStructure
   */
  public function getCollection() {
    return $this->_collection;
  }

  /**
   * @return AbstractStructureItem[]
   */
  public function getDescendants() {
    return $this->_collection->getDescendants($this);
  }

  /**
   * @inheritDoc
   */
  public function getIterator() {
    return new ArrayIterator($this->getChildren());
  }

  /**
   * @return AbstractStructureItem|null
   */
  public function getParent() {
    return $this->_collection->getParent($this);
  }

  /**
   * @return bool
   */
  public function hasChildren() {
    return count($this->getChildren()) > 0;
  }

  /**
   * @return bool
   */
  public function hasParent() {
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
   * @param AbstractStructure $collection
   * @param ElementInterface[] $elements
   * @return static[]
   */
  static public function fromElements(AbstractStructure $collection, $elements) {
    $result = [];
    foreach ($elements as $element) {
      $result[] = new static($collection, $element);
    }

    return $result;
  }
}
