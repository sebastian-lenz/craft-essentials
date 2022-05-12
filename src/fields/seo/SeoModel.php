<?php

namespace lenz\craft\essentials\fields\seo;

use Craft;
use craft\base\ElementInterface;
use craft\helpers\Html;
use craft\helpers\Template;
use lenz\craft\essentials\Plugin;
use lenz\craft\utils\foreignField\ForeignFieldModel;
use Twig\Markup;
use yii\base\InvalidConfigException;

/**
 * Class SeoModel
 */
class SeoModel extends ForeignFieldModel
{
  /**
   * @var string
   */
  public string $enabled;

  /**
   * @var string|null
   */
  public ?string $description;

  /**
   * @var string|null
   */
  public ?string $keywords;


  /**
   * @inheritDoc
   */
  public function getAttributeLabel($attribute): string {
    return match ($attribute) {
      'enabled' => Craft::t('lenz-craft-essentials', 'List this page in the search engine sitemap'),
      'description' => Craft::t('lenz-craft-essentials', 'Description'),
      'keywords' => Craft::t('lenz-craft-essentials', 'Keywords'),
      default => parent::getAttributeLabel($attribute),
    };
  }

  /**
   * @return array
   * @throws InvalidConfigException
   */
  public function getCanonicalTags(): array {
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
          'rel' => 'canonical',
          'href' => $translation['url'],
        ]);
      } else {
        $site = $translation['site'];
        $tags['link:alternate:' . $site->language] = Html::tag('link', '', [
          'rel' => 'alternate',
          'href' => $translation['url'],
          'hreflang' => $site->language,
        ]);
      }
    }

    return $tags;
  }

  /**
   * @return array
   */
  public function getMetaData(): array {
    return [
      'description' => $this->getMetaDescription(),
      'keywords' => $this->getMetaKeywords(),
    ];
  }

  /**
   * @return string|null
   */
  public function getMetaDescription(): ?string {
    return $this->description;
  }

  /**
   * @return string|null
   */
  public function getMetaKeywords(): ?string {
    return $this->keywords;
  }

  /**
   * @return array
   */
  public function getMetaTags(): array {
    $tags = [];
    foreach ($this->getMetaData() as $name => $content) {
      if (!empty($content)) {
        $tags['meta:' . $name] = Html::tag('meta', '', [
          'name' => $name,
          'content' => $content,
        ]);
      }
    }

    return $tags;
  }

  /**
   * @return Markup
   * @throws InvalidConfigException
   */
  public function getHeaderTags(): Markup {
    return Template::raw(implode("\n  ",
      $this->getCanonicalTags() +
      $this->getMetaTags()
    ));
  }

  /**
   * @return string
   */
  public function getSearchKeywords(): string {
    return implode(' ', [
      $this->description,
      $this->keywords
    ]);
  }

  /**
   * @inheritDoc
   */
  public function rules(): array {
    return array_merge(parent::rules(), [
      [['enabled'], 'boolean'],
      [['description', 'keywords'], 'string'],
    ]);
  }
}
