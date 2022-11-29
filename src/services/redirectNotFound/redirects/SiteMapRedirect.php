<?php

namespace lenz\craft\essentials\services\redirectNotFound\redirects;

use craft\web\Request;
use lenz\craft\essentials\Plugin;
use yii\base\InvalidConfigException;

/**
 * Class SiteMapRedirect
 */
class SiteMapRedirect extends AbstractRedirect
{
  /**
   * @param Request $request
   * @return bool
   * @throws InvalidConfigException
   */
  public function redirect(Request $request): bool {
    if ($request->getFullPath() != 'sitemap.xml') {
      return false;
    }

    header('Content-type: text/xml');
    die(Plugin::getInstance()->siteMap->create()->getXml());
  }
}
