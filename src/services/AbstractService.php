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
  static private array $_INSTANCES = [];


  /**
   * @return self
   * @throws InvalidConfigException
   */
  public static function getInstance(): self {
    $name = get_called_class();
    if (!array_key_exists($name, self::$_INSTANCES)) {
      self::$_INSTANCES[$name] = Yii::createObject($name);
    }

    return self::$_INSTANCES[$name];
  }
}
