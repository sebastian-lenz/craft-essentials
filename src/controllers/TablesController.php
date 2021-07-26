<?php

namespace lenz\craft\essentials\controllers;

use Craft;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use lenz\craft\essentials\Plugin;
use lenz\craft\essentials\services\tables\AbstractTable;
use Throwable;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class TablesController
 */
class TablesController extends Controller
{
  /**
   * @var AbstractTable|null
   */
  public $current;

  /**
   * @var AbstractTable[]
   */
  public $tables;


  /**
   * @inheritDoc
   */
  public function init() {
    parent::init();

    $tables = Plugin::getInstance()->tables->getAllTables();
    $id = Craft::$app->request->getQueryParam('id');
    $current = reset($tables);

    foreach ($tables as $table) {
      if ($table->getId() == $id) {
        $current = $table;
      }
    }

    $this->current = $current;
    $this->tables = $tables;
  }

  /**
   * @return Response
   */
  public function actionIndex(): Response {
    $current = $this->current;
    $selectedTab = 0;
    $tabs = [];
    foreach ($this->tables as $table) {
      if ($current === $table) {
        $selectedTab = count($tabs);
      }

      $tabs[] = [
        'url' => UrlHelper::cpUrl('tables', ['id' => $table->getId()]),
        'label' => $table->getLabel(),
      ];
    }

    return $this->renderTemplate(
      'lenz-craft-essentials/_tables/index', [
        'contentUrl' => $current
          ? UrlHelper::cpUrl('tables/view', ['id' => $current->getId()])
          : null,
        'selectedTab' => $selectedTab,
        'table' => $current,
        'tabs' => $tabs,
      ]
    );
  }

  /**
   * @throws NotFoundHttpException
   */
  public function actionView(): Response {
    Craft::$app->view->setRegisteredAssetBundles([]);

    $current = $this->current;
    if (is_null($current)) {
      throw new NotFoundHttpException();
    }

    $rows = $this->getPostData();
    if (!is_null($rows)) {
      $current->setRows($rows);
    }

    return $this->renderTemplate(
      'lenz-craft-essentials/_tables/view', [
        'table' => $current,
        'url' => UrlHelper::cpUrl('tables/view', ['id' => $current->getId()]),
      ]
    );
  }


  // Private methods
  // ---------------

  /**
   * @return array|null
   */
  private function getPostData(): ?array {
    $request = Craft::$app->getRequest();
    $postData = $request->getIsPost() ? $request->getParam('table-data') : null;
    if (is_null($postData)) {
      return null;
    }

    try {
      $data = Json::decode($postData);
      return is_array($data) ? $data : null;
    } catch (Throwable $error) { }

    return null;
  }
}
