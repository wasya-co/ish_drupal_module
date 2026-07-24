<?php

/*
 * Run:
 * phpunit -c core modules/custom/mymodule/tests/src/Functional/PublishOnDefaultValueTest.php
 *
 * Or:
 * phpunit -c core --group mymodule
 * ddev phpunit --group mymodule
**/

namespace Drupal\Tests\mymodule\Functional;

use Drupal\node\Entity\NodeType;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests default value on the "publish_on" field of node form.
 *
 * @group mymodule
 */
class PublishOnDefaultValueTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'scheduler',
    'mymodule', // Replace with your actual module name.
  ];

  protected function setUp(): void {
    parent::setUp();

    // Create a content type with Scheduler enabled.
    $type = NodeType::create([
      'type' => 'article',
      'name' => 'Article',
    ]);
    $type->save();

    // Enable Scheduler for this content type.
    \Drupal::service('config.factory')
      ->getEditable('scheduler.settings_article')
      ->set('publish_enable', TRUE)
      ->save();

    // Create a user with permission to create content.
    $this->drupalLogin($this->drupalCreateUser([
      'create article content',
      'edit any article content',
    ]));
  }

  /**
   * Test that the publish_on field has the default value.
   */
  public function testPublishOnDefaultValue() {
    // Visit the node creation form.
    $this->drupalGet('node/add/article');

    // Assert that the publish_on field exists.
    $this->assertSession()->fieldExists('Publish on');

    // Get the value from the field.
    $value = $this->getSession()->getPage()->findField('Publish on')->getValue();

    // Parse the value and check it's within an acceptable default range.
    $defaultTimestamp = strtotime('+2 days');
    $actualTimestamp = strtotime($value);

    // Allow a margin of error (e.g., 5 minutes).
    $this->assertTrue(
      abs($defaultTimestamp - $actualTimestamp) < 300,
      'The publish_on default value is approximately 2 days from now.'
    );
  }

}
