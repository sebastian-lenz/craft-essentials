<?php

namespace lenz\craft\essentials\structs\menu;

use craft\base\ElementInterface;
use craft\elements\Entry;
use craft\helpers\Html;
use craft\helpers\Template;
use Exception;
use lenz\craft\essentials\structs\structure\AbstractStructureItem;
use Twig\Markup;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * Class AbstractMenuItem
 */
abstract class AbstractMenuItem extends AbstractStructureItem
{
  /**
   * @var string|array
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
   * @param array $attributes
   * @return Markup
   * @noinspection PhpUnused (Public/Template API)
   * @throws Exception
   */
  public function getLinkAttributes(array $attributes = []): Markup {
    if (isset($this->customLinkAttributes)) {
      if (is_string($this->customLinkAttributes)) {
        if (!empty($attributes) && $_ENV['ENVIRONMENT'] !== 'production') {
          throw new Exception('Cannot apply attributes when custom attributes are set to a string.');
        }

        return Template::raw($this->customLinkAttributes);
      } else {
        $attributes = array_merge($attributes, $this->customLinkAttributes);
      }
    } else {
      $attributes = array_merge($attributes, [
        'href' => $this->url
      ]);
    }

    if ($this->isActive) {
      $classNames = ArrayHelper::getValue($attributes, 'class', []);
      if (!is_array($classNames)) {
        $classNames = array_filter(preg_split('/\s+/', $classNames));
      }

      if (!in_array('active', $classNames)) {
        $classNames[] = 'active';
      }

      $attributes['class'] = $classNames;
    }

    return Template::raw(Html::renderTagAttributes($attributes));
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
