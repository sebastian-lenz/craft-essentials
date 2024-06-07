<?php

namespace lenz\craft\essentials\services\gettext\sources;

use Craft;
use craft\base\FieldInterface;
use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\Entry;
use craft\fieldlayoutelements\BaseField;
use craft\fieldlayoutelements\Heading;
use craft\fieldlayoutelements\Tip;
use craft\fields\BaseOptionsField;
use craft\fields\Matrix;
use craft\models\FieldLayoutTab;
use craft\records\FieldLayout;
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
    $layoutIds = FieldLayout::find()->select('id')->column();

    foreach ($layoutIds as $layoutId) {
      $layout = Craft::$app->getFields()->getLayoutById($layoutId);

      foreach ($layout->getTabs() as $tab) {
        $this->extractFieldLayoutTab($translations, $tab);
      }
    }

    foreach (Craft::$app->getFields()->getAllFields() as $field) {
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
    }
  }

  /**
   * @param Translations $translations
   * @param FieldLayoutTab $tab
   * @return void
   */
  private function extractFieldLayoutTab(Translations $translations, FieldLayoutTab $tab): void {
    $hint = $this->getTabHint($tab);
    $this->insert($translations, $hint, $tab->name);

    foreach ($tab->elements as $element) {
      if ($element instanceof BaseField) {
        $this->insert($translations, $hint, $element->label);
        $this->insert($translations, $hint, $element->instructions);
        $this->insert($translations, $hint, $element->tip);
        $this->insert($translations, $hint, $element->warning);
      } elseif ($element instanceof Heading) {
        $this->insert($translations, $hint, $element->heading);
      } elseif ($element instanceof Tip) {
        $this->insert($translations, $hint, $element->tip);
      }
    }
  }

  /**
   * @param FieldLayoutTab $tab
   * @return string
   */
  private function getAssetTabHint(FieldLayoutTab $tab): string {
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
  private function getCategoryTabHint(FieldLayoutTab $tab): string {
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
  private function getEntryTabHint(FieldLayoutTab $tab): string {
    foreach (Craft::$app->getEntries()->getAllEntryTypes() as $entryType) {
      if ($entryType->fieldLayoutId == $tab->layoutId) {
        return 'tab/entryType/' . $entryType->handle;
      }
    }

    return 'tab/entry';
  }

  /**
   * @param FieldLayoutTab $tab
   * @return string
   */
  private function getTabHint(FieldLayoutTab $tab): string {
    return match ($tab->elementType ?? null) {
      Asset::class => $this->getAssetTabHint($tab),
      Category::class => $this->getCategoryTabHint($tab),
      Entry::class => $this->getEntryTabHint($tab),
      default => 'tab',
    };
  }

  /**
   * @param Translations $translations
   * @param string|FieldInterface $fieldOrHint
   * @param string|null|mixed $original
   */
  private function insert(Translations $translations, string|FieldInterface $fieldOrHint, mixed $original): void {
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
