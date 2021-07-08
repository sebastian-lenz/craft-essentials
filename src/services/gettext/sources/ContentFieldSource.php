<?php

namespace lenz\craft\essentials\services\gettext\sources;

use lenz\contentfield\models\fields\AbstractField;
use lenz\contentfield\models\fields\SelectField;
use lenz\contentfield\models\schemas\AbstractSchema;
use lenz\contentfield\models\schemas\AbstractSchemaContainer;
use lenz\contentfield\Plugin;
use lenz\craft\essentials\services\gettext\utils\Translations;

/**
 * Class ContentFieldSource
 */
class ContentFieldSource extends AbstractSource
{
  /**
   * @inheritDoc
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
   * @param AbstractSchema $schema
   * @param AbstractField $field
   */
  private function extractField(Translations $translations, AbstractSchema $schema, AbstractField $field) {
    $this->insert($translations, $schema, $field->label);
    $this->insert($translations, $schema, $field->instructions);

    if ($field instanceof SelectField) {
      $this->extractSelectField($translations, $schema, $field);
    }
  }

  /**
   * @param Translations $translations
   * @param AbstractSchema $schema
   */
  private function extractSchema(Translations $translations, AbstractSchema $schema) {
    $this->insert($translations, $schema, $schema->label);

    foreach ($schema->fields as $field) {
      $this->extractField($translations, $schema, $field);
    }

    if ($schema instanceof AbstractSchemaContainer) {
      foreach ($schema->getLocalStructures() as $structure) {
        $this->extractSchema($translations, $structure);
      }
    }
  }

  /**
   * @param Translations $translations
   * @param AbstractSchema $schema
   * @param SelectField $field
   */
  private function extractSelectField(Translations $translations, AbstractSchema $schema, SelectField $field) {
    $enumeration = $field->getEnumeration();
    foreach ($enumeration->getOptions() as $option) {
      $this->insert($translations, $schema, $option['label']);
    }
  }

  /**
   * @param Translations $translations
   * @param AbstractSchema $schema
   * @param string $original
   */
  private function insert(Translations $translations, AbstractSchema $schema, string $original) {
    $result = $translations->insertCp($original);
    if (!is_null($result)) {
      $result->addReference($schema->qualifier);
    }
  }
}
