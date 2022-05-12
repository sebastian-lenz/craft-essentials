<?php

namespace lenz\craft\essentials\services\redirectLanguage;

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
  private array $_groups = [];

  /**
   * The regular expression used to parse the language list.
   */
  const LANGUAGE_REGEXP = '/(\*|[a-zA-Z\d]{1,8}(?:-[a-zA-Z\d]{1,8})*)(?:\s*;\s*q\s*=\s*(0\.\d{0,3}|1\.0{0,3}))?/';


  /**
   * @param float $quality
   * @return LanguageGroup
   */
  public function addGroup(float $quality): LanguageGroup {
    $group = $this->getGroup($quality);
    if (!is_null($group)) {
      return $group;
    }

    $group = $this->_groups[] = new LanguageGroup($quality);
    $this->sortGroups();
    return $group;
  }

  /**
   * @param string $language
   * @param float $quality
   */
  public function addLanguage(string $language, float $quality = 1.0): void {
    $this->addGroup($quality)->addLanguage($language);
  }

  /**
   * @inheritdoc
   */
  public function count(): int {
    return count($this->_groups);
  }

  /**
   * @return LanguageGroup
   */
  public function getBestGroup(): LanguageGroup {
    return reset($this->_groups);
  }

  /**
   * @param LanguageStack $acceptedStack
   * @return string
   * @throws Exception
   */
  public function getBestLanguage(LanguageStack $acceptedStack): string {
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
   * @param float $quality
   * @return LanguageGroup|null
   */
  public function getGroup(float $quality): ?LanguageGroup {
    foreach ($this->_groups as $group) {
      if ($group->getQuality() == $quality) {
        return $group;
      }
    }

    return null;
  }


  // Private methods
  // ---------------

  /**
   * @param LanguageStack $otherStack
   * @return LanguageStack
   */
  private function combine(LanguageStack $otherStack): LanguageStack {
    $result = new LanguageStack();

    foreach ($otherStack->_groups as $otherGroup) {
      if ($otherGroup->getQuality() <= 0.0) {
        continue;
      }

      foreach ($this->_groups as $group) {
        if ($group->getQuality() <= 0.0) {
          continue;
        }

        $group->combine($result, $otherGroup);
      }
    }

    return $result;
  }

  /**
   * @return void
   */
  private function sortGroups(): void {
    usort($this->_groups, function(LanguageGroup $lft, LanguageGroup $rgt) {
      return $lft->getQuality() - $rgt->getQuality();
    });
  }


  // Static methods
  // --------------

  /**
   * Parse the given language list string.
   * @param string $value
   * @return LanguageStack
   */
  public static function fromString(string $value): LanguageStack {
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
