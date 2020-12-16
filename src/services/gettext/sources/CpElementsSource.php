<?php

namespace lenz\craft\essentials\services\gettext\sources;

use Craft;
use craft\base\Field;
use craft\models\Section;
use lenz\craft\essentials\services\gettext\utils\Translations;

/**
 * Class CpElementsSource
 */
class CpElementsSource extends AbstractSource
{
  /**
   * @inheritDoc
   */
  public function extract(Translations $translations) {
    foreach (Craft::$app->getCategories()->getAllGroups() as $group) {
      $this->insert($translations, 'craft:category/' . $group->handle, $group->name);
    }

    foreach (Craft::$app->getSections()->getAllSections() as $section) {
      $this->extractSection($translations, $section);
    }

    foreach (Craft::$app->getSites()->getAllSites() as $site) {
      $this->insert($translations, 'craft:sites/' . $site->handle, $site->name);
    }
  }

  // Private methods
  // ---------------

  /**
   * @param Translations $translations
   * @param Section $section
   */
  private function extractSection(Translations $translations, Section $section) {
    $hint = 'craft:section/' . $section->handle;
    $this->insert($translations, $hint, $section->name);

    foreach ($section->getEntryTypes() as $entryType) {
      $this->insert($translations, $hint, $entryType->name);
    }
  }

  /**
   * @param Translations $translations
   * @param Field $field
   * @param string $original
   */
  private function insert(Translations $translations, string $hint, $original) {
    $result = $translations->insertCp($original);
    if (!is_null($result)) {
      $result->addReference($hint);
    }
  }
}
