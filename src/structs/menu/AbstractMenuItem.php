<?php

namespace lenz\craft\essentials\structs\menu;

use craft\base\ElementInterface;
use craft\elements\Entry;
use lenz\craft\essentials\structs\structure\AbstractStructureItem;
use lenz\craft\utils\models\Attributes;
use yii\base\InvalidConfigException;

/**
 * Class AbstractMenuItem
 * @template T of AbstractMenu
 * @extends AbstractStructureItem<T>
 */
abstract class AbstractMenuItem extends AbstractStructureItem
{
  /**
   * @var array|null
   */
  public ?array $customLinkAttributes = null;

  /**
   * @var bool
   */
  public bool $isActive = false;

  /**
   * @var string|null
   */
  public ?string $sectionHandle = null;

  /**
   * @var int
   */
  public int $sectionId = 0;

  /**
   * @var string
   */
  public string $title = '';

  /**
   * @var string|null
   */
  public ?string $typeHandle = null;

  /**
   * @var int
   */
  public int $typeId = 0;

  /**
   * @var string|null
   */
  public ?string $url = null;


  /**
   * @param array $extraAttribs
   * @return Attributes
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
   * @param ElementInterface $element
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
