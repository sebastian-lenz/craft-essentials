<?php

namespace lenz\craft\essentials\structs\menu;

use craft\base\ElementInterface;
use craft\elements\Entry;
use lenz\craft\essentials\structs\structure\AbstractStructureItem;
use lenz\craft\utils\models\Attributes;
use yii\base\InvalidConfigException;

/**
 * Class AbstractMenuItem
 *
 * @phpstan-template T of AbstractMenu
 * @phpstan-extends AbstractStructureItem<T>
 */
abstract class AbstractMenuItem extends AbstractStructureItem
{
  /**
   * @phpstan-var array|null
   */
  public ?array $customLinkAttributes = null;

  /**
   * @phpstan-var bool
   */
  public bool $isActive = false;

  /**
   * @phpstan-var string|null
   */
  public ?string $sectionHandle = null;

  /**
   * @phpstan-var int
   */
  public int $sectionId = 0;

  /**
   * @phpstan-var string
   */
  public string $title = '';

  /**
   * @phpstan-var string|null
   */
  public ?string $typeHandle = null;

  /**
   * @phpstan-var int
   */
  public int $typeId = 0;

  /**
   * @phpstan-var string|null
   */
  public ?string $url = null;


  /**
   * @phpstan-param array $extraAttribs
   * @phpstan-return Attributes
   * @noinspection PhpUnused (Public/Template API)
   */
  public function getLinkAttributes(array $extraAttribs = []): Attributes {
    $attr = new Attributes($this->customLinkAttributes ?? ['href' => $this->url]);
    $attr->set($extraAttribs);

    if ($this->isActive) {
      $attr->addClass('active');
    }

    return $attr;
  }


  // Protected methods
  // -----------------

  /**
   * @phpstan-param ElementInterface $element
   * @throws InvalidConfigException
   */
  protected function setElement(ElementInterface $element) {
    parent::setElement($element);
    $this->title = (string)$element;
    $this->url = $element->getUrl();

    if ($element instanceof Entry) {
      $this->sectionHandle = $element->getSection()->handle;
      $this->sectionId = intval($element->sectionId);
      $this->typeHandle = $element->getType()->handle;
      $this->typeId = intval($element->typeId);
    }
  }
}
