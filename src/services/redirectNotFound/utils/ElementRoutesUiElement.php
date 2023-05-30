<?php

namespace lenz\craft\essentials\services\redirectNotFound\utils;

use Craft;
use craft\base\ElementInterface;
use craft\fieldlayoutelements\BaseUiElement;
use craft\helpers\ArrayHelper;
use craft\models\Site;
use lenz\craft\essentials\services\redirectNotFound\RedirectNotFound;
use lenz\craft\essentials\services\redirectNotFound\redirects\AppendableRedirect;
use lenz\craft\essentials\services\redirectNotFound\redirects\ElementRoutesRedirect;
use Throwable;
use function Sodium\compare;

/**
 * Class RedirectsUiElement
 */
class ElementRoutesUiElement extends BaseUiElement
{
  /**
   * @var ElementRoutesRedirect[]
   */
  private array $_redirects;


  /**
   * @inheritDoc
   */
  public function formHtml(?ElementInterface $element = null, bool $static = false): ?string {
    $routes = $element ? $this->getElementRoutes($element) : [];
    usort($routes, fn(ElementRoute $lft, ElementRoute $rgt) => strcmp($lft->url, $rgt->url));

    return Craft::$app->getView()->renderTemplate(
      'lenz-craft-essentials/_redirects/element-routes',
      [
        'canAppend' => $this->canAppend($element),
        'element' => $element,
        'routes' => $routes,
      ]
    );
  }

  /**
   * @param ElementInterface $element
   * @param string $origin
   * @return void
   */
  public function tryAppend(ElementInterface $element, string $origin): void {
    $this->getAppendableRedirect($element)?->append($origin, $element);
  }

  /**
   * @param ElementInterface $element
   * @param string $uid
   * @return void
   */
  public function tryDelete(ElementInterface $element, string $uid): void {
    foreach ($this->getElementRoutes($element) as $route) {
      if ($route->uid == $uid) {
        $route->delete();
        break;
      }
    }
  }

  // Protected methods
  // -----------------

  /**
   * @param ElementInterface|null $element
   * @return bool
   */
  protected function canAppend(?ElementInterface $element = null): bool {
    return $element && !is_null($this->getAppendableRedirect($element));
  }

  /**
   * @param ElementInterface $element
   * @return AppendableRedirect|null
   */
  protected function getAppendableRedirect(ElementInterface $element): ?ElementRoutesRedirect {
    return ArrayHelper::firstWhere(
      $this->getRedirects($element->getSite()),
      fn(ElementRoutesRedirect $redirect) => $redirect instanceof AppendableRedirect
    );
  }

  /**
   * @param ElementInterface $element
   * @return ElementRoute[]
   */
  protected function getElementRoutes(ElementInterface $element): array {
    $result = [];
    foreach ($this->getRedirects($element->getSite()) as $redirect) {
      $result = array_merge($result, $redirect->getElementRoutes($element));
    }

    return $result;
  }

  /**
   * @param Site|null $site
   * @return array|ElementRoutesRedirect[]
   */
  protected function getRedirects(?Site $site = null): array {
    if (!isset($this->_redirects)) {
      try {
        $this->_redirects = array_filter(
          RedirectNotFound::getInstance()->getRedirects($site),
          fn($redirect) => $redirect instanceof ElementRoutesRedirect
        );
      } catch (Throwable) {
        $this->_redirects = [];
      }
    }

    return $this->_redirects;
  }

  /**
   * @inheritdoc
   */
  protected function selectorIcon(): ?string {
    return '@appicons/routes.svg';
  }

  /**
   * @inheritDoc
   */
  protected function selectorLabel(): string {
    return Craft::t('lenz-craft-essentials', 'Redirect Table');
  }
}
