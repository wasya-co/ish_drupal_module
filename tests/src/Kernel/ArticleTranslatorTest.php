<?php

namespace Drupal\Tests\ish_drupal_module\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

/**
 * @group ish_drupal_module

    composer require --dev drupal/core-dev
    composer require --dev phpunit/phpunit
    composer require --dev behat/mink
    composer require --dev symfony/phpunit-bridge

    php ./vendor/bin/phpunit -c web/core/phpunit.xml.dist web/modules/contrib/ish_drupal_module/tests/src/Kernel/ArticleTranslatorTest.php
    vendor/bin/phpunit   -c web/core/phpunit.xml.dist   web/modules/contrib/ish_drupal_module/tests/src/Kernel/SanityTest.php

 *
**/
class CollectLatestArticlesTest extends KernelTestBase {

  protected static $modules = [
    'system',
    'user',
    'field',
    'text',
    'filter',
    'node',
    'ish_drupal_module',
  ];

  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installConfig(['node']);

    NodeType::create([
      'type' => 'article',
      'name' => 'Article',
    ])->save();
  }

  public function testCollectsLatestTenArticles(): void {

    // Create 20 articles.
    for ($i = 1; $i <= 20; $i++) {
      Node::create([
        'type' => 'article',
        'title' => "Article {$i}",
        'status' => 1,
        'created' => time() + $i,
      ])->save();
    }

    $outs = Drupal\ish_drupal_module\Models\ArticleTranslator.collectMissingTranslation();

    $this->assertCount(10, $outs);

    // Newest article should be first.
    $this->assertEquals('Article 20', $outs[0]->label());

    // Oldest returned should be Article 11.
    $this->assertEquals('Article 11', $outs[9]->label());
  }

}
