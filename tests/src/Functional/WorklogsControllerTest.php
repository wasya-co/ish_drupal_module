<?php

/**
 * php ./vendor/bin/phpunit -c core/phpunit.xml.dist modules/custom/worklog_redirect/tests/src/Functional/WorklogRedirectTest.php
**/

namespace Drupal\Tests\ish_drupal_module\Functional;

use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\BrowserTestBase;

class WorklogsControllerTest extends BrowserTestBase {
  protected $defaultTheme = 'stark';
  protected static $modules = ['node', 'ish_drupal_module', 'user'];
  protected $user;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    if (!NodeType::load('worklog')) {
      NodeType::create([
          'type' => 'worklog',
          'name' => 'Worklog',
      ])->save();
    }
    if (!FieldStorageConfig::loadByName('node', 'field_datestr')) {
      FieldStorageConfig::create([
        'field_name' => 'field_datestr',
        'entity_type' => 'node',
        'type' => 'string',
      ])->save();
    }
    if (!FieldConfig::loadByName('node', 'worklog', 'field_datestr')) {
      FieldConfig::create([
        'field_name' => 'field_datestr',
        'entity_type' => 'node',
        'bundle' => 'worklog',
        'label' => 'Date',
      ])->save();
    }

    //                                    permissions, name, is_admin
    $this->user = $this->drupalCreateUser(['access content'], NULL, TRUE);
    $this->user->addRole('administrator');
  }

  /**
   * Tests the redirect
  **/
  public function testShowRedirect() {
    $node = Node::create([
      'type' => 'worklog',
      'title' => 'Test Node 2025a',
      'field_datestr' => '2025a',
      'status' => 1,
    ]);
    $node->save();
    $saved_node = Node::load($node->id());
    $this->assertNotNull($saved_node, 'Node was saved successfully.');

    $this->drupalLogin($this->user);
    $current_user = \Drupal::currentUser();
    $this->assertEquals($this->user->id(), $current_user->id(), 'User is logged in.');


    $this->drupalGet('/worklogs/2025a');
    // $content = $this->getSession()->getPage()->getContent();
    // echo('+++ $content');
    // var_dump( $content );

    /* works well: */
    // $response = $this->getSession()->getDriver()->getClient()->request('GET', '/worklogs/2025a', [], [], ['max_redirects' => 0]);
    // echo('+++ $response');
    // var_dump($response);

    $this->assertSession()->addressEquals($node->toUrl()->toString());
    $this->assertSession()->statusCodeEquals(200);

    $this->assertSession()->pageTextContains('Test Node 2025a');
  }
}
