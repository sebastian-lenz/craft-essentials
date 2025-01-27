<?php

namespace lenz\craft\essentials\services\cp\elements;

use Craft;
use craft\base\ElementInterface;
use craft\fieldlayoutelements\BaseUiElement;
use craft\helpers\Cp;
use craft\helpers\Html;
use craft\web\View;
use lenz\craft\essentials\assets\CpAsset;
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
   * @var bool
   */
  static bool $IN_COLUMN = false;

  /**
   * @var string
   */
  static string $STACK_HTML = '';

  /**
   * @var array
   */
  static array $STACK_IDS = [];


  /**
   * @inheritdoc
   */
  public function hasCustomWidth(): bool {
    return true;
  }

  /**
   * @inheritdoc
   */
  public function formHtml(?ElementInterface $element = null, bool $static = false): ?string {
    CpAsset::autoRegister();

    return $this->isAjaxForm()
      ? $this->renderAjaxHtml()
      : $this->renderStaticHtml();
  }


  // Protected methods
  // -----------------

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
  protected function settingsHtml(): ?string {
    return Cp::lightswitchFieldHtml([
      'label' => Craft::t('app', 'Is closing element'),
      'id' => 'isCloser',
      'name' => 'isCloser',
      'on' => $this->isCloser,
    ]);
  }


  // Private methods
  // ---------------

  /**
   * @return bool
   */
  private function isAjaxForm(): bool {
    return \Craft::$app->getRequest()->isAjax;
  }

  /**
   * @return string
   */
  private function renderAjaxHtml(): string {
    $id = uniqid('ceColumn');
    if ($this->isCloser) {
      Craft::$app->getView()->registerJs('craftEssentials.registerAjaxColumns(' . json_encode([
        'columns' => self::$STACK_IDS,
        'id' => $id,
      ]) . ');');

      self::$STACK_IDS[] = [];
      return Html::tag('div', '', [
        'class' => 'ceGrid__row',
        'id' => $id,
      ]);
    }

    self::$STACK_IDS[] = $id;
    return Html::tag('div', '', [
      'class' => 'ceGrid__column width-' . ($this->width ?? 100),
      'id' => $id,
    ]);
  }

  /**
   * @return string
   */
  private function renderStaticHtml(): string {
    $result = self::$STACK_HTML;
    self::$STACK_HTML = '';

    if (!$this->isCloser) {
      if (!self::$IN_COLUMN) {
        self::$IN_COLUMN = true;
        $result .= '<div class="ceGrid__row">';
      }

      $result .= '<div class="ceGrid__column width-' . ($this->width ?? 100) . '">';
      self::$STACK_HTML .= '</div>';
    } else if (self::$IN_COLUMN) {
      self::$IN_COLUMN = false;
      $result .= '</div>';
    }

    return '<span class="ceGrid__dummy"></span>' . $result;
  }
}
