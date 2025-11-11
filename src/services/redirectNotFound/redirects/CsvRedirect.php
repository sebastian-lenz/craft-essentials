<?php

namespace lenz\craft\essentials\services\redirectNotFound\redirects;

use Craft;
use craft\base\ElementInterface;
use craft\web\Request;
use Generator;
use lenz\craft\essentials\events\RedirectUrlEvent;
use lenz\craft\essentials\services\redirectNotFound\formats\UrlFormat;
use lenz\craft\essentials\services\redirectNotFound\utils\ElementRef;
use lenz\craft\essentials\services\redirectNotFound\utils\ElementRoute;
use lenz\craft\utils\models\Url;
use yii\base\Event;

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
   * @var string
   */
  const EVENT_COLLECT_REDIRECT_URLS = 'collectRedirectUrls';


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
    Event::trigger(__CLASS__, self::EVENT_COLLECT_REDIRECT_URLS, $event = new RedirectUrlEvent([
      'requestUrl' => $request->url,
      'urls' => [$request->url],
    ]));

    foreach ($event->urls as $original) {
      $original = $original instanceof Url ? $original : new Url($original);
      $target = $this->findTarget($original);
      if (is_null($target)) {
        continue;
      }

      [$url, $code] = $target;
      $url = UrlFormat::decodeUrl($url);
      if (empty($url)) {
        continue;
      }

      $this->sendRedirect(Url::compose($url, $original->getQuery()), $code);
      return true;
    }

    return false;
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
