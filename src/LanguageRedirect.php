<?php

namespace sebastianlenz\common;

use sebastianlenz\common\utils\LanguageStack;

/**
 * Helper that parses the HTTP_ACCEPT_LANGUAGE header.
 * @see http://stackoverflow.com/questions/3770513/detect-browser-language-in-php
 */
class LanguageRedirect
{
  /**
   * The list of available languages.
   * @var LanguageStack
   */
  private $availableStack;

  /**
   * @var LanguageRedirect
   */
  static private $_instance;


  /**
   * Languages constructor.
   */
  public function __construct() {
    $this->availableStack = new LanguageStack();
  }

  /**
   * Add an available language.
   * @param string|string[] $language
   * @param float $quality
   * @return $this
   */
  public function addLanguage($language, $quality = 1.0) {
    $this->availableStack->addLanguage($language, $quality);
    return $this;
  }

  /**
   * Return the best matching language.
   * @return string
   * @throws \Exception
   */
  public function getBestLanguage() {
    if (count($this->availableStack) == 0) {
      throw new \Exception('No available languages.');
    }

    if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
      return $this->availableStack->getBestGroup()->getBestLanguage();
    }

    return $this->availableStack->getBestLanguage(
      LanguageStack::fromString($_SERVER['HTTP_ACCEPT_LANGUAGE'])
    );
  }

  /**
   * @throws \Exception
   */
  public function redirect() {
    $language = $this->getBestLanguage();
    header('Location: /' . $language . '/', true, 301);
    die();
  }

  /**
   * @return LanguageRedirect
   */
  public static function getInstance() {
    if (!isset(self::$_instance)) {
      self::$_instance = new LanguageRedirect();
    }

    return self::$_instance;
  }
}
