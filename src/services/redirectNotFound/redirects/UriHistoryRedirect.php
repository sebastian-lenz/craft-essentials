<?php

namespace lenz\craft\essentials\services\redirectNotFound\redirects;

use Craft;
use craft\base\ElementInterface;
use craft\web\Request;
use lenz\craft\essentials\records\UriHistoryRecord;
use lenz\craft\essentials\services\redirectNotFound\utils\ElementRoute;
use yii\base\InvalidConfigException;

/**
 * Class SlugHistoryRedirect
 */
class UriHistoryRedirect extends AbstractRedirect implements ElementRoutesRedirect
{
  /**
   * @inheritDoc
   */
  public function delete(ElementRoute $route): void {
    UriHistoryRecord::deleteAll(['uid' => $route->uid]);
  }

  /**
   * @inheritDoc
   */
  public function getElementRoutes(ElementInterface $element): array {
    return array_map(
      fn(UriHistoryRecord $record) => new ElementRoute([
        'origin' => Craft::t('lenz-craft-essentials', 'History'),
        'originId' => $record->id,
        'redirect' => $this,
        'uid' => $record->uid,
        'url' => $record->uri,
      ]),
      UriHistoryRecord::find()->where([
        'elementId' =>  $element->id,
        'siteId' => $element->siteId,
      ])->all()
    );
  }

  /**
   * @inheritDoc
   * @throws InvalidConfigException
   */
  public function redirect(Request $request): bool {
    $site = Craft::$app->sites->currentSite;
    $history = UriHistoryRecord::findOne([
      'siteId' => $site->id,
      'uri' => $request->getPathInfo(),
    ]);

    $element = $history?->getElement();
    $url = $element?->getUrl();
    if (is_null($url)) {
      return false;
    }

    $this->sendRedirect($url);
    return true;
  }
}
