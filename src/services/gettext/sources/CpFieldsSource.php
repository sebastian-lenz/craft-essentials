<?php

namespace lenz\craft\essentials\services\gettext\sources;

use Craft;
use craft\base\Field;
use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\Entry;
use craft\fields\BaseOptionsField;
use craft\fields\Matrix;
use craft\records\FieldLayoutTab;
use lenz\craft\essentials\services\gettext\utils\Translations;

/**
 * Class CpFieldsSource
 */
class CpFieldsSource extends AbstractSource
{
  /**
   * @inheritDoc
   */
  public function extract(Translations $translations) {
    foreach (FieldLayoutTab::find()->all() as $tab) {
      $this->insert($translations, $this->getTabHint($tab), $tab->name);
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
      $this->insert($translations, $field, $option['label']);
    }
  }

  /**
   * @param Translations $translations
   * @param Field $field
   */
  private function extractField(Translations $translations, Field $field) {
    $this->insert($translations, $field, $field->name);
    $this->insert($translations, $field, $field->instructions);

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
      $this->insert($translations, $field, $field->name);

      foreach ($blockType->getFields() as $field) {
        $this->extractField($translations, $field);
      }
    }
  }

  /**
   * @param FieldLayoutTab $tab
   * @return string
   */
  private function getAssetTabHint(FieldLayoutTab $tab) {
    foreach (Craft::$app->getVolumes()->getAllVolumes() as $volume) {
      if ($volume->fieldLayoutId == $tab->layoutId) {
        return 'tab/volume/' . $volume->handle;
      }
    }

    return 'tab/volume';
  }

  /**
   * @param FieldLayoutTab $tab
   * @return string
   */
  private function getCategoryTabHint(FieldLayoutTab $tab) {
    foreach (Craft::$app->getCategories()->getAllGroups() as $group) {
      if ($group->fieldLayoutId == $tab->layoutId) {
        return 'tab/category/' . $group->handle;
      }
    }

    return 'tab/category';
  }

  /**
   * @param FieldLayoutTab $tab
   * @return string
   */
  private function getEntryTabHint(FieldLayoutTab $tab) {
    foreach (Craft::$app->getSections()->getAllSections() as $section)
    foreach ($section->entryTypes as $entryType) {
      if ($entryType->fieldLayoutId == $tab->layoutId) {
        return 'tab/entry/' . $section->handle . '/' . $entryType->handle;
      }
    }

    return 'tab/entry';
  }

  /**
   * @param FieldLayoutTab $tab
   * @return string
   */
  private function getTabHint(FieldLayoutTab $tab) {
    $layout = $tab->layout;
    switch ($layout ? $layout->type : null) {
      case Asset::class:
        return $this->getAssetTabHint($tab);
      case Category::class:
        return $this->getCategoryTabHint($tab);
      case Entry::class:
        return $this->getEntryTabHint($tab);
    }

    return 'tab';
  }

  /**
   * @param Translations $translations
   * @param Field|null $field
   * @param string|null|mixed $original
   */
  private function insert(Translations $translations, $fieldOrHint, $original) {
    $result = $translations->insertCp($original);
    if (!is_null($result)) {
      $reference = 'craft:fields';
      if ($fieldOrHint instanceof Field) {
        $reference .= '/' . $fieldOrHint->handle;
      } elseif (is_string($fieldOrHint)) {
        $reference .= '/' . $fieldOrHint;
      }

      $result->addReference($reference);
    }
  }
}
