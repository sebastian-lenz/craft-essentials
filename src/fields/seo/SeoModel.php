<?php

namespace lenz\craft\essentials\fields\seo;

use lenz\craft\utils\foreignField\ForeignFieldModel;

/**
 * Class SeoModel
 */
class SeoModel extends ForeignFieldModel
{
  /**
   * @var string
   */
  public $description;

  /**
   * @var string
   */
  public $keywords;
}
