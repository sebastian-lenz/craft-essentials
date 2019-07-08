<?php

namespace lenz\craft\essentials\utils;

use Countable;
use Exception;

/**
 * Class LanguagesStack
 */
class LanguageStack implements Countable
{
  /**
   * @var LanguageGroup[]
   */
  private $groups = array();

  /**
   * The regular expression used to parse the language list.
   */
  const LANGUAGE_REGEXP = '/(\*|[a-zA-Z0-9]{1,8}(?:-[a-zA-Z0-9]{1,8})*)(?:\s*;\s*q\s*=\s*(0(?:\.\d{0,3})|1(?:\.0{0,3})))?/';


  /**
   * @param string $language
   * @param float $quality
   */
  public function addLanguage($language, $quality = 1.0) {
    $this->addGroup($quality)->addLanguage($language);
  }

  /**
   * @param float $quality
   * @return LanguageGroup
   */
  public function addGroup($quality) {
    $group = $this->getGroup($quality);
    if (!is_null($group)) {
      return $group;
    }

    $group = $this->groups[] = new LanguageGroup($quality);
    $this->sortGroups();
    return $group;
  }

  /**
   * @param LanguageStack $otherStack
   * @return LanguageStack
   */
  private function combine($otherStack) {
    $result = new LanguageStack();

    foreach ($otherStack->groups as $otherGroup) {
      if ($otherGroup->getQuality() <= 0.0) {
        continue;
      }

      foreach ($this->groups as $group) {
        if ($group->getQuality() <= 0.0) {
          continue;
        }

        $group->combine($result, $otherGroup);
      }
    }

    return $result;
  }

  /**
   * @inheritdoc
   */
  public function count() {
    return count($this->groups);
  }

  /**
   * @param LanguageStack $acceptedStack
   * @return string
   * @throws Exception
   */
  public function getBestLanguage($acceptedStack) {
    $combined = $this->combine($acceptedStack);
    if (count($combined) === 0) {
      $combined = $this;
    }

    if (count($combined) === 0) {
      throw new Exception('No languages available.');
    }

    return $combined->getBestGroup()->getBestLanguage();
  }

  /**
   * @return LanguageGroup
   */
  public function getBestGroup() {
    return reset($this->groups);
  }

  /**
   * @param float $quality
   * @return LanguageGroup
   */
  public function getGroup($quality) {
    foreach ($this->groups as $group) {
      if ($group->getQuality() == $quality) {
        return $group;
      }
    }

    return null;
  }

  /**
   * @return $this
   */
  private function sortGroups() {
    usort($this->groups, function(LanguageGroup $left, LanguageGroup $right) {
      return $left->getQuality() - $right->getQuality();
    });

    return $this;
  }

  /**
   * Parse the given language list string.
   * @param string $value
   * @return LanguageStack
   */
  public static function fromString($value) {
    $stack = new LanguageStack();
    $ranges = explode(',', trim($value));

    foreach ($ranges as $range) {
      if (preg_match(self::LANGUAGE_REGEXP, trim($range), $match) !== 1) {
        continue;
      }

      $stack->addGroup(
        !isset($match[2]) ? 1.0 : floatval($match[2])
      )->addLanguage(strtolower($match[1]));
    }

    return $stack;
  }
}
