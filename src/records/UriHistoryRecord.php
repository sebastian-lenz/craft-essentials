<?php

namespace lenz\craft\essentials\records;

use craft\base\ElementInterface;
use craft\db\ActiveRecord;

/**
 * Class UriHistoryRecord
 *
 * @property string $id
 * @property string $elementId
 * @property string $siteId
 * @property string $uri
 */
class UriHistoryRecord extends ActiveRecord
{
  /**
   * @inheritDoc
   */
  static public function tableName(): string {
    return '{{%lenz_uri_history}}';
  }

  /**
   * @return ElementInterface|null
   */
  public function getElement(): ?ElementInterface {
    return \Craft::$app->elements->getElementById($this->elementId, null, $this->siteId);
  }
}
