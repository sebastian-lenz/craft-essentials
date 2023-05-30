<?php

namespace lenz\craft\essentials\services\redirectNotFound;

use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\db\Query;
use craft\db\Table;
use craft\events\DefineFieldLayoutElementsEvent;
use craft\events\ElementEvent;
use craft\events\ExceptionEvent;
use craft\models\FieldLayout;
use craft\models\Site;
use craft\services\Elements;
use craft\services\Plugins;
use craft\web\ErrorHandler;
use lenz\craft\essentials\events\RegisterRedirectsEvent;
use lenz\craft\essentials\records\UriHistoryRecord;
use lenz\craft\essentials\services\AbstractService;
use lenz\craft\essentials\services\redirectNotFound\redirects\AbstractRedirect;
use lenz\craft\essentials\services\redirectNotFound\utils\ElementRoutesUiElement;
use yii\base\Event;
use yii\base\ModelEvent;
use yii\web\HttpException;

/**
 * Class RedirectNotFound
 */
class RedirectNotFound extends AbstractService
{

  /**
   * @var AbstractRedirect[]
   */
  private array $_redirects;

  /**
   * @var string
   */
  const EVENT_REGISTER_REDIRECTS = 'registerRedirects';


  /**
   * RedirectNotFound constructor.
   */
  public function __construct() {
    parent::__construct();

    Event::on(Plugins::class, Plugins::EVENT_AFTER_LOAD_PLUGINS, [$this, 'onAfterLoadPlugins']);
    Event::on(Element::class, Element::EVENT_BEFORE_SAVE, [$this, 'onBeforeElementSave']);
    Event::on(Elements::class, Elements::EVENT_BEFORE_UPDATE_SLUG_AND_URI, [$this, 'onBeforeUpdateSlug']);
    Event::on(FieldLayout::class, FieldLayout::EVENT_DEFINE_UI_ELEMENTS, [$this, 'onDefineUiElements']);
  }

  /**
   * @param Site|null $site
   * @return AbstractRedirect[]
   */
  public function getRedirects(?Site $site = null): array {
    if (!isset($this->_redirects)) {
      $event = RegisterRedirectsEvent::create($site);
      $this->trigger(self::EVENT_REGISTER_REDIRECTS, $event);

      if ($site) {
        return $event->redirects;
      } else {
        $this->_redirects = $event->redirects;
      }
    }

    return $this->_redirects;
  }

  /**
   * @return void
   */
  public function onAfterLoadPlugins(): void {
    $request = Craft::$app->getRequest();
    if (!$request->getIsSiteRequest() || $request->getIsConsoleRequest()) {
      return;
    }

    Event::on(
      ErrorHandler::class,
      ErrorHandler::EVENT_BEFORE_HANDLE_EXCEPTION,
      [$this, 'onBeforeHandleException']
    );
  }

  /**
   * @param ExceptionEvent $event
   */
  public function onBeforeHandleException(ExceptionEvent $event): void {
    $exception = $event->exception;
    while ($exception) {
      if (
        $exception instanceof HttpException &&
        $exception->statusCode === 404
      ) {
        $event->handled = $this->handleError();
        return;
      }

      $exception = $exception->getPrevious();
    }
  }

  /**
   * @param ModelEvent $event
   */
  public function onBeforeElementSave(ModelEvent $event): void {
    $element = $event->sender;
    if (!($element instanceof Element) || empty($event->sender->id)) {
      return;
    }

    $state = (object)['uri' => $this->getCurrentUri($element)];
    $state->handler = function(ModelEvent $event) use ($state) {
      $element = $event->sender;
      $element->off(Element::EVENT_AFTER_SAVE, $state->handler);
      if ($element instanceof Element && $element->uri !== $state->uri) {
        $this->storeUriHistory($element, $state->uri, $element->uri);
      }
    };

    $element->on(Element::EVENT_AFTER_SAVE, $state->handler);
  }

  /**
   * @param ElementEvent $event
   */
  public function onBeforeUpdateSlug(ElementEvent $event): void {
    $element = $event->element;
    if (empty($element->uri)) {
      return;
    }

    $oldUri = $this->getCurrentUri($element);
    if (!empty($oldUri) && $oldUri !== $element->uri) {
      $this->storeUriHistory($element, $oldUri, $element->uri);
    }
  }

  /**
   * @param DefineFieldLayoutElementsEvent $event
   * @return void
   */
  public function onDefineUiElements(DefineFieldLayoutElementsEvent $event): void {
    $event->elements[] = ElementRoutesUiElement::class;
  }


  // Private methods
  // ---------------

  /**
   * @param ElementInterface $element
   * @return string|null
   */
  private function getCurrentUri(ElementInterface $element): ?string {
    return (new Query())
      ->select('uri')
      ->from(Table::ELEMENTS_SITES)
      ->where([
        'elementId' => $element->id,
        'siteId' => $element->siteId,
      ])->scalar();
  }

  /**
   * @return bool
   */
  private function handleError(): bool {
    $request = Craft::$app->getRequest();

    foreach ($this->getRedirects() as $redirect) {
      if ($redirect->redirect($request)) {
        return true;
      }
    }

    return false;
  }

  /**
   * @param ElementInterface $element
   * @param string|null $oldUri
   * @param string|null $newUri
   */
  private function storeUriHistory(ElementInterface $element, ?string $oldUri, ?string $newUri): void {
    if (
      str_contains($oldUri, '__temp_') ||
      $element->isProvisionalDraft ||
      $element->getIsRevision() ||
      $element->getIsDraft()
    ) {
      return;
    }

    UriHistoryRecord::deleteAll([
      'siteId' => $element->siteId,
      'uri' => array_filter([$oldUri, $newUri]),
    ]);

    if (!empty($oldUri)) {
      (new UriHistoryRecord([
        'elementId' => $element->id,
        'siteId' => $element->siteId,
        'uri' => $oldUri,
      ]))->save();
    }
  }
}
