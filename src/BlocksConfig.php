<?php
namespace Drupal\ish_drupal_module;

use Drupal\block\Entity\Block;
use Drupal\block_content\Entity\BlockContent;
use Drupal\block_content\Entity\BlockContentType;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

use Drupal\editor\Entity\Editor;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\node\NodeInterface;

use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\taxonomy\Entity\Term;

use Symfony\Component\HttpFoundation\RedirectResponse;

class BlocksConfig {

  /*
   * hours_of_operation
  **/
  public static function hours_of_operation() {
    $slug = 'hours_of_operation';

    $block = BlockContent::create([
      'type' => 'basic',
      'info' => $slug,
      'body' => [
        'value' => <<<HTML
          <ul><li>Monday: Closed</li><li>Tuesday: 9:00 AM -- 6:00 PM</li><li>Wednesday: 9:00 AM -- 6:00 PM</li><li>Thursday: 9:00 AM -- 6:00 PM</li><li>Friday: 9:00 AM -- 6:00 PM</li><li>Saturday: 9:00 AM -- 2:00 PM</li><li>Sunday: Closed</li></ul>
    HTML,
        'format' => 'full_html',
      ],
    ]);

    $block->save();

    $theme = \Drupal::config('system.theme')->get('default');
    if (!Block::load("{$theme}_{$slug}")) {
    Block::create([
        'id' => "{$theme}_{$slug}",
        'theme' => $theme,
        'plugin' => 'block_content:' . $block->uuid(),
        'region' => 'footer_first',
        'weight' => 0,
        'visibility' => [],
        'settings' => [
          'id' => 'block_content:' . $block->uuid(),
          'label' => 'Hours of Operation',
          'label_display' => true,
          'provider' => 'block_content',
          'view_mode' => 'full',
        ],
      ])->save();
    }
  }

}
