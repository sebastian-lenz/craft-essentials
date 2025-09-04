<?php

namespace lenz\craft\essentials\services;

use Craft;
use craft\events\TemplateEvent;
use craft\web\View;
use lenz\craft\essentials\services\eventBus\On;
use yii\base\Component;

/**
 * Class MailEncoder
 */
class MailEncoder extends Component
{
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

  /**
   * @param TemplateEvent $event
   */
  #[On(View::class, View::EVENT_AFTER_RENDER_PAGE_TEMPLATE)]
  static public function onAfterRenderPageTemplate(TemplateEvent $event): void {
    if (!Craft::$app->getRequest()->getIsCpRequest()) {
      $event->output = self::encodeAll($event->output);
    }
  }
}
