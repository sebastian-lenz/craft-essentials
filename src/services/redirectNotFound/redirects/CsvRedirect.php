<?php

namespace lenz\craft\essentials\services\redirectNotFound\redirects;

use Craft;
use craft\base\ElementInterface;
use craft\web\Request;
use Generator;
use lenz\craft\essentials\services\redirectNotFound\formats\UrlFormat;
use lenz\craft\essentials\services\redirectNotFound\utils\ElementRef;
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

    fputcsv($handle, [$origin, (string)ElementRef::fromElement($target)]);
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
      $ref = ElementRef::parse($data[1]);
      if (!$ref || !$ref->matches($element)) {
        continue;
      }

      $result[] = new ElementRoute([
        'origin' => Craft::t('lenz-craft-essentials', 'Custom redirect'),
        'originId' => $index,
        'redirect' => $this,
        'uid' => md5(implode(';', [$this->_fileName, $index])),
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
    $target = $this->findTarget($original);
    if (is_null($target)) {
      return false;
    }

    [$url, $code] = $target;
    $url = UrlFormat::decodeUrl($url);
    if (empty($url)) {
      return false;
    }

    $this->sendRedirect(Url::compose($url, $original->getQuery()), $code);
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
   * @return array|null
   */
  protected function findTarget(Url $url): array|null {
    $variants = [trim((string)$url, '/')];

    if (!empty($url->query)) {
      $url = clone $url;
      $url->query = null;
      $variants[] = trim((string)$url, '/');
    }

    foreach ($this->eachRow() as $data) {
      $pattern = trim($data[0], '/');
      if (in_array($pattern, $variants)) {
        $code = isset($data[2]) && is_numeric($data[2])
          ? intval($data[2])
          : 301;

        return [trim($data[1]), $code];
      }
    }

    return null;
  }
}
