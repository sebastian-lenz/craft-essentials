<?php

namespace lenz\craft\essentials\services;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * Class AbstractService
 */
abstract class AbstractService extends Component
{
  /**
   * @var AbstractService[]
   */
  static private $_INSTANCES = [];


  /**
   * @return self
   * @throws InvalidConfigException
   */
  public static function getInstance(): AbstractService {
    $name = get_called_class();
    if (!array_key_exists($name, self::$_INSTANCES)) {
      self::$_INSTANCES[$name] = Yii::createObject($name);
    }

    return self::$_INSTANCES[$name];
  }
}
