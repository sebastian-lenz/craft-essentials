<?php

namespace lenz\craft\essentials\services\redirectNotFound\utils;

use lenz\craft\essentials\services\redirectNotFound\redirects\ElementRoutesRedirect;
use yii\base\Model;

/**
 * Class ElementRoute
 */
class ElementRoute extends Model
{
  /**
   * @var string
   */
  public string $origin;

  /**
   * @var mixed
   */
  public mixed $originId;

  /**
   * @var ElementRoutesRedirect
   */
  public ElementRoutesRedirect $redirect;

  /**
   * @var string
   */
  public string $uid;

  /**
   * @var string
   */
  public string $url;


  /**
   * @return void
   */
  public function delete(): void {
    $this->redirect->delete($this);
  }
}
