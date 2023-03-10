<?php

namespace lenz\craft\essentials\services\cp;

use craft\base\Utility;
use craft\helpers\App;
use lenz\craft\essentials\Plugin;
use lenz\craft\essentials\structs\icon\Icon;

/**
 * Class IconUtility
 */
class IconUtility extends Utility
{
  /**
   * @inheritdoc
   */
  static public function displayName(): string {
    return 'Icons';
  }

  /**
   * @inheritDoc
   */
  static public function id(): string {
    return 'staedel-icons';
  }

  /**
   * @inheritDoc
   */
  static public function contentHtml(): string {
    return \Craft::$app->getView()->renderTemplate(
      'lenz-craft-essentials/_utilities/icons',
      [
        'sources' => self::getSources()
      ]
    );
  }


  // Private methods
  // ---------------

  /**
   * @return array
   */
  static private function getSources(): array {
    $settings = Plugin::getInstance()->getSettings();
    $result = [];

    foreach ($settings->iconClasses as $iconClass) {
      $source = [
        'class' => $iconClass,
        'groups' => [],
        'message' => '',
      ];

      if (!class_exists($iconClass)) {
        $source['message'] = "$iconClass does not exist.";
      } elseif (!is_subclass_of($iconClass, Icon::class)) {
        $source['message'] = "$iconClass is not a subclass of lenz\\craft\\essentials\\structs\\icon\\Icon.";
      } elseif (is_null($iconClass::getSourceFile())) {
        $source['message'] = "$iconClass has no source file.";
      } else {
        $source['groups'] = self::getSourceGroups($iconClass);
      }

      $result[] = $source;
    }

    return $result;
  }

  /**
   * @param string $iconClass
   * @return array
   */
  static private function getSourceGroups(string $iconClass): array {
    $svg = file_get_contents(App::parseEnv($iconClass::getSourceFile()));
    $groups = [];
    preg_match_all('/id="([^"]+)"/', $svg, $matches);

    foreach ($matches[1] as $id) {
      $display = $iconClass::create($id);
      $size = $display->getSize();
      $icon = [
        'display' => $display,
        'id' => $id,
      ];

      if (!$size || $size[0] != $size[1]) {
        $groups['*'][] = $icon;
      } else {
        $groups[$size[0]][] = $icon;
      }
    }

    return $groups;
  }
}
