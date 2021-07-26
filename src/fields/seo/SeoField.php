<?php

namespace lenz\craft\essentials\fields\seo;

use Craft;
use craft\base\ElementInterface;
use lenz\craft\utils\foreignField\ForeignField;

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
      'description',
      'keywords',
    ];
  }
}
