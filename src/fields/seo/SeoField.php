<?php

namespace lenz\craft\essentials\fields\seo;

use Craft;
use craft\base\ElementInterface;
use lenz\craft\essentials\records\SeoRecord;
use lenz\craft\utils\foreignField\ForeignField;
use lenz\craft\utils\foreignField\ForeignFieldModel;

/**
 * Class SeoField
 */
class SeoField extends ForeignField
{
  /**
   * @inheritDoc
   */
  public function getSearchKeywords($value, ElementInterface $element): string {
    return $value instanceof SeoModel
      ? $value->getSearchKeywords()
      : '';
  }


  // Static methods
  // --------------

  /**
   * @inheritdoc
   */
  public static function displayName(): string {
    return Craft::t('site', 'SEO data');
  }

  /**
   * @inheritDoc
   */
  public static function inputTemplate(): string {
    return 'lenz-craft-essentials/_seo/field';
  }

  /**
   * @inheritDoc
   */
  public static function modelClass(): string {
    return SeoModel::class;
  }

  /**
   * @inheritDoc
   */
  public static function recordClass(): string {
    return SeoRecord::class;
  }

  /**
   * @inheritDoc
   */
  public static function recordModelAttributes(): array {
    return [
      'enabled',
      'description',
      'keywords',
    ];
  }

  /**
   * @param ForeignFieldModel $model
   * @param ElementInterface $element
   * @return array
   */
  protected function toRecordAttributes(ForeignFieldModel $model, ElementInterface $element): array {
    $result = parent::toRecordAttributes($model, $element);
    $result['enabled'] = $result['enabled'] ? 1 : 0;
    return $result;
  }
}
