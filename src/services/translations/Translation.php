<?php

namespace lenz\craft\essentials\services\translations;

use craft\base\ElementInterface;
use craft\models\Site;
use lenz\craft\utils\models\Attributes;
use yii\base\BaseObject;

/**
 * Class Translation
 *
 * @property bool $isFallback
 * @property Attributes $linkAttributes
 * @property string $name
 * @property string $url
 */
class Translation extends BaseObject implements \ArrayAccess
{
  /**
   * @var bool
   */
  public bool $isCurrent = false;

  /**
   * @var Site|null
   */
  public ?Site $site = null;

  /**
   * @var ElementInterface|null
   */
  public ?ElementInterface $target = null;


  /**
   * @param Site $site
   * @param array $config
   */
  public function __construct(Site $site, array $config = []) {
    parent::__construct($config);
    $this->site = $site;
  }

  /**
   * @return Attributes
   */
  public function getLinkAttributes(): Attributes {
    return new Attributes([
      'href' => $this->getUrl(),
    ]);
  }

  /**
   * @return string
   */
  public function getName(): string {
    return $this->site ? $this->site->getName() : '';
  }

  /**
   * @return string
   */
  public function getUrl(): string {
    return is_null($this->target)
      ? $this->site->baseUrl
      : $this->target->getUrl();
  }

  /**
   * @return bool
   */
  public function getIsFallback(): bool {
    return is_null($this->target);
  }

  /**
   * @inheritDoc
   */
  public function offsetExists($offset): bool {
    return isset($this->$offset);
  }

  /**
   * @inheritDoc
   */
  public function offsetGet($offset) {
    return $this->$offset;
  }

  /**
   * @inheritDoc
   */
  public function offsetSet($offset, $value) {
    $this->$offset = $value;
  }

  /**
   * @inheritDoc
   */
  public function offsetUnset($offset) {
    unset($this->$offset);
  }
}
