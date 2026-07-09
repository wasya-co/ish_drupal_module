<?php

namespace Drupal\Tests\ish_drupal_module\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * @group ish_drupal_module

  ./vendor/bin/phpunit \
    -c web/core/phpunit.xml.dist \
    web/modules/custom/ish_drupal_module/tests/src/Kernel/SanityTest.php

  ./vendor/bin/phpunit -c web/core/phpunit.xml.dist \
    web/modules/custom/ish_drupal_module/tests/src/Kernel/ArticleTranslatorTest.php

 *
**/
class SanityTest extends KernelTestBase {

  protected static $modules = [
    'system',
  ];

  public function testTrue(): void {
    $this->assertTrue(TRUE);
  }

}
