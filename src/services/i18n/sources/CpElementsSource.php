<?php

namespace lenz\craft\essentials\services\i18n\sources;

use Craft;
use craft\models\Section;
use lenz\craft\essentials\services\i18n\Translations;

/**
 * Class CpElementsSource
 */
class CpElementsSource extends AbstractSource
{
  /**
   * @param Translations $translations
   */
  public function extract(Translations $translations) {
    foreach (Craft::$app->getCategories()->getAllGroups() as $group) {
      $translations->insertCp($group->name);
    }

    foreach (Craft::$app->getSections()->getAllSections() as $section) {
      $this->extractSection($translations, $section);
    }

    foreach (Craft::$app->getSites()->getAllSites() as $site) {
      $translations->insertCp($site->name);
    }
  }

  // Private methods
  // ---------------

  /**
   * @param Translations $translations
   * @param Section $section
   */
  private function extractSection(Translations $translations, Section $section) {
    $translations->insertCp($section->name);

    foreach ($section->getEntryTypes() as $entryType) {
      $translations->insertCp($entryType->name);
    }
  }
}
