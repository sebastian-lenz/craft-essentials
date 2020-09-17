<?php

namespace lenz\craft\essentials\twig\queries\filters;

use Craft;
use craft\elements\db\ElementQuery;
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
  public $label;

  /**
   * @var string
   */
  public $type;


  /**
   * AbstractFilter constructor.
   *
   * @param array $config
   */
  public function __construct($config = []) {
    parent::__construct($config);
  }

  /**
   * @return bool
   */
  public function allowCustomFilter() {
    return true;
  }

  /**
   * @return string|null
   */
  public function getDescription() {
    return null;
  }

  /**
   * @return string
   */
  abstract function getName();

  /**
   * @return string|null
   */
  public function getQueryParameter() {
    return null;
  }

  /**
   * @param AbstractQuery $owner
   * @param ElementQuery $query
   */
  public function prepareQuery(AbstractQuery $owner, ElementQuery $query) { }

  /**
   * @param string $value
   */
  public function setQueryParameter($value) { }


  // Protected methods
  // -----------------

  /**
   * @return void
   */
  protected function prepare() {
    if ($this->allowCustomFilter()) {
      $custom = Craft::$app->getRequest()->getParam($this->getName());
      if (!is_null($custom)) {
        $this->setQueryParameter($custom);
      }
    }
  }


  // Static methods
  // --------------

  /**
   * @param array $config
   * @return static
   */
  static public function create(array $config = []) {
    $filter = new static($config);
    $filter->prepare();
    return $filter;
  }
}
