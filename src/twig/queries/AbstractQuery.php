<?php

namespace lenz\craft\essentials\twig\queries;

use Craft;
use craft\db\Paginator;
use craft\elements\db\ElementQuery;
use craft\helpers\UrlHelper;
use lenz\craft\essentials\twig\queries\displays\ToolbarDisplay;
use lenz\craft\essentials\twig\queries\displays\PaginationDisplay;
use lenz\craft\essentials\twig\queries\filters\AbstractFilter;
use Throwable;
use yii\base\BaseObject;

/**
 * Class AbstractQuery
 */
abstract class AbstractQuery extends BaseObject
{
  /**
   * @var int
   */
  public $pageSize = 20;

  /**
   * @var string
   */
  public $sort;

  /**
   * @var boolean
   */
  public $sortDirection = 'asc';

  /**
   * @var AbstractFilter[]
   */
  protected $_filters;

  /**
   * @var Paginator
   */
  protected $_paginator;

  /**
   * @var ElementQuery
   */
  protected $_query;

  /**
   * @var string[]
   */
  const ALLOWED_SORTS = [];

  /**
   * @var string
   */
  const DEFAULT_SORT = '';

  /**
   * @var string
   */
  const DEFAULT_SORT_DIRECTION = 'asc';


  /**
   * AbstractQuery constructor.
   *
   * @param AbstractFilter[] $filters
   * @param array $options
   */
  public function __construct(array $filters, array $options = []) {
    parent::__construct($options);

    $this->_filters = $filters;

    $request = Craft::$app->getRequest();
    $this->setSort(
      $request->getParam('sort', static::DEFAULT_SORT),
      $request->getParam('dir', static::DEFAULT_SORT_DIRECTION)
    );

    $query = $this->createQuery();
    foreach ($filters as $filter) {
      $filter->prepareQuery($this, $query);
    }

    if (is_array($query->relatedTo) && count($query->relatedTo) > 1) {
      $query->relatedTo = array_merge(['and'], $query->relatedTo);
    }

    $this->_query = $query;
  }

  /**
   * @param string $sort
   * @return bool
   */
  public function allowSort(string $sort) {
    return in_array($sort, static::ALLOWED_SORTS);
  }

  /**
   * @return string
   */
  public function getBasePath() {
    try {
      return Craft::$app->getRequest()->getPathInfo();
    } catch (Throwable $error) {
      return '';
    }
  }

  /**
   * @return ToolbarDisplay
   */
  public function getToolbar() {
    return new ToolbarDisplay($this);
  }

  /**
   * @return AbstractFilter[]
   */
  public function getFilters() {
    return $this->_filters;
  }

  /**
   * @param string $key
   * @return string
   */
  public function getSortUrl(string $key) {
    if ($this->sort == $key) {
      return $this->getUrl([], [
        'sort' => $key,
        'dir'  => $this->sortDirection == 'asc' ? 'desc' : 'asc',
      ]);
    } else {
      return $this->getUrl([], [
        'sort' => $key,
        'dir'  => 'asc'
      ]);
    }
  }

  /**
   * @return PaginationDisplay
   * @throws Throwable
   */
  public function getPagination() {
    return PaginationDisplay::createFromQuery($this);
  }

  /**
   * @return Paginator
   */
  public function getPaginator() {
    if (!isset($this->_paginator)) {
      $this->_paginator = new Paginator($this->_query, [
        'currentPage' => Craft::$app->getRequest()->getPageNum(),
        'pageSize'    => $this->pageSize,
      ]);
    }

    return $this->_paginator;
  }

  /**
   * @return array
   */
  public function getResults() {
    return $this->getPaginator()->getPageResults();
  }

  /**
   * @return float|int
   */
  public function getTotalResults() {
    return $this->getPaginator()->getTotalResults();
  }

  /**
   * @param array $overrides
   * @param array $params
   * @return string
   */
  public function getUrl($overrides = [], $params = []) {
    foreach ($this->_filters as $filter) {
      $name = $filter->getName();
      $value = array_key_exists($name, $overrides)
        ? $overrides[$name]
        : $filter->getQueryParameter();

      if (!empty($value)) {
        $params[$name] = $value;
      }
    }

    if (!isset($params['sort']) && !empty($this->sort)) {
      $params['sort'] = $this->sort;
    }

    if (!isset($params['dir'])) {
      $params['dir'] = $this->sortDirection;
    }

    // Remove default values
    if (isset($params['sort']) && $params['sort'] == static::DEFAULT_SORT) {
      unset($params['sort']);
    }

    if (isset($params['dir']) && $params['dir'] == self::DEFAULT_SORT_DIRECTION) {
      unset($params['dir']);
    }

    return UrlHelper::url($this->getBasePath(), $params);
  }

  /**
   * @return bool
   */
  public function hasPagination() {
    return $this->getPaginator()->getTotalPages() > 1;
  }

  /**
   * @return bool
   */
  public function hasResults() {
    return $this->getPaginator()->totalResults > 0;
  }

  /**
   * @param string $sort
   * @param string $direction
   */
  public function setSort(string $sort, string $direction) {
    if ($this->allowSort($sort)) {
      $this->sort = $sort;
      $this->sortDirection = $direction == 'asc' ? 'asc' : 'desc';
    }
  }


  // Protected methods
  // -----------------

  /**
   * @return ElementQuery
   */
  abstract protected function createQuery();


  // Static methods
  // --------------

  /**
   * @param array $config
   * @return static
   */
  static public function create(array $config = []) {
    return new static($config);
  }
}
