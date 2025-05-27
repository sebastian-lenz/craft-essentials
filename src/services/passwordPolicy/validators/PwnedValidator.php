<?php

namespace lenz\craft\essentials\services\passwordPolicy\validators;

use lenz\craft\essentials\services\passwordPolicy\helpers\Pwned;
use yii\validators\Validator;

/**
 * Class PwnedValidator
 */
class PwnedValidator extends Validator
{
  /**
   * @inheritdoc
   */
  public function validateValue($value): ?array {
    if (Pwned::isPwned($value)) {
      return [\Craft::t(
        'lenz-craft-essentials',
        'This password has been compromised in a data breach. Please choose another password.'
      ), []];
    }

    return null;
  }
}
