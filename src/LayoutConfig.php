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
    $blocks = \Drupal\block\Entity\Block::loadMultiple();
    foreach ($blocks as $block) {
      $block->delete();
    }
  }

  /*
   * setup_marketing_site
  **/
  public static function setup_marketing_site() {
    logg(null, 'Setting up marketing site...');

    $theme = \Drupal::config('system.theme')->get('default');

    if (!Block::load($theme . '_site_branding')) {
      Block::create([
        'id' => $theme . '_site_branding',
        'theme' => $theme,
        'plugin' => 'system_branding_block',
        'region' => 'header',
        'weight' => 0,
        'visibility' => [],
        'settings' => [
          'id' => 'system_branding_block',
          'label' => 'Site branding',
          'label_display' => FALSE,
          'provider' => 'system',
          'use_site_logo' => TRUE,
          'use_site_name' => TRUE,
          // 'use_site_slogan' => TRUE,
        ],
      ])->save();
    }

    if (!Block::load($theme . '_main_menu')) {
      Block::create([
        'id' => $theme . '_main_menu',
        'theme' => $theme,
        'plugin' => 'superfish:main',
        'region' => 'primary_menu',
        'weight' => 0,
        'visibility' => [],
        'settings' => [
          'id' => 'superfish:main',
          'label' => 'Main Menu',
          'label_display' => FALSE,
          'provider' => 'superfish',
        ],
      ])->save();
    }

    /*
    * region: content
    **/
    $region = 'content';
    $blocks = [
      'page_title' => [
        'plugin' => 'page_title_block',
        'provider' => 'core',
        'label' => 'Page title',
        'settings' => [],
      ],
      'local_tasks' => [
        'plugin' => 'local_tasks_block',
        'provider' => 'core',
        'label' => 'Primary tabs',
      ],
      'content' => [
        'plugin' => 'system_main_block',
        'provider' => 'system',
        'label' => 'Main page content',
      ],
    ];
    $weight = 0;
    foreach ($blocks as $id => $block) {
      $block_id = "{$theme}_{$id}";

      if (Block::load($block_id)) {
        continue;
      }

      $settings = [
        'id' => $block['plugin'],
        'label' => $block['label'],
        'label_display' => FALSE,
        'provider' => $block['provider'],
      ];

      if (!empty($block['settings'])) {
        $settings += $block['settings'];
      }

      if (!Block::load($block_id)) {
        Block::create([
          'id' => $block_id,
          'theme' => $theme,
          'plugin' => $block['plugin'],
          'region' => $region,
          'weight' => $weight++,
          'visibility' => [],
          'settings' => $settings,
        ])->save();
      }
    }
  }

}

