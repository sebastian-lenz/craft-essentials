<?php

namespace lenz\craft\essentials\twig\queries;

use Craft;
use craft\db\Paginator;
use craft\elements\db\ElementQuery;
use craft\helpers\UrlHelper;
use craft\web\Request;
use lenz\craft\essentials\twig\queries\displays\ToolbarDisplay;
use lenz\craft\essentials\twig\queries\displays\PaginationDisplay;
use lenz\craft\essentials\twig\queries\filters\AbstractFilter;
use lenz\craft\utils\models\Url;
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
    $this->setRequest(Craft::$app->getRequest());
    $this->setQuery($this->createQuery());
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
   * @param array $config
   * @return ToolbarDisplay
   */
  public function getToolbar(array $config = []) {
    return new ToolbarDisplay($this, $config);
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
   * @param array $config
   * @return PaginationDisplay
   * @throws Throwable
   */
  public function getPagination($config = []) {
    return new PaginationDisplay($this, $config);
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
   * @param array $overrides
   * @param array $params
   * @return array
   */
  public function getParameters($overrides = [], $params = []) {
    foreach ($this->_filters as $filter) {
      $params = array_merge($params, $filter->getParameters());
    }

    return $this->applyParameters($params);
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
    return intval($this->getPaginator()->getTotalResults());
  }

  /**
   * @param array $overrides
   * @param array $params
   * @return string
   */
  public function getUrl($overrides = [], $params = []) {
    return Url::compose(
      $this->getBasePath(),
      $this->getParameters($overrides, $params)
    );
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
   * @param array $params
   * @return array|mixed
   */
  protected function applyParameters($params = []) {
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

    return $params;
  }

  /**
   * @return ElementQuery
   */
  abstract protected function createQuery();

  /**
   * @param ElementQuery $query
   */
  protected function setQuery(ElementQuery $query) {
    foreach ($this->_filters as $filter) {
      $filter->prepareQuery($this, $query);
    }

    if (is_array($query->relatedTo) && count($query->relatedTo) > 1) {
      $query->relatedTo = array_merge(['and'], $query->relatedTo);
    }

    $this->_query = $query;
  }

  /**
   * @param Request $request
   */
  protected function setRequest(Request $request) {
    foreach ($this->_filters as $filter) {
      $filter->setRequest($request);
    }

    $this->setSort(
      $request->getParam('sort', static::DEFAULT_SORT),
      $request->getParam('dir', static::DEFAULT_SORT_DIRECTION)
    );
  }


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
