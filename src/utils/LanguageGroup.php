<?php

namespace lenz\craft\essentials\utils;

/**
 * Class LanguageGroup
 */
class LanguageGroup
{
  /**
   * @var string[]
   */
  private $languages = array();

  /**
   * @var float
   */
  private $quality;


  /**
   * LanguageGroup constructor.
   * @param float $quality
   */
  public function __construct($quality) {
    $this->quality = $quality;
  }

  /**
   * @param $language
   */
  public function addLanguage($language) {
    $this->languages[] = $language;
  }

  /**
   * @param LanguageStack $result
   * @param LanguageGroup $otherGroup
   */
  public function combine(LanguageStack $result, LanguageGroup $otherGroup) {
    foreach ($otherGroup->languages as $otherLanguage) {
      $isWildcard = $otherLanguage === '*';
      $quality = $otherGroup->getQuality() * $this->getQuality();

      foreach ($this->languages as $language) {
        $score = $isWildcard
          ? 1
          : self::languageScore($otherLanguage, $language);

        if ($score > 0) {
          $result->addGroup($quality * $score)->addLanguage($language);
        }
      }
    }
  }

  /**
   * @return string
   */
  public function getBestLanguage() {
    return reset($this->languages);
  }

  /**
   * @return float
   */
  public function getQuality() {
    return $this->quality;
  }

  /**
   * Compare two language tags and distinguish the degree of matching.
   *
   * @param string $a
   * @param string $b
   * @return float
   */
  private static function languageScore($a, $b) {
    $a = explode('-', $a);
    $b = explode('-', $b);

    for ($index = 0, $n = min(count($a), count($b)); $index < $n; $index++) {
      if ($a[$index] !== $b[$index]) break;
    }

    return $index === 0 ? 0 : (float)$index / count($a);
  }
}
