<?php

namespace lenz\craft\essentials\services\cp\elements;

use Craft;
use craft\base\ElementInterface;
use craft\fieldlayoutelements\BaseUiElement;
use craft\helpers\Cp;
use craft\helpers\Html;
use craft\web\View;
use Throwable;

/**
 * Class Column
 */
class Column extends BaseUiElement
{
  /**
   * @var bool
   */
  public bool $isCloser = false;

  /**
   * @var string
   */
  static string $STACK = '';


  /**
   * @inheritdoc
   */
  protected function selectorLabel(): string {
    return $this->isCloser ? 'End Columns' : 'Column';
  }

  /**
   * @inheritdoc
   */
  protected function selectorIcon(): ?string {
    return '@appicons/folder-open.svg';
  }

  /**
   * @inheritdoc
   */
  public function hasCustomWidth(): bool {
    return true;
  }

  /**
   * @inheritdoc
   */
  protected function settingsHtml(): ?string {
    return Cp::lightswitchFieldHtml([
      'label' => Craft::t('app', 'Is closing element'),
      'id' => 'isCloser',
      'name' => 'isCloser',
      'value' => $this->isCloser,
    ]);
  }

  /**
   * @inheritdoc
   */
  public function formHtml(?ElementInterface $element = null, bool $static = false): ?string {
    $result = self::$STACK;
    self::$STACK = '';

    if (!$this->isCloser)   {
      $result .= '<div class="field width-' . ($this->width ?? 100) . '">';
      self::$STACK .= '</div>';
    }

    return '<div style="display:none;"></div>' . $result;
  }
}
