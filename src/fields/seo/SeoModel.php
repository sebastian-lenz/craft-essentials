<?php

namespace lenz\craft\essentials\fields\seo;

use craft\base\ElementInterface;
use craft\helpers\Html;
use craft\helpers\Template;
use lenz\craft\essentials\Plugin;
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


  /**
   * @return array
   */
  public function getCanonicalTags() {
    $tags = [];
    $root = $this->getRoot();
    if (!($root instanceof ElementInterface)) {
      return $tags;
    }

    $service = Plugin::getInstance()->translations;
    $translations = $service->getTranslations($root);
    foreach ($translations as $translation) {
      if ($translation['isFallback']) {
        continue;
      }

      if ($translation['isCurrent']) {
        $tags['link:canonical'] = Html::tag('link', '', [
          'rel'  => 'canonical',
          'href' => $translation['url'],
        ]);
      } else {
        $site = $translation['site'];
        $tags['link:alternate:' . $site->language] = Html::tag('link', '', [
          'rel'      => 'alternate',
          'href'     => $translation['url'],
          'hreflang' => $site->language,
        ]);
      }
    }

    return $tags;
  }

  /**
   * @return array
   */
  public function getMetaData() {
    return [
      'description' => $this->getMetaDescription(),
      'keywords'    => $this->getMetaKeywords(),
    ];
  }

  /**
   * @return string
   */
  public function getMetaDescription() {
    return $this->description;
  }

  /**
   * @return string
   */
  public function getMetaKeywords() {
    return $this->keywords;
  }

  /**
   * @return array
   */
  public function getMetaTags() {
    $tags = [];
    foreach ($this->getMetaData() as $name => $content) {
      if (!empty($content)) {
        $tags['meta:' . $name] = Html::tag('meta', '', [
          'name'    => $name,
          'content' => $content,
        ]);
      }
    }

    return $tags;
  }

  /**
   * @return string
   */
  public function getHeaderTags() {
    return Template::raw(implode("\n  ",
      $this->getCanonicalTags() +
      $this->getMetaTags()
    ));
  }
}
