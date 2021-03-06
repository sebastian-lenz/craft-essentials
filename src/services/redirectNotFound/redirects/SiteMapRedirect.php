<?php

namespace lenz\craft\essentials\services\redirectNotFound\redirects;

use Craft;
use craft\web\Request;
use craft\web\Response;
use lenz\craft\essentials\Plugin;

/**
 * Class SiteMapRedirect
 */
class SiteMapRedirect extends AbstractRedirect
{
  /**
   * @param Request $request
   * @return bool
   */
  public function redirect(Request $request): bool {
    if ($request->getFullPath() != 'sitemap.xml') {
      return false;
    }

    header('Content-type: text/xml');
    die(Plugin::getInstance()->siteMap->create()->getXml());
  }
}
