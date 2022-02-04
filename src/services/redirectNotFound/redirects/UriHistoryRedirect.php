<?php

namespace lenz\craft\essentials\services\redirectNotFound\redirects;

use Craft;
use craft\web\Request;
use lenz\craft\essentials\records\UriHistoryRecord;
use yii\base\InvalidConfigException;

/**
 * Class SlugHistoryRedirect
 */
class UriHistoryRedirect extends AbstractRedirect
{
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

    $element = $history ? $history->getElement() : null;
    $url = $element ? $element->getUrl() : null;
    if (is_null($url)) {
      return false;
    }

    $this->sendRedirect($url);
    return true;
  }
}
