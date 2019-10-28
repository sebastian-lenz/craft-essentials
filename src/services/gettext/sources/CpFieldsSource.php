<?php

namespace lenz\craft\essentials\services\gettext\sources;

use Craft;
use craft\base\Field;
use craft\fields\BaseOptionsField;
use craft\fields\Matrix;
use craft\records\FieldLayoutTab;
use lenz\craft\essentials\services\gettext\Translations;

/**
 * Class CpFieldsSource
 */
class CpFieldsSource extends AbstractSource
{
  /**
   * @param Translations $translations
   */
  public function extract(Translations $translations) {
    foreach (FieldLayoutTab::find()->all() as $tab) {
      $translations->insertCp($tab->name);
    }

    foreach (Craft::$app->getFields()->getAllFields(false) as $field) {
      $this->extractField($translations, $field);
    }
  }

  // Private methods
  // ---------------

  /**
   * @param Translations $translations
   * @param BaseOptionsField $field
   */
  private function extractBaseOptionsField(Translations $translations, BaseOptionsField $field) {
    foreach ($field->options as $option) {
      $translations->insertCp($option['label']);
    }
  }

  /**
   * @param Translations $translations
   * @param Field $field
   */
  private function extractField(Translations $translations, Field $field) {
    $translations->insertCp($field->name);
    $translations->insertCp($field->instructions);

    if ($field instanceof BaseOptionsField) {
      $this->extractBaseOptionsField($translations, $field);
    } elseif ($field instanceof Matrix) {
      $this->extractMatrixField($translations, $field);
    }
  }

  /**
   * @param Translations $translations
   * @param Matrix $field
   */
  private function extractMatrixField(Translations $translations, Matrix $field) {
    foreach ($field->getBlockTypes() as $blockType) {
      $translations->insertCp($field->name);

      foreach ($blockType->getFields() as $field) {
        $this->extractField($translations, $field);
      }
    }
  }
}
