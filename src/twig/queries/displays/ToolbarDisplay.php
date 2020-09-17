<?php

namespace lenz\craft\essentials\twig\queries\displays;

use Craft;
use craft\helpers\UrlHelper;
use lenz\craft\essentials\twig\queries\AbstractQuery;
use lenz\craft\essentials\twig\queries\filters\SearchFilter;
use lenz\contentfield\twig\DisplayInterface;

/**
 * Class FilterDisplay
 */
class ToolbarDisplay implements DisplayInterface
{
  /**
   * @var AbstractQuery
   */
  private $_query;


  /**
   * FilterDisplay constructor.
   * @param AbstractQuery $query
   */
  public function __construct(AbstractQuery $query) {
    $this->_query = $query;
  }

  /**
   * @inheritDoc
   */
  public function display(array $variables = []) {
    Craft::$app
      ->getView()
      ->getTwig()
      ->load('_includes/query-filter.twig')
      ->display([
        'display' => $this,
        'filters' => $this->_query->getFilters(),
      ] + $variables);
  }

  /**
   * @return string
   */
  public function getBaseUrl() {
    return UrlHelper::url($this->_query->getBasePath());
  }

  /**
   * @return string
   */
  public function getDescription() {
    $result = [];
    foreach ($this->_query->getFilters() as $filter) {
      $description = $filter->getDescription();
      if (!is_null($description)) {
        $result[] = $description;
      }
    }

    return count($result) > 0
      ? implode(', ', $result)
      : '';
  }

  /**
   * @return int
   */
  public function getTotalResults() {
    return $this->_query->getPaginator()->totalResults;
  }

  /**
   * @return SearchFilter|null
   */
  public function getSearch() {
    foreach ($this->_query->getFilters() as $filter) {
      if ($filter instanceof SearchFilter) {
        return $filter;
      }
    }

    return null;
  }

  /**
   * @return bool
   */
  public function hasSearch() {
    return !is_null($this->getSearch());
  }
}
