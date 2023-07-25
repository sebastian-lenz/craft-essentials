<?php

namespace lenz\craft\essentials\services\redirectNotFound\redirects;

use Craft;
use craft\base\ElementInterface;
use craft\web\Request;
use Generator;
use lenz\craft\essentials\services\redirectNotFound\formats\UrlFormat;
use lenz\craft\essentials\services\redirectNotFound\utils\ElementRoute;
use lenz\craft\utils\models\Url;

/**
 * Class CsvRedirect
 */
class CsvRedirect extends AbstractRedirect implements AppendableRedirect, ElementRoutesRedirect
{
  /**
   * @var string
   */
  private string $_fileName;


  /**
   * CsvRedirect constructor.
   *
   * @param string $fileName
   */
  public function __construct(string $fileName) {
    $this->_fileName = $fileName;
  }

  /**
   * @param string $origin
   * @param ElementInterface $target
   * @return void
   */
  public function append(string $origin, ElementInterface $target): void {
    $exists = file_exists($this->_fileName);
    $handle = fopen($this->_fileName, 'a');

    if (!$exists) {
      fputcsv($handle, ['source', 'target']);
    }

    fputcsv($handle, [$origin, "#entry:{$target->id}@{$target->siteId}"]);
    fclose($handle);
  }

  /**
   * @inheritDoc
   */
  public function delete(ElementRoute $route): void {
    $swapFile = $this->_fileName . '.swp';
    if (file_exists($swapFile)) {
      unlink($swapFile);
    }

    $handle = fopen($swapFile, 'w');
    foreach ($this->eachRow() as $index => $row) {
      if ($index == $route->originId && $row[0] == $route->url) {
        continue;
      }

      fputcsv($handle, $row);
    }

    fclose($handle);
    if (file_exists($this->_fileName)) {
      unlink($this->_fileName);
    }

    rename($swapFile, $this->_fileName);
  }

  /**
   * @inheritDoc
   */
  public function getElementRoutes(ElementInterface $element): array {
    $result = [];
    foreach ($this->eachRow() as $index => $data) {
      if (!str_starts_with($data[1], '#entry:')) {
        continue;
      }

      list($id, $siteId) = array_pad(
        explode('@', substr($data[1], 7), 2)
      , 2, null);

      if ($element->id != $id || $element->siteId != $siteId) {
        continue;
      }

      $result[] = new ElementRoute([
        'origin' => Craft::t('lenz-craft-essentials', 'Custom redirect'),
        'originId' => $index,
        'redirect' => $this,
        'uid' => md5( implode(';', [$this->_fileName, $index])),
        'url' => $data[0],
      ]);
    }

    return $result;
  }

  /**
   * @param Request $request
   * @return bool
   */
  public function redirect(Request $request): bool {
    $original = new Url($request->url);
    $target = UrlFormat::decodeUrl(
      $this->findTarget($original)
    );

    if (empty($target)) {
      return false;
    }

    $this->sendRedirect(Url::compose($target, $original->getQuery()));
    return true;
  }


  // Protected methods
  // -----------------

  /**
   * @return Generator
   */
  protected function eachRow(): Generator {
    $handle = fopen($this->_fileName, 'r');
    $index = 0;
    while (($data = fgetcsv($handle, 1000)) !== false) {
      if (count($data) >= 2) {
        yield $index => $data;
      }

      $index += 1;
    }

    fclose($handle);
  }

  /**
   * @param Url $url
   * @return string|null
   */
  protected function findTarget(Url $url): ?string {
    $variants = [trim((string)$url, '/')];

    if (!empty($url->query)) {
      $url = clone $url;
      $url->query = null;
      $variants[] = trim((string)$url, '/');
    }

    foreach ($this->eachRow() as $data) {
      $pattern = trim($data[0], '/');
      if (in_array($pattern, $variants)) {
        return trim($data[1]);
      }
    }

    return null;
  }
}
