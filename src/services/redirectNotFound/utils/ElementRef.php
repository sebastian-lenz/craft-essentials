<?php

namespace lenz\craft\essentials\services\redirectNotFound\utils;

use craft\base\ElementInterface;
use craft\elements\Asset;
use craft\elements\Entry;
use lenz\craft\essentials\helpers\Arr;
use yii\base\BaseObject;

/**
 * Represents an element reference in the form of
 * ```
 *   #TYPE:ID@SITE_ID#HASH
 * ```
 */
class ElementRef extends BaseObject
{
  /**
   * @var string|null
   */
  public string|null $hash = null;

  /**
   * @var int|null
   */
  public int|null $siteId = null;

  /**
   * @var string
   */
  const PATTERN = '/^#(?<type>asset|entry):(?<id>\d+)(?:@(?<siteId>\d+))?(?:#(?<hash>.*))?$/';

  /**
   * @var string[]
   */
  const TYPES = ['asset', 'entry'];


  /**
   * @param string $type
   * @param int $id
   * @param array $config
   */
  public function __construct(
    public string $type,
    public int $id,
    array $config = []
  ) {
    parent::__construct($config);
    self::assertType($this->type);
  }

  /**
   * @return string
   */
  public function __toString(): string {
    $result = "#$this->type:$this->id";

    if (!is_null($this->siteId)) {
      $result .= "@$this->siteId";
    }

    if (!is_null($this->hash)) {
      $result .= "#$this->hash";
    }

    return $result;
  }

  /**
   * @param string $url
   * @return void
   */
  public function copyHash(string $url): void {
    $hashPosition = strpos($url, '#');
    $this->hash = $hashPosition
      ? substr($url, $hashPosition + 1)
      : '';
  }

  /**
   * @param ElementInterface $element
   * @return bool
   */
  public function isInstanceOf(ElementInterface $element): bool {
    return match($this->type) {
      'asset' => $element instanceof Asset,
      default => $element instanceof Entry,
    };
  }

  /**
   * @param ElementInterface $element
   * @return bool
   */
  public function matches(ElementInterface $element): bool {
    return (
      $this->isInstanceOf($element) &&
      $this->id == $element->id &&
      (is_null($this->siteId) || $element->siteId == $this->siteId)
    );
  }


  // Static methods
  // --------------

  /**
   * @param mixed $value
   * @return void
   */
  static public function assertType(mixed $value): void {
    if (!in_array($value, self::TYPES)) {
      throw new \Exception("Invalid type `$value`");
    }
  }

  /**
   * @param ElementInterface $element
   * @param string|null $url
   * @return ElementRef
   */
  static public function fromElement(ElementInterface $element, ?string $url = null): ElementRef {
    if ($element instanceof Entry) {
      $ref = new ElementRef('entry', $element->id, ['siteId' => $element->siteId]);
    } elseif ($element instanceof Asset) {
      $ref = new ElementRef('asset', $element->id);
    } else {
      throw new \Exception('Invalid element type');
    }

    if (!empty($url)) {
      $ref->copyHash($url);
    }

    return $ref;
  }

  /**
   * @param string $value
   * @return ElementRef|null
   */
  static public function parse(string $value): ElementRef|null {
    if (preg_match(self::PATTERN, $value, $match)) {
      $config = Arr::only($match, ['hash', 'siteId']);
      return new ElementRef($match['type'], intval($match['id']), $config);
    } else {
      return null;
    }
  }
}
