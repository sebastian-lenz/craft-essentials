<?php

namespace lenz\craft\essentials\structs\menu;

use craft\base\ElementInterface;
use craft\elements\Entry;
use craft\helpers\Html;
use craft\helpers\Template;
use lenz\craft\essentials\structs\structure\AbstractStructureItem;
use Twig\Markup;
use yii\base\InvalidConfigException;

/**
 * Class AbstractMenuItem
 */
abstract class AbstractMenuItem extends AbstractStructureItem
{
  /**
   * @var string
   */
  public $customLinkAttributes;

  /**
   * @var bool
   */
  public $isActive = false;

  /**
   * @var string
   */
  public $sectionHandle;

  /**
   * @var int
   */
  public $sectionId;

  /**
   * @var string
   */
  public $title;

  /**
   * @var string
   */
  public $typeHandle;

  /**
   * @var int
   */
  public $typeId;

  /**
   * @var string
   */
  public $url;


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


  // Protected methods
  // -----------------

  /**
   * @param ElementInterface $element
   * @throws InvalidConfigException
   */
  protected function setElement(ElementInterface $element) {
    parent::setElement($element);

    if ($element instanceof ElementInterface) {
      $this->title = (string)$element;
      $this->url   = $element->getUrl();
    }

    if ($element instanceof Entry) {
      $this->sectionHandle = $element->getSection()->handle;
      $this->sectionId     = intval($element->sectionId);
      $this->typeHandle    = $element->getType()->handle;
      $this->typeId        = intval($element->typeId);
    }
  }
}
