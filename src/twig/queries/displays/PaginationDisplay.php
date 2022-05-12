<?php

namespace lenz\craft\essentials\twig\queries\displays;

use Craft;
use craft\web\twig\variables\Paginate;
use lenz\craft\essentials\twig\queries\AbstractQuery;
use lenz\contentfield\twig\DisplayInterface;
use Throwable;

/**
 * Class PaginationDisplay
 */
class PaginationDisplay extends Paginate implements DisplayInterface
{
  /**
   * @var string
   */
  public string $template = '_includes/query-pagination.twig';

  /**
   * @var array
   */
  public array $variables = [];

  /**
   * @var AbstractQuery|null
   */
  private ?AbstractQuery $_query;


  /**
   * PaginationDisplay constructor.
   *
   * @param AbstractQuery $query
   * @param array $config
   */
  public function __construct(AbstractQuery $query, array $config = []) {
    $paginator = $query->getPaginator();
    $pageResults = $paginator->getPageResults();
    $pageOffset = $paginator->getPageOffset();

    parent::__construct(array_merge([
      'first' => $pageOffset + 1,
      'last' => $pageOffset + count($pageResults),
      'total' => $paginator->getTotalResults(),
      'currentPage' => $paginator->getCurrentPage(),
      'totalPages' => $paginator->getTotalPages(),
    ], $config));

    $this->_query = $query;
  }

  /**
   * @inheritDoc
   */
  public function display(array $variables = []) {
    if (!$this->_query->hasPagination()) {
      return;
    }

    Craft::$app
      ->getView()
      ->getTwig()
      ->load($this->template)
      ->display($this->getVariables($variables));
  }

  /**
   * @inheritDoc
   */
  public function getPageUrl(int $page): ?string {
    if ($page >= 1 && $page <= $this->totalPages) {
      $params = [];
      if ($page != 1) {
        $params = ['page' => $page];
      }

      return $this->_query->getUrl([], $params);
    }

    return null;
  }

  /**
   * @param array $variables
   * @return array
   */
  public function getVariables(array $variables = []): array {
    return array_merge([
      'paginate' => $this,
      'query' => $this->_query,
    ], $this->variables, $variables);
  }

  /**
   * @param array $variables
   * @return string
   * @throws Throwable
   */
  public function render(array $variables = []): string {
    if (!$this->_query->hasPagination()) {
      return '';
    }

    return Craft::$app
      ->getView()
      ->getTwig()
      ->load($this->template)
      ->render($this->getVariables($variables));
  }
}
