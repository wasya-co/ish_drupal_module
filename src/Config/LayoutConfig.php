<?php
namespace Drupal\ish_drupal_module\Config;

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

class LayoutConfig {

  /*
   * clear
  **/
  public static function clear() {
    $theme = \Drupal::config('system.theme')->get('default');
    $blocks = \Drupal\block\Entity\Block::loadMultiple();
    foreach ($blocks as $block) {
      if ($block->getTheme() === $theme) {
        $block->delete();
      }
    }
  }

  /*
   * setup_marketing_site
  **/
  public static function setup_marketing_site() {
    $theme = \Drupal::config('system.theme')->get('default');

    $regions_blocks = [
      'header' => [
        'site_branding' => [
          'plugin' => 'system_branding_block',
          'provider' => 'system',
          'settings' => [
            'id' => 'system_branding_block',
            'provider' => 'system',
            'use_site_logo' => TRUE,
            'use_site_name' => TRUE,
          ],
        ],
      ],
      'primary_menu' => [
        'main_menu' => [
          'plugin' => 'superfish:main',
          'provider' => 'superfish',
          'settings' => [
            'id' => 'superfish:main',
          ],
        ],
      ],
      'content' => [
        'pagetitle' => [
          'plugin' => 'page_title_block',
          'provider' => 'system',
          'settings' => [],
        ],
        'tabs' => [
          'plugin' => 'local_tasks_block',
          'provider' => 'core',
        ],
        'content' => [
          'plugin' => 'system_main_block',
          'provider' => 'system',
        ],
      ],
      'footer_fourth' => [
        'tabs' => [
          'plugin' => 'local_tasks_block',
          'provider' => 'core',
        ],
      ],
    ];
    $weight = 0;
    foreach ($regions_blocks as $region => $blocks) {
      foreach ($blocks as $id => $block) {
        $block_id = "{$theme}_{$id}";

        if (Block::load($block_id)) {
          continue;
        }

        $settings = [
          'id' => $block['plugin'],
          'label' => $id,
          'label_display' => FALSE,
          'provider' => $block['provider'],
        ];

        if (!empty($block['settings'])) {
          $settings += $block['settings'];
        }


        Block::create([
          'id' => $block_id,
          'theme' => $theme,
          'plugin' => $block['plugin'],
          'region' => $region,
          'weight' => $block['weight'] ?? $weight++,
          'visibility' => [],
          'settings' => $settings,
        ])->save();

      }
    }
  }

  /*
  **/
  public static function update_pagetitle_for_issue() {
    $theme = \Drupal::config('system.theme')->get('default');
    $block = Block::load("{$theme}_pagetitle");
    $block->setVisibilityConfig('entity_bundle:node', [
      'id' => 'entity_bundle:node',
      'bundles' => [
        'issue' => 'issue',
      ],
      'negate' => TRUE,
      'context_mapping' => [
        'node' => '@node.node_route_context:node',
      ],
    ]);

    /* trash */
    // $config = $block->get('visibility');
    // $config['entity_bundle:node'] = [
    //   'id' => 'entity_bundle:node',
    //   'bundles' => [
    //     'issue' => 'issue',
    //   ],
    //   'negate' => TRUE,
    //   'context_mapping' => [
    //     'node' => '@node.node_route_context:node',
    //   ],
    // ];
    // $block->set('visibility', $config);

    $block->save();
  }

}

