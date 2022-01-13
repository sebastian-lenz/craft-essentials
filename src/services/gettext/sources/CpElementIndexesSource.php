<?php

namespace lenz\craft\essentials\services\gettext\sources;

use Craft;
use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\Entry;
use lenz\craft\essentials\services\gettext\utils\Translations;

/**
 * Class CpElementIndexes
 */
class CpElementIndexesSource extends AbstractSource
{
  /**
   * @var array
   */
  const ELEMENT_TYPES = [
    'assets' => Asset::class,
    'categories' => Category::class,
    'entries' => Entry::class,
  ];

  /**
   * @inheritDoc
   */
  public function extract(Translations $translations) {
    foreach (self::ELEMENT_TYPES as $name => $elementType) {
      $settings = Craft::$app->getElementIndexes()->getSettings($elementType);

      if (is_array($settings) && array_key_exists('sourceOrder', $settings)) {
        $this->extractSourceOrder($translations, $name, $settings['sourceOrder']);
      }
    }
  }


  // Private methods
  // ---------------

  /**
   * @param Translations $translations
   * @param string $name
   * @param array $sourceOrder
   */
  private function extractSourceOrder(Translations $translations, string $name, array $sourceOrder) {
    $hint = 'craft:element-index/' . $name;

    foreach ($sourceOrder as $row) {
      if ($row[0] == 'heading') {
        $this->insert($translations, $hint, $row[1]);
      }
    }
  }

  /**
   * @param Translations $translations
   * @param string $hint
   * @param string $original
   */
  private function insert(Translations $translations, string $hint, string $original) {
    $result = $translations->insertCp($original);
    if (!is_null($result)) {
      $result->addReference($hint);
    }
  }
}
