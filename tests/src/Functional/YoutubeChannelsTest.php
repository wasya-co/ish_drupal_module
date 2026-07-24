<?php

/**
 * php ./vendor/bin/phpunit -c core/phpunit.xml modules/ish_drupal_module/tests/src/Functional/YoutubeChannelsTest.php
**/

namespace Drupal\Tests\ish_drupal_module\Functional;

use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\BrowserTestBase;

class YoutubeChannelsControllerTest extends BrowserTestBase {
  protected $defaultTheme = 'stark';
  protected static $modules = ['node', 'ish_drupal_module', 'user'];
  protected $user;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
  }

  /**
   * Tests the redirect
  **/
  public function testIndex() {
    $this->drupalGet('/youtube_channels');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Youtube Channels');
  }

  // public function testCheck() {
  // }

}
