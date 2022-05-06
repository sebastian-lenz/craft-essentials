<?php

namespace lenz\craft\essentials\twig;

use craft\helpers\App;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Fixture
 */
class Fixture
{
  /**
   * @var array
   */
  static array $FIXTURES = [];


  /**
   * @param string $name
   * @return mixed
   */
  static public function get(string $name) {
    if (!array_key_exists($name, self::$FIXTURES)) {
      self::$FIXTURES[$name] = self::load($name);
    }

    return self::$FIXTURES[$name];
  }

  /**
   * @param string $name
   * @return mixed
   */
  static public function load(string $name) {
    return Yaml::parseFile(App::parseEnv("@config/fixtures/$name.yml"), Yaml::PARSE_OBJECT);
  }
}
