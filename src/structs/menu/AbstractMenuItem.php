<?php

namespace lenz\craft\essentials\structs\menu;

use craft\base\ElementInterface;
use craft\elements\Entry;
use craft\helpers\Html;
use craft\helpers\Template;
use Twig\Markup;

/**
 * Class AbstractMenuItem
 */
abstract class AbstractMenuItem
{
  /**
   * @var string
   */
  public $customLinkAttributes;

  /**
   * @var int
   */
  public $id;

  /**
   * @var int|false
   */
  public $parentId = false;

  /**
   * @var string
   */
  public $title;

  /**
   * @var int
   */
  public $sectionId;

  /**
   * @var int
   */
  public $typeId;

  /**
   * @var string
   */
  public $url;


  /**
   * AbstractMenuItem constructor.
   * @param ElementInterface|array $config
   */
  function __construct($config) {
    if ($config instanceof ElementInterface) {
      $this->setElement($config);
    } elseif (is_array($config)) {
      \Yii::configure($this, $config);
    }
  }

  /**
   * @param bool $includeSelf
   * @return AbstractMenuItem[]
   */
  public function getAncestors($includeSelf = false) {
    return AbstractMenu::getInstance()->getAncestors($this, $includeSelf);
  }

  /**
   * @return AbstractMenuItem[]
   */
  public function getChildren() {
    return AbstractMenu::getInstance()->getChildren($this->id);
  }

  /**
   * @return Markup
   */
  public function getLinkAttributes() {
    if (isset($this->customLinkAttributes)) {
      return Template::raw($this->customLinkAttributes);
    }

    return Template::raw(Html::renderTagAttributes([
      'href' => $this->url
    ]));
  }

  /**
   * @return AbstractMenuItem|null
   */
  public function getParent() {
    return $this->parentId == -1
      ? null
      : AbstractMenu::getInstance()->getById($this->parentId);
  }

  /**
   * @return bool
   */
  public function hasChildren() {
    return count($this->getChildren()) > 0;
  }


  // Protected methods
  // -----------------

  /**
   * @param ElementInterface $element
   */
  protected function setElement(ElementInterface $element) {
    $this->id = intval($element->getId());

    if ($element instanceof Entry) {
      $this->title     = $element->title;
      $this->sectionId = intval($element->sectionId);
      $this->typeId    = intval($element->typeId);
      $this->url       = $element->getUrl();
    }
  }


  // Static methods
  // --------------

  /**
   * @param ElementInterface[] $elements
   * @return array
   */
  static public function fromElements($elements) {
    $result = [];
    foreach ($elements as $element) {
      $result[] = new static($element);
    }

    return $result;
  }
}
