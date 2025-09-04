<?php

namespace lenz\craft\essentials\services;

use craft\elements\db\ElementQuery;
use craft\search\SearchQuery;
use lenz\craft\essentials\Plugin;
use lenz\craft\essentials\services\eventBus\On;
use Throwable;
use yii\base\Component;
use yii\base\Event;

/**
 * Class RemoveStopWords
 */
class RemoveStopWords extends Component
{
  /**
   * @var string[]
   */
  public array $stopWords;


  /**
   * @return string[]
   */
  public function getStopWords(): array {
    if (!isset($this->stopWords)) {
      $this->stopWords = require(__DIR__ . '/../../data/stopwords.php');
    }

    return $this->stopWords;
  }

  /**
   * @param string $value
   * @return string
   */
  public function filterStopWords(string $value) : string {
    $stopWords = $this->getStopwords();

    return implode(' ', array_filter(
      preg_split('/\s+/', $value),
      function($token) use (&$stopWords) {
        return !in_array(strtolower($token), $stopWords);
      }
    ));
  }

  /**
   * @param Event $event
   */
  #[On(ElementQuery::class, ElementQuery::EVENT_BEFORE_PREPARE,)]
  public function onBeforePrepare(Event $event): void {
    $query = $event->sender;
    if (!($query instanceof ElementQuery) || empty($query->search)) {
      return;
    }

    $originalSearch = $query->search;
    $search = $this->getFilteredSearch($originalSearch);
    if (is_null($search)) {
      return;
    }

    $query->search = $search;
    $query->on(
      ElementQuery::EVENT_AFTER_PREPARE,
      function() use ($originalSearch, $query) {
        $query->search = $originalSearch;
      }
    );
  }


  // Private methods
  // ---------------

  /**
   * @param mixed $search
   * @return SearchQuery|string|null
   */
  private function getFilteredSearch(mixed $search): SearchQuery|string|null {
    if ($search instanceof SearchQuery) {
      $originalQuery = $search->getQuery();
      $query = $this->filterStopWords($originalQuery);

      return $query == $originalQuery
        ? null
        : new SearchQuery($query, $this->getTermOptions($search));
    }
    elseif (is_string($search)) {
      $filtered = $this->filterStopWords($search);

      return $filtered == $search
        ? null
        : $filtered;
    }

    return null;
  }

  /**
   * @param SearchQuery $query
   * @return array
   */
  private function getTermOptions(SearchQuery $query): array {
    static $property;
    try {
      if (!isset($property)) {
        $property = new \ReflectionProperty(SearchQuery::class, '_defaultTermOptions');
      }

      return $property->getValue($query);
    } catch (Throwable) {
      return [];
    }
  }


  // Static methods
  // --------------

  /**
   * @return RemoveStopWords
   */
  public static function getInstance(): RemoveStopWords {
    return Plugin::getInstance()->removeStopWords;
  }
}
