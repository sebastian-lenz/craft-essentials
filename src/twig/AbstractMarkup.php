<?php

namespace lenz\craft\essentials\twig;

use Twig\Markup;

/**
 * Class AbstractMarkup
 */
abstract class AbstractMarkup extends Markup
{
  /**
   * @var string
   */
  const CHARSET = 'UTF-8';


  /**
   * Icon constructor.
   * @param string $content
   */
  public function __construct(string $content = '') {
    parent::__construct($content, self::CHARSET);
  }

  /**
   * @return string
   */
  public function __toString(): string {
    return $this->getContent();
  }

  /**
   * @inheritDoc
   */
  public function count(): int {
    return mb_strlen($this->getContent(), self::CHARSET);
  }

  /**
   * @inheritDoc
   */
  public function jsonSerialize(): string {
    return $this->getContent();
  }


  // Protected methods
  // -----------------

  /**
   * @return string
   */
  abstract protected function getContent(): string;
}
