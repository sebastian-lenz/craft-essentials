<?php

namespace lenz\craft\essentials\services\gettext\sources;

use Craft;
use craft\base\FieldInterface;
use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\Entry;
use craft\fieldlayoutelements\Heading;
use craft\fieldlayoutelements\Tip;
use craft\fields\BaseOptionsField;
use craft\fields\Matrix;
use craft\models\FieldLayoutTab;
use craft\records\FieldLayoutTab as FieldLayoutTabRecord;
use lenz\craft\essentials\services\gettext\utils\Translations;

/**
 * Class CpFieldsSource
 */
class CpFieldsSource extends AbstractSource
{
  /**
   * @inheritDoc
   */
  public function extract(Translations $translations): void {
    foreach (FieldLayoutTabRecord::find()->all() as $tab) {
      $this->extractFieldLayoutTab($translations, $tab);
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
  private function extractBaseOptionsField(Translations $translations, BaseOptionsField $field): void {
    foreach ($field->options as $option) {
      $this->insert($translations, $field, $option['label']);
    }
  }

  /**
   * @param Translations $translations
   * @param FieldInterface $field
   */
  private function extractField(Translations $translations, FieldInterface $field): void {
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
   * @param FieldLayoutTabRecord $record
   * @return void
   */
  private function extractFieldLayoutTab(Translations $translations, FieldLayoutTabRecord $record): void {
    $hint = $this->getTabHint($record);
    $this->insert($translations, $hint, $record->name);

    $model = FieldLayoutTab::createFromConfig($record->getAttributes([
      'id', 'layoutId', 'name', 'elements', 'sortOrder', 'uid'
    ]));

    foreach ($model->elements as $element) {
      if ($element instanceof Heading) {
        $this->insert($translations, $hint, $element->heading);
      } elseif ($element instanceof Tip) {
        $this->insert($translations, $hint, $element->tip);
      }
    }
  }

  /**
   * @param Translations $translations
   * @param Matrix $field
   */
  private function extractMatrixField(Translations $translations, Matrix $field): void {
    foreach ($field->getBlockTypes() as $blockType) {
      $this->insert($translations, $field, $field->name);

      foreach ($blockType->getCustomFields() as $field) {
        $this->extractField($translations, $field);
      }
    }
  }

  /**
   * @param FieldLayoutTabRecord $record
   * @return string
   */
  private function getAssetTabHint(FieldLayoutTabRecord $record): string {
    foreach (Craft::$app->getVolumes()->getAllVolumes() as $volume) {
      if ($volume->fieldLayoutId == $record->layoutId) {
        return 'tab/volume/' . $volume->handle;
      }
    }

    return 'tab/volume';
  }

  /**
   * @param FieldLayoutTabRecord $record
   * @return string
   */
  private function getCategoryTabHint(FieldLayoutTabRecord $record): string {
    foreach (Craft::$app->getCategories()->getAllGroups() as $group) {
      if ($group->fieldLayoutId == $record->layoutId) {
        return 'tab/category/' . $group->handle;
      }
    }

    return 'tab/category';
  }

  /**
   * @param FieldLayoutTabRecord $record
   * @return string
   */
  private function getEntryTabHint(FieldLayoutTabRecord $record): string {
    foreach (Craft::$app->getSections()->getAllSections() as $section)
    foreach ($section->entryTypes as $entryType) {
      if ($entryType->fieldLayoutId == $record->layoutId) {
        return 'tab/entry/' . $section->handle . '/' . $entryType->handle;
      }
    }

    return 'tab/entry';
  }

  /**
   * @param FieldLayoutTabRecord $record
   * @return string
   */
  private function getTabHint(FieldLayoutTabRecord $record): string {
    $layout = $record->layout;

    return match ($layout?->type) {
      Asset::class => $this->getAssetTabHint($record),
      Category::class => $this->getCategoryTabHint($record),
      Entry::class => $this->getEntryTabHint($record),
      default => 'tab',
    };
  }

  /**
   * @param Translations $translations
   * @param string|FieldInterface $fieldOrHint
   * @param string|null|mixed $original
   */
  private function insert(Translations $translations, string|FieldInterface $fieldOrHint, mixed $original) {
    if (!is_string($original) || empty($original)) {
      return;
    }

    $result = $translations->insertCp($original);
    if (!is_null($result)) {
      $reference = 'craft:fields';

      if ($fieldOrHint instanceof FieldInterface) {
        $reference .= '/' . $fieldOrHint->handle;
      } else {
        $reference .= '/' . $fieldOrHint;
      }

      $result->addReference($reference);
    }
  }
}
