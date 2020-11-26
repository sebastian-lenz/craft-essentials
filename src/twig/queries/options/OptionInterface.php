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
  function getOptionValue();

  /**
   * @return string
   */
  function getOptionTitle(): string;
}
