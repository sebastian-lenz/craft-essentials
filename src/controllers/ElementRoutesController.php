<?php

namespace lenz\craft\essentials\controllers;

use Craft;
use craft\base\ElementInterface;
use craft\web\Controller;
use lenz\craft\essentials\services\redirectNotFound\utils\ElementRoutesUiElement;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class ElementRoutesController
 * @noinspection PhpUnused
 */
class ElementRoutesController extends Controller
{
  /**
   * @var ElementInterface
   */
  public ElementInterface $element;

  /**
   * @var ElementRoutesUiElement
   */
  public ElementRoutesUiElement $routes;


  /**
   * @return Response
   * @noinspection PhpUnused
   */
  public function actionAppend(): Response {
    $this->routes->tryAppend(
      $this->element,
      $this->request->getBodyParam('origin')
    );

    return $this->createResponse();
  }

  /**
   * @return Response
   * @noinspection PhpUnused
   */
  public function actionDelete(): Response {
    $this->routes->tryDelete(
      $this->element,
      $this->request->getBodyParam('uid')
    );

    return $this->createResponse();
  }

  /**
   * @inheritDoc
   * @throws NotFoundHttpException
   */
  public function beforeAction($action): bool {
    $element = $this->findElement();
    if (!$element) {
      throw new NotFoundHttpException();
    }

    $this->element = $element;
    $this->routes = new ElementRoutesUiElement();
    return parent::beforeAction($action);
  }


  // Private methods
  // ---------------

  /**
   * @return Response
   */
  private function createResponse(): Response {
    return $this->asJson([
      'html' => $this->routes->formHtml($this->element),
      'success' => true,
    ]);
  }

  /**
   * @return ElementInterface|null
   */
  private function findElement(): ?ElementInterface {
    return Craft::$app->getElements()->getElementById(
      $this->request->getBodyParam('elementId'),
      null,
      $this->request->getBodyParam('siteId')
    );
  }
}
