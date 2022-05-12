<?php

namespace lenz\craft\essentials\services\redirectLanguage;

/**
 * Class LanguageGroup
 */
class LanguageGroup
{
  /**
   * @var string[]
   */
  private array $_languages = [];

  /**
   * @var float
   */
  private float $_quality;


  /**
   * LanguageGroup constructor.
   * @param float $quality
   */
  public function __construct(float $quality) {
    $this->_quality = $quality;
  }

  /**
   * @param string $language
   */
  public function addLanguage(string $language): void {
    $this->_languages[] = $language;
  }

  /**
   * @param LanguageStack $result
   * @param LanguageGroup $otherGroup
   */
  public function combine(LanguageStack $result, LanguageGroup $otherGroup): void {
    foreach ($otherGroup->_languages as $otherLanguage) {
      $isWildcard = $otherLanguage === '*';
      $quality = $otherGroup->getQuality() * $this->getQuality();

      foreach ($this->_languages as $language) {
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
  public function getBestLanguage(): string {
    return reset($this->_languages);
  }

  /**
   * @return float
   */
  public function getQuality(): float {
    return $this->_quality;
  }


  // Private methods
  // ---------------

  /**
   * Compare two language tags and distinguish the degree of matching.
   *
   * @param string $lft
   * @param string $rgt
   * @return float
   */
  private static function languageScore(string $lft, string $rgt): float {
    $lft = explode('-', $lft);
    $rgt = explode('-', $rgt);

    for ($index = 0, $n = min(count($lft), count($rgt)); $index < $n; $index++) {
      if ($lft[$index] !== $rgt[$index]) break;
    }

    return $index === 0 ? 0 : (float)$index / count($lft);
  }
}
