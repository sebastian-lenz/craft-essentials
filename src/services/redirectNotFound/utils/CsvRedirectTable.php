<?php

/** @noinspection PhpUnused */

namespace lenz\craft\essentials\services\redirectNotFound\utils;

use Craft;
use craft\helpers\UrlHelper;
use lenz\craft\essentials\services\redirectNotFound\redirects\CsvRedirect;
use lenz\craft\essentials\services\tables\AbstractCsvTable;
use lenz\craft\essentials\services\tables\Column;
use lenz\craft\essentials\services\tables\Row;

/**
 * Class CsvRedirectTable
 */
class CsvRedirectTable extends AbstractCsvTable
{
  /**
   * @inheritDoc
   */
  public function getColumns(): array {
    return [
      'source' => new Column([
        'title' => Craft::t('lenz-craft-essentials', 'Source'),
      ]),
      'target' => new Column([
        'title' => Craft::t('lenz-craft-essentials', 'Target'),
      ]),
    ];
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
      $value = trim(trim($value), '/');
      if (!CsvRedirect::isHandler($value) && !UrlHelper::isFullUrl($value)) {
        $value = '/' . $value;
      }

      $attributes[$key] = $value;
      if (!empty($value)) {
        $isEmpty = false;
      }
    }

    return $isEmpty
      ? null
      : parent::createRow($attributes);
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
}
