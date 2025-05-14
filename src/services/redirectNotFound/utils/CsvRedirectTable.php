<?php

/** @noinspection PhpUnused */

namespace lenz\craft\essentials\services\redirectNotFound\utils;

use Craft;
use craft\helpers\UrlHelper;
use lenz\craft\essentials\services\redirectNotFound\formats\UrlFormat;
use lenz\craft\essentials\services\redirectNotFound\redirects\AbstractRedirect;
use lenz\craft\essentials\services\tables\AbstractCsvTable;
use lenz\craft\essentials\services\tables\Column;
use lenz\craft\essentials\services\tables\Row;

/**
 * Class CsvRedirectTable
 */
class CsvRedirectTable extends AbstractCsvTable
{
  /**
   * @var bool
   */
  public bool $hasStatusCode = false;


  /**
   * @inheritDoc
   */
  public function getColumns(): array {
    $columns = [
      'source' => new Column([
        'title' => Craft::t('lenz-craft-essentials', 'Source'),
      ]),
      'target' => new Column([
        'title' => Craft::t('lenz-craft-essentials', 'Target'),
      ]),
    ];

    if ($this->hasStatusCode) {
      $columns['code'] = new Column([
        'source' => array_values($this->getCodeOptions()),
        'title' => Craft::t('lenz-craft-essentials', 'Code'),
        'type' => 'dropdown',
        'width' => 40,
      ]);
    }

    return $columns;
  }

  /**
   * @inheritDoc
   */
  function getFileName(): string {
    return '@storage/tables/redirects.csv';
  }

  /**
   * @return string
   */
  function getLabel(): string {
    return Craft::t('lenz-craft-essentials', 'Redirects');
  }


  // Protected methods
  // -----------------

  /**
   * @inheritDoc
   */
  protected function createRow(array $attributes): ?Row {
    $isEmpty = true;
    foreach ($attributes as $key => $value) {
      if ($key == 'code') {
        $options = $this->getCodeOptions();
        $attributes[$key] = array_key_exists($value, $options) ? $options[$value] : $value;
      } else {
        $value = trim(trim($value), '/');
        if (!UrlFormat::isUrlFormat($value) && !UrlHelper::isFullUrl($value)) {
          $value = '/' . $value;
        }

        $attributes[$key] = $value;
        if (!empty($value)) {
          $isEmpty = false;
        }
      }
    }

    return $isEmpty
      ? null
      : parent::createRow($attributes);
  }

  /**
   * @inheritDoc
   */
  protected function createRowFromInput(array $columns, array $data): ?Row {
    if (array_key_exists('target', $data) && !empty($data['target'])) {
      $data['target'] = UrlFormat::encodeUrl($data['target']);
    }

    return parent::createRowFromInput($columns, $data);
  }

  /**
   * @param Row[] $rows
   * @return Row[]
   */
  protected function filterRows(array $rows): array {
    usort($rows, function(Row $lft, Row $rgt) {
      return strcmp($lft['source'], $rgt['source']);
    });

    return $rows;
  }

  /**
   * @return array
   */
  protected function getCodeOptions(): array {
    static $options;
    if (!isset($options)) {
      $options = [
        '301' => Craft::t('lenz-craft-essentials', 'Permanent (301)'),
        '302' => Craft::t('lenz-craft-essentials', 'Temporary (302)'),
      ];
    }

    return $options;
  }

  /**
   * @inheritDoc
   */
  protected function getSaveRowData(Row $row, array $attributes): array {
    if (array_key_exists('code', $attributes)) {
      $attributes['code'] = self::parseCode($attributes['code']);
    }

    return parent::getSaveRowData($row, $attributes);
  }


  // Static methods
  // --------------

  /**
   * @param mixed $value
   * @return int
   */
  static public function parseCode(mixed $value): int {
    $code = AbstractRedirect::DEFAULT_REDIRECT_CODE;
    if (is_numeric($value)) {
      $code = intval($value);
    } elseif (preg_match('/\((\d+)\)/', $value, $match)) {
      $code = intval($match[1]);
    }

    return in_array($code, AbstractRedirect::REDIRECT_CODES)
      ? $code
      : AbstractRedirect::DEFAULT_REDIRECT_CODE;
  }
}
