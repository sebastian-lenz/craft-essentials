<?php

namespace lenz\craft\essentials\services\gettext\sources;

use Craft;
use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\Entry;
use craft\services\ElementSources;
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
  public function extract(Translations $translations): void {
    foreach (self::ELEMENT_TYPES as $name => $elementType) {
      $hint = 'craft:element-sources/' . $name;
      $sources = Craft::$app->getElementSources()->getSources($elementType);

      foreach ($sources as $source) {
        if ($source['type'] === ElementSources::TYPE_HEADING && !empty($source['heading'])) {
          $this->insert($translations, $hint, $source['heading']);
        }
      }
    }
  }


  // Private methods
  // ---------------

  /**
   * @param Translations $translations
   * @param string $hint
   * @param string $original
   */
  private function insert(Translations $translations, string $hint, string $original): void {
    $result = $translations->insertCp($original);
    if (!is_null($result)) {
      $result->addReference($hint);
    }
  }
}
