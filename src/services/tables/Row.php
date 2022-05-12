<?php

namespace lenz\craft\essentials\services\tables;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/**
 * Class Row
 */
class Row implements ArrayAccess, JsonSerializable, IteratorAggregate
{
  /**
   * @var array
   */
  public array $attributes;


  /**
   * Row constructor.
   *
   * @param array $attributes
   */
  public function __construct(array $attributes) {
    $this->attributes = $attributes;
  }

  /**
   * @param string|int $offset
   * @return mixed
   */
  public function __get(string|int $offset) {
    return array_key_exists($offset, $this->attributes)
      ? $this->attributes[$offset]
      : null;
  }

  /**
   * @param string|int $offset
   * @return bool
   */
  public function __isset(string|int $offset): bool {
    return array_key_exists($offset, $this->attributes);
  }

  /**
   * @param string|int $offset
   * @param mixed $value
   */
  public function __set(string|int $offset, mixed $value) {
    $this->attributes[$offset] = $value;
  }

  /**
   * @param string|int $offset
   */
  public function __unset(string|int $offset) {
    unset($this->attributes[$offset]);
  }

  /**
   * @inheritDoc
   */
  public function getIterator(): Traversable {
    return new ArrayIterator($this->attributes);
  }

  /**
   * @inheritDoc
   */
  public function offsetExists($offset): bool {
    return array_key_exists($offset, $this->attributes);
  }

  /**
   * @inheritDoc
   */
  public function offsetGet($offset) {
    return array_key_exists($offset, $this->attributes)
      ? $this->attributes[$offset]
      : null;
  }

  /**
   * @inheritDoc
   */
  public function offsetSet($offset, $value) {
    $this->attributes[$offset] = $value;
  }

  /**
   * @inheritDoc
   */
  public function offsetUnset($offset) {
    unset($this->attributes[$offset]);
  }

  /**
   * @inheritDoc
   */
  public function jsonSerialize() {
    return $this->attributes;
  }
}
