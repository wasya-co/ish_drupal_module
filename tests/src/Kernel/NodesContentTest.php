<?php

namespace Drupal\Tests\ish_drupal_module\Kernel;

use Drupal\ish_drupal_module\Content\NodesContent;
use Drupal\KernelTests\KernelTestBase;

/**
 * @group ish_drupal_module

  cd web ; ../vendor/bin/phpunit -c core/phpunit.xml modules/ish_drupal_module/tests/src/Kernel/NodesContentTest.php

 *
**/
class NodesContentTest extends KernelTestBase {

  protected static $modules = [
    'system',
  ];

  public function testPrepareFieldValuesAddsBasicHtmlFormatToBody(): void {
    $values = NodesContent::prepare_field_values([
      'title' => 'Home',
      'body' => [
        'value' => '<p>Welcome</p>',
      ],
    ]);

    $this->assertSame('Home', $values['title']);
    $this->assertSame('<p>Welcome</p>', $values['body']['value']);
    $this->assertSame('basic_html', $values['body']['format']);
  }

}
