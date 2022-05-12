<?php

namespace lenz\craft\essentials\twig\queries\filters;

use craft\elements\db\ElementQuery;
use craft\web\Request;
use lenz\craft\essentials\twig\queries\AbstractQuery;
use yii\base\BaseObject;

/**
 * Class AbstractQuery
 */
abstract class AbstractFilter extends BaseObject
{
  /**
   * @var string
   */
  public string $label;

  /**
   * @var string
   */
  public string $type;


  /**
   * @return bool
   */
  public function allowCustomFilter(): bool {
    return true;
  }

  /**
   * @return string|null
   */
  public function getDescription(): ?string {
    return null;
  }

  /**
   * @return string[]
   */
  public function getParameters() : array {
    return [];
  }

  /**
   * @param AbstractQuery $owner
   * @param ElementQuery $query
   */
  public function prepareQuery(AbstractQuery $owner, ElementQuery $query): void { }

  /**
   * @param Request $request
   */
  public function setRequest(Request $request): void { }


  // Static methods
  // --------------

  /**
   * @param array $config
   * @return static
   */
  static public function create(array $config = []): static {
    return new static($config);
  }
}
