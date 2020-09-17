<?php

namespace lenz\craft\essentials\twig\queries\filters;

use craft\elements\db\ElementQuery;
use lenz\craft\essentials\twig\queries\AbstractQuery;

/**
 * Class SearchFilter
 */
class SearchFilter extends AbstractFilter
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
   * @return string|null
   */
  public function getDescription() {
    $search = $this->_search;
    if (empty($search)) {
      return null;
    }

    return '"' . $search . '"';
  }

  /**
   * @return string
   */
  public function getName() {
    return self::NAME;
  }

  /**
   * @return string|null
   */
  public function getQueryParameter() {
    return $this->_search;
  }

  /**
   * @return string|null
   */
  public function getValue() {
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
  public function setQueryParameter($value) {
    $value = trim($value);
    $this->_search = empty($value)
      ? null
      : $value;
  }
}
