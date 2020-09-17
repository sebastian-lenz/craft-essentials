<?php

namespace lenz\craft\essentials\twig\queries\displays;

use Craft;
use craft\web\twig\variables\Paginate;
use lenz\craft\essentials\twig\queries\AbstractQuery;
use lenz\contentfield\twig\DisplayInterface;

/**
 * Class PaginationDisplay
 */
class PaginationDisplay extends Paginate implements DisplayInterface
{
  /**
   * @var AbstractQuery|null
   */
  private $_query = null;


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
      ->load('_includes/query-pagination.twig')
      ->display([
        'paginate' => $this,
      ] + $variables);
  }

  /**
   * @inheritDoc
   */
  public function getPageUrl(int $page) {
    if ($page >= 1 && $page <= $this->totalPages) {
      $params = [];
      if ($page != 1) {
        $params = ['page' => $page];
      }

      return $this->_query->getUrl([], $params);
    }

    return null;
  }


  // Static methods
  // --------------

  /**
   * @param AbstractQuery $query
   * @return PaginationDisplay
   */
  public static function createFromQuery(AbstractQuery $query) {
    /** @var PaginationDisplay $pagination */
    $pagination = parent::create($query->getPaginator());
    $pagination->_query = $query;
    return $pagination;
  }
}
