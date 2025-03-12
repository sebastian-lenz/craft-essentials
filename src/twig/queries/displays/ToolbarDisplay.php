<?php

namespace lenz\craft\essentials\twig\queries\displays;

use Craft;
use craft\helpers\UrlHelper;
use Generator;
use lenz\craft\essentials\twig\queries\AbstractQuery;
use lenz\craft\essentials\twig\queries\filters\SearchFilter;
use lenz\contentfield\twig\DisplayInterface;
use Throwable;
use yii\base\BaseObject;

/**
 * Class FilterDisplay
 */
class ToolbarDisplay extends BaseObject implements DisplayInterface
{
  /**
   * @var string
   */
  public string $template = '_includes/query-filter.twig';

  /**
   * @var array
   */
  public array $variables = [];

  /**
   * @var AbstractQuery
   */
  private AbstractQuery $_query;


  /**
   * FilterDisplay constructor.
   *
   * @param AbstractQuery $query
   * @param array $config
   */
  public function __construct(AbstractQuery $query, array $config = []) {
    parent::__construct($config);
    $this->_query = $query;
  }

  /**
   * @inheritDoc
   * @throws Throwable
   */
  public function display(array $variables = []): Generator {
    $variables = array_merge([
      'display' => $this,
      'filters' => $this->_query->getFilters(),
      'query'   => $this->_query,
    ], $this->variables, $variables);

    yield Craft::$app
      ->getView()
      ->getTwig()
      ->load($this->template)
      ->render($variables);
  }

  /**
   * @return string
   */
  public function getBaseUrl(): string {
    return UrlHelper::url($this->_query->getBasePath());
  }

  /**
   * @return string
   */
  public function getDescription(): string {
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
   * @noinspection PhpUnused
   */
  public function getTotalResults(): int {
    return $this->_query->getPaginator()->totalResults;
  }

  /**
   * @return SearchFilter|null
   */
  public function getSearch(): ?SearchFilter {
    foreach ($this->_query->getFilters() as $filter) {
      if ($filter instanceof SearchFilter) {
        return $filter;
      }
    }

    return null;
  }

  /**
   * @return bool
   * @noinspection PhpUnused
   */
  public function hasSearch(): bool {
    return !is_null($this->getSearch());
  }
}
