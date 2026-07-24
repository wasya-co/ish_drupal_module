<?php

/* Lets move to full functional tests */

namespace Drupal\Tests\ish_drupal_module\Kernel;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Session\AccountInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\ish_drupal_module\Plugin\Block\CurrentUserBlock;

/**
 * @coversDefaultClass \Drupal\ish_drupal_module\Plugin\Block\CurrentUserBlock
 *
 * @group ish_drupal_module
**/
class CurrentUserBlockTest extends KernelTestBase {
  /**
   * Modules to enable for this test.
   *
   * @var array
  **/
  protected static $modules = [
    'system',
    'user',
    'ish_drupal_module',
  ];


  protected function setUp(): void {
    parent::setUp();
    // $container = new ContainerBuilder();
    // $user = $this->createMock(AccountInterface::class);
    // $user->method('id')->willReturn(123);
    // $user->method('getEmail')->willReturn('test_email'); // expected
    // $container->set('current_user', $user);
    // \Drupal::setContainer($container);
  }

  public function testBuild() {
    $actual = Drupal\ish_drupal_module\Plugin\Block\CurrentUserBlock->build();
    // $actual = CurrentUserBlock->build();

    $this->assertSame('test_email', $actual['#email']);
  }

  // public function testBuild2() {
  //   $plugin_manager = $this->container->get('plugin.manager.block');
  //   $block = $plugin_manager->createInstance('current_user_block', []);
  //   $build = $block->build();
  //   $output = (string) $this->container->get('renderer')->renderPlain($build);
  //   $this->assertStringContainsString('Hello,', $output);
  // }

}


