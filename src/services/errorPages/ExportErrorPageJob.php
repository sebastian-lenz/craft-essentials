<?php

namespace lenz\craft\essentials\services\errorPages;

use craft\elements\Entry;
use craft\helpers\App;
use craft\queue\JobInterface;
use craft\web\Request;
use craft\web\View;
use lenz\craft\essentials\Plugin;
use yii\base\BaseObject;

/**
 * Class ExportErrorPageJob
 */
class ExportErrorPageJob extends BaseObject implements JobInterface
{
  /**
   * @var int|null
   */
  public ?int $elementId = null;

  /**
   * @var int|array
   */
  public int|array $siteId;


  /**
   * @return string|null
   */
  public function getDescription(): ?string {
    return 'Export error page';
  }

  /**
   * @param $queue
   * @return void
   */
  public function execute($queue): void {
    if (!isset($this->elementId)) {
      $this->exportEntries(Entry::findAll([
        'siteId' => $this->siteId,
        'slug' => Plugin::getInstance()->getSettings()->errorSlugs,
      ]));
    } else {
      $this->exportEntry(Entry::findOne([
        'id' => $this->elementId,
        'siteId' => $this->siteId,
      ]));
    }
  }


  // Private methods
  // ---------------

  /**
   * @param array $entries
   * @return void
   */
  private function exportEntries(array $entries): void {
    foreach ($entries as $entry) {
      $this->exportEntry($entry);
    }
  }

  /**
   * @param Entry $entry
   * @return void
   */
  private function exportEntry(Entry $entry): void {
    $app = \Craft::$app;
    $app->getUrlManager()->setMatchedElement($entry);
    $app->getSites()->setCurrentSite($entry->getSite());
    $app->getView()->setTemplateMode(View::TEMPLATE_MODE_SITE);
    $app->getView()->clear();
    $app->getConfig()->getGeneral()->generateTransformsBeforePageLoad = true;

    $request = $app->getRequest();
    if ($request instanceof Request) {
      $request->setIsCpRequest(false);
    }

    $path = Plugin::getInstance()->getSettings()->errorFileName;
    $path = App::parseEnv($app->getView()->renderObjectTemplate($path, $entry));
    $html = $this->getEntryHtml($entry);
    file_put_contents($path, $html);
  }

  /**
   * @param Entry $entry
   * @return string
   */
  private function getEntryHtml(Entry $entry): string {
    foreach ($entry->getFieldValues() as $fieldValue) {
      if (is_a($fieldValue, 'lenz\contentfield\models\Content') && $fieldValue->getField()->useAsPageTemplate) {
        return $fieldValue->render([], [
          'view' => \Craft::$app->getView(),
        ]);
      }
    }

    [$route, $options] = $entry->getRoute();
    if ($route !== 'templates/render') {
      throw new \Exception('Unsupported error page route');
    }

    return \Craft::$app->getView()->renderTemplate($options['template'], $options['variables']);
  }
}
