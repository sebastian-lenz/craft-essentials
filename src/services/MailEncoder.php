<?php

namespace lenz\craft\essentials\services;

use Craft;
use craft\events\TemplateEvent;
use craft\web\View;
use yii\base\Component;
use yii\base\Event;

/**
 * Class MailEncoder
 */
class MailEncoder extends Component
{
  /**
   * @var MailEncoder
   */
  static private MailEncoder $_instance;


  /**
   * Component constructor.
   */
  public function __construct() {
    parent::__construct();

    Event::on(
      View::class,
      View::EVENT_AFTER_RENDER_PAGE_TEMPLATE,
      [$this, 'onAfterRenderPageTemplate']
    );
  }

  /**
   * @param TemplateEvent $event
   */
  public function onAfterRenderPageTemplate(TemplateEvent $event) {
    if (!Craft::$app->getRequest()->getIsCpRequest()) {
      $event->output = self::encodeAll($event->output);
    }
  }


  // Static methods
  // --------------

  /**
   * @return MailEncoder
   */
  public static function getInstance(): MailEncoder {
    if (!isset(self::$_instance)) {
      self::$_instance = new MailEncoder();
    }

    return self::$_instance;
  }

  /**
   * @param string $value
   * @return string
   */
  public static function encode(string $value): string {
    $id = uniqid();
    $encoded = str_replace(
      array("\n", "\r"),
      array('\n', '\r'),
      addslashes(str_rot13($value))
    );

    return implode('', array(
      '<span id="', $id, '"></span>',
      '<script>',
        'document.getElementById("', $id, '").innerHTML = "', $encoded, '".replace(/[a-zA-Z]/g, function(c) {',
        'return String.fromCharCode((c<="Z"?90:122)>=(c=c.charCodeAt(0)+13)?c:c-26);',
        '});',
      '</script>'
    ));
  }

  /**
   * @param string $value
   * @return string
   */
  public static function encodeAll(string $value): string {
    return preg_replace_callback('/<a[^>]*href="mailto:([^"]*)"[^>]*>.*?<\/a>/s', function($matches) {
      $mail = trim($matches[1]);
      if (empty($mail)) {
        return '';
      } else {
        return self::encode($matches[0]);
      }
    }, $value);
  }
}
