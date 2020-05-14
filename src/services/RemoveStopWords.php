<?php

namespace lenz\craft\essentials\services;

use craft\elements\db\ElementQuery;
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
  public $stopWords;

  /**
   * @var RemoveStopWords
   */
  static private $_instance;


  /**
   * RemoveStopWords constructor.
   */
  public function __construct() {
    parent::__construct();

    Event::on(
      ElementQuery::class,
      ElementQuery::EVENT_BEFORE_PREPARE,
      [$this, 'onBeforePrepare']
    );
  }

  /**
   * @return string[]
   */
  public function getStopWords() {
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
  public function onBeforePrepare(Event $event) {
    $query = $event->sender;
    if (!($query instanceof ElementQuery) || empty($query->search)) {
      return;
    }

    $search = $this->filterStopWords($query->search);
    if ($search == $query->search) {
      return;
    }

    $originalSearch = $query->search;
    $query->search = $search;
    $query->on(
      ElementQuery::EVENT_AFTER_PREPARE,
      function() use ($originalSearch, $query) {
        $query->search = $originalSearch;
      }
    );
  }



  // Static methods
  // --------------

  /**
   * @return RemoveStopWords
   */
  public static function getInstance() {
    if (!isset(self::$_instance)) {
      self::$_instance = new RemoveStopWords();
    }

    return self::$_instance;
  }
}
