<?php

namespace lenz\craft\essentials\services\redirectNotFound;

use Craft;
use craft\events\ExceptionEvent;
use craft\services\Plugins;
use craft\web\ErrorHandler;
use lenz\craft\essentials\services\redirectNotFound\redirects\AbstractRedirect;
use yii\base\Component;
use yii\base\Event;
use yii\web\HttpException;

/**
 * Class RedirectNotFound
 */
class RedirectNotFound extends Component
{
  /**
   * @var RedirectNotFound
   */
  static private $_instance;


  /**
   * RedirectNotFound constructor.
   */
  public function __construct() {
    parent::__construct();

    Event::on(
      Plugins::class,
      Plugins::EVENT_AFTER_LOAD_PLUGINS,
      [$this, 'onAfterLoadPlugins']
    );
  }

  /**
   * @param Event $event
   */
  public function onAfterLoadPlugins(Event $event) {
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
  public function onBeforeHandleException(ExceptionEvent $event) {
    $exception = $event->exception;
    while ($exception) {
      if (
        $exception instanceof HttpException &&
        $exception->statusCode === 404
      ) {
        $event->handled = $this->handleError($exception);
        return;
      }

      $exception = $exception->getPrevious();
    }
  }


  // Private methods
  // ---------------

  /**
   * @param HttpException $exception
   * @return bool
   */
  private function handleError(HttpException $exception) {
    $request = Craft::$app->getRequest();

    foreach (AbstractRedirect::getRedirects() as $redirect) {
      if ($redirect->redirect($request)) {
        return true;
      }
    }

    return false;
  }


  // Static methods
  // --------------

  /**
   * @return RedirectNotFound
   */
  public static function getInstance() {
    if (!isset(self::$_instance)) {
      self::$_instance = new RedirectNotFound();
    }

    return self::$_instance;
  }
}
