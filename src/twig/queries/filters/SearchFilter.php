<?php

namespace lenz\craft\essentials\twig\queries\filters;

use craft\elements\db\ElementQuery;
use craft\helpers\Html;
use lenz\contentfield\twig\DisplayInterface;
use lenz\craft\essentials\twig\queries\AbstractQuery;

/**
 * Class SearchFilter
 */
class SearchFilter extends AbstractValueFilter implements DisplayInterface
{
  /**
   * @var string|null
   */
  private $_search = null;

  /**
   * The parameter name of this filter.
   */
  const NAME = 'q';


  /**
   * @inheritDoc
   */
  public function display(array $variables = []) {
    echo Html::input('search', $this->getName(), $this->getValue());
  }

  /**
   * @inheritDoc
   */
  public function getDescription() {
    $search = $this->_search;
    if (empty($search)) {
      return null;
    }

    return '"' . $search . '"';
  }

  /**
   * @inheritDoc
   */
  public function getName() : string {
    return self::NAME;
  }

  /**
   * @inheritDoc
   */
  public function getValue() : ?string {
    return $this->_search;
  }

  /**
   * @inheritDoc
   */
  public function prepareQuery(AbstractQuery $owner, ElementQuery $query) {
    $search = $this->_search;

    if (!empty($search)) {
      $query->search($search)->orderBy('score');
    }
  }

  /**
   * @inheritDoc
   */
  public function setValue(string $value) {
    $value = trim($value);
    $this->_search = empty($value)
      ? null
      : $value;
  }
}
