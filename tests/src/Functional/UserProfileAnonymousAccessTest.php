<?php

namespace Drupal\Tests\ish_drupal_module\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\user\Entity\User;

/**
 * Tests anonymous access to user profile pages.
 *
 * @group ish_drupal_module
 */
class UserProfileAnonymousAccessTest extends BrowserTestBase {

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

    $anonymous_role->grantPermission('view user information');
    $anonymous_role->save();

    // Clear caches so permission changes apply.
    $this->container->get('cache_tags.invalidator')->invalidateTags(['config:user.role.anonymous']);

    // Visit the user profile as anonymous.
    $this->drupalGet('/user/' . $account->id());

    // Assert HTTP 200.
    $this->assertSession()->statusCodeEquals(200);

    // Assert the page is not access denied.
    $this->assertSession()->pageTextNotContains('Access denied');

    // Assert username is visible.
    $this->assertSession()->pageTextContains('public_user');
  }

}
