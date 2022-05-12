<?php

namespace lenz\craft\essentials\twig\queries\options;

/**
 * Class OptionInterface
 */
interface OptionInterface
{
  /**
   * @return string|int
   */
  function getOptionValue(): int|string;

  /**
   * @return string
   */
  function getOptionTitle(): string;
}
