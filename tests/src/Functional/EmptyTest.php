<?php

use Drupal\Tests\BrowserTestBase;
use Drupal\ish_drupal_module\Controller\AphorismsController;

class EmptyTest extends BrowserTestBase {

  protected static $modules = ['node', 'user', 'ish_drupal_module' ];
  protected $defaultTheme = 'stark';

  protected function setUp(): void {
    parent::setUp();
    // $this->drupalPlaceBlock('current_user_block');
  }

  public function testCurrentUserBlock() {
    $user = $this->drupalCreateUser([
      'access content',
    ]);

    $this->drupalLogin($user);

    $this->assertEquals($user->id(), $this->loggedInUser->id());

    $this->assertEquals(1, 1);
  }

}
