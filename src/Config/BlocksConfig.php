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

    $blocks = \Drupal::entityTypeManager()->getStorage('block_content')->loadByProperties([
      'type' => 'basic',
      'info' => $slug,
    ]);
    if ($blocks) {
      $block = reset($blocks);
    }
    else {
      $block = BlockContent::create([
        'type' => 'basic',
        'info' => $slug,
        'body' => [
          'value' => <<<HTML
            <ul>
              <li>Monday: Closed</li>
              <li>Tuesday: 9:00 AM -- 6:00 PM</li>
              <li>Wednesday: 9:00 AM -- 6:00 PM</li>
              <li>Thursday: 9:00 AM -- 6:00 PM</li>
              <li>Friday: 9:00 AM -- 6:00 PM</li>
              <li>Saturday: 9:00 AM -- 2:00 PM</li>
              <li>Sunday: Closed</li>
            </ul>
          HTML,
          'format' => 'full_html',
        ],
      ]);
      $block->save();
    }


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

  /*
  **/
  public static function create_block() {
    $block_type = 'section_block';
    $info = 'rapid_consulting';


    $storage = \Drupal::entityTypeManager()->getStorage('block_content');

    $blocks = $storage->loadByProperties([
      'type' => $block_type,
      'info' => $info,
    ]);

    if ($blocks) {
      $block = reset($blocks);
    }
    else {
      $block = BlockContent::create([
        'type' => $block_type,
      'info' => $info,
      ]);
      $block->save();
    }
  }

  /*
  **/
  public static function setup_block($block_type, $fields) {
    if (!BlockContentType::load($block_type)) {
      BlockContentType::create([
        'id' => $block_type,
        'label' => $block_type,
        'revision' => TRUE,
      ])->save();
    }

    $form_display = EntityFormDisplay::load("block_content.$block_type.default");
    if (!$form_display) {
      $form_display = EntityFormDisplay::create([
        'targetEntityType' => 'block_content',
        'bundle' => $block_type,
        'mode' => 'default',
        'status' => TRUE,
      ]);
    }

    $display = EntityViewDisplay::load("block_content.$block_type.full");
    if (!$display) {
      $display = EntityViewDisplay::create([
        'targetEntityType' => 'block_content',
        'bundle' => $block_type,
        'mode'   => 'full',
        'status' => TRUE,
      ]);
    }

    foreach ($fields as $field_name => $field_c) {
      if (!FieldStorageConfig::loadByName('block_content', $field_name)) {
        FieldStorageConfig::create([
          'field_name'  => $field_name,
          'entity_type' => 'block_content',
          'type'        => $field_c['type'],
          'cardinality' => $field_c['cardinality'] ?? 1,
        ])->save();
      }
      if (!FieldConfig::loadByName('block_content', $block_type, $field_name)) {
        FieldConfig::create([
          'bundle'        => $block_type,
          'default_value' => $field_c['default_value'] ?? [],
          'entity_type'   => 'block_content',
          'field_name'    => $field_name,
          'label'         => $field_name,
          'required'      => FALSE,
          'settings'      => $field_c['field_config_settings'] ?? [],
          'translatable'  => $field_c['translatable'] ?? false,
        ])->save();
      }
      $form_display->setComponent($field_name, [
        'type' => $field_c['form_display'],
      ])->save();
      $display->setComponent($field_name, [
        'label' => 'hidden',
        'type'  => $field_c['display'],
      ])->save();
    } // end fields foreach

  }

}
