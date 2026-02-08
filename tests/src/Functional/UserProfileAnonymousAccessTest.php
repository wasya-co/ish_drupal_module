<?php

/**
 * SYMFONY_DEPRECATIONS_HELPER=weak ./vendor/bin/phpunit web/modules/ish_drupal_module/tests/src/Functional/UserProfileAnonymousAccessTest.php
**/

namespace Drupal\Tests\ish_drupal_module\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\user\Entity\User;

/**
 * Tests anonymous access to user profile pages.
 *
 * @group ish_drupal_module
 */
class UserProfileAnonymousAccessTest extends BrowserTestBase {
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'user',
  ];

  /**
   * Test that anonymous users can view user profiles.
   */
  public function testAnonymousCanViewUserProfile(): void {
    // Create a user account.
    $account = User::create([
      'name' => 'public_user',
      'mail' => 'public@example.com',
      'status' => 1,
    ]);
    $account->save();

    // Grant anonymous users permission to view user information.
    $anonymous_role = $this->container
      ->get('entity_type.manager')
      ->getStorage('user_role')
      ->load('anonymous');

    $anonymous_role->grantPermission('access user profiles');
    $anonymous_role->save();

    // Clear caches so permission changes apply.
    $this->container->get('cache_tags.invalidator')->invalidateTags(['config:user.role.anonymous']);

    $this->drupalGet('/user/' . $account->id());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextNotContains('Access denied');
    $this->assertSession()->pageTextContains('public_user');
  }

}
