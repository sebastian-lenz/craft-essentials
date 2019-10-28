<?php

namespace lenz\craft\essentials\services\gettext\sources;

use lenz\contentfield\models\fields\AbstractField;
use lenz\contentfield\models\fields\SelectField;
use lenz\contentfield\models\schemas\AbstractSchema;
use lenz\contentfield\Plugin;
use lenz\craft\essentials\services\gettext\Translations;

/**
 * Class ContentFieldSource
 */
class ContentFieldSource extends AbstractSource
{
  /**
   * @param Translations $translations
   */
  public function extract(Translations $translations) {
    $schemas = Plugin::getInstance()->schemas->getAllSchemas();
    foreach ($schemas as $schema) {
      $this->extractSchema($translations, $schema);
    }
  }

  // Private methods
  // ---------------

  /**
   * @param Translations $translations
   * @param AbstractField $field
   */
  private function extractField(Translations $translations, AbstractField $field) {
    $translations->insertCp($field->label);
    $translations->insertCp($field->instructions);

    if ($field instanceof SelectField) {
      $this->extractSelectField($translations, $field);
    }
  }

  /**
   * @param Translations $translations
   * @param AbstractSchema $schema
   */
  private function extractSchema(Translations $translations, AbstractSchema $schema) {
    $translations->insertCp($schema->label);

    foreach ($schema->fields as $field) {
      $this->extractField($translations, $field);
    }
  }

  /**
   * @param Translations $translations
   * @param SelectField $field
   */
  private function extractSelectField(Translations $translations, SelectField $field) {
    $enumeration = $field->getEnumeration();
    foreach ($enumeration->getOptions() as $option) {
      $translations->insertCp($option['label']);
    }
  }
}
