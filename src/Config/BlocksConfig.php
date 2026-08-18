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

/*
**/
class BlocksConfig {

  /*
  **/
  public static function copyright() {
    $slug = 'copyright';

    $blocks = \Drupal::entityTypeManager()->getStorage('block_content')->loadByProperties([
      'type' => 'basic',
      'info' => $slug,
    ]);
    if ($blocks) {
      $block = reset($blocks);
    } else {
      $block = BlockContent::create([
        'type' => 'basic',
        'info' => $slug,
        'body' => [
          'value' => <<<HTML
            <center>
              Copyright &copy; 2026 <a href="https://wasyaco.com/">WasyaCo</a> &nbsp; All rights Reserved
            </center>
          HTML,
          'format' => 'full_html',
        ],
      ]);
      $block->save();
    }

    /* place it in the theme */
    $theme = \Drupal::config('system.theme')->get('default');
    if (!Block::load("{$theme}_{$slug}")) {
      Block::create([
        'id' => "{$theme}_{$slug}",
        'theme' => $theme,
        'plugin' => 'block_content:' . $block->uuid(),
        'region' => 'footer_fifth',
        'weight' => 0,
        'visibility' => [],
        'settings' => [
          'id' => 'block_content:' . $block->uuid(),
          'label' => 'Copyright',
          'label_display' => false,
          'provider' => 'block_content',
          'view_mode' => 'full',
        ],
      ])->save();
    }

    return $block;
  }

  /*
   * the block must have been already setup.
   * and this doesn't place the block?! but only creates it.
  **/
  public static function create_block($block_type, $info, $config) {
    $storage = \Drupal::entityTypeManager()->getStorage('block_content');

    $blocks = $storage->loadByProperties([
      'type' => $block_type,
      'info' => $info,
    ]);
    if ($blocks) {
      $block = reset($blocks);
    }
    else {
      $config2 = array_merge($config, $config['fields']);
      $block = BlockContent::create($config2);
      $block->save();
    }

  }

  /*
   * hours_of_operation, a basic block.
  **/
  public static function hours_of_operation() {
    $slug = 'hours_of_operation';

    $blocks = \Drupal::entityTypeManager()->getStorage('block_content')->loadByProperties([
      'type' => 'basic',
      'info' => $slug,
    ]);
    if ($blocks) {
      $block = reset($blocks);
    } else {
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

    /* place it in the theme */
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

    return $block;
  }


  /*
   * the block definition. should be create_block_type()
  **/
  public static function create_block_type($block_type, $fields) {
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

  /* use setup_about_20()
  **/
  // public static function setup_about_10() {
  //   $section_name = 'section_about_10';
  //   $fields = [
  //     'body'              => DefaultFields::body,
  //     'field_class_name'  => DefaultFields::text,
  //     'field_custom_css'  => DefaultFields::text_long,
  //     'field_image_hero'  => DefaultFields::image,
  //     'field_image_thumb' => DefaultFields::image,
  //     'field_is_reverse'  => DefaultFields::toggle,
  //     'field_link_text'   => DefaultFields::text,
  //     'field_link_url'    => DefaultFields::text,
  //     'field_subtitle'    => DefaultFields::text,
  //   ];
  //   self::create_block_type($section_name, $fields);
  // }

  public static function setup_about_20() {
    $section_name = 'section_about_20';
    $fields = [
      'field_class_name'  => DefaultFields::text,
      'field_custom_css'  => DefaultFields::text_long,
      'field_style'       => DefaultFields::select_3style,
      'field_is_reverse'  => DefaultFields::toggle,

      'field_subtitle'    => DefaultFields::text,
      'body'              => DefaultFields::body,

      'field_image_hero'  => DefaultFields::image,
      'field_image_thumb' => DefaultFields::image,
      'field_icon'        => DefaultFields::file,

      'field_link_text'   => DefaultFields::text,
      'field_link_url'    => DefaultFields::text,

    ];
    self::create_block_type($section_name, $fields);
  }
  /*
  **/
  public static function setup_about_3cards() {
    $section_name = 'section_about_3cards';
    $fields = [
      'field_tagline'    => DefaultFields::text,
      'field_subtitle'    => DefaultFields::text,
      'body'              => DefaultFields::body,

      'field_class_name'  => DefaultFields::text,
      'field_custom_css'  => DefaultFields::text_long,

      'field_link_text'   => DefaultFields::text,
      'field_link_url'    => DefaultFields::text,

      'field_1_subtitle'    => DefaultFields::text,
      'field_1_body'        => DefaultFields::text,
      'field_1_icon'        => DefaultFields::file,

      'field_2_subtitle'    => DefaultFields::text,
      'field_2_body'        => DefaultFields::text,
      'field_2_icon'        => DefaultFields::file,

      'field_3_subtitle'    => DefaultFields::text,
      'field_3_body'        => DefaultFields::text,
      'field_3_icon'        => DefaultFields::file,
    ];
    self::create_block_type($section_name, $fields);
  }

  /*
   * section_callout_parallax
  **/
  public static function setup_callout_parallax() {
    $section_name = 'section_callout_parallax';
    $fields = [
      'body'              => DefaultFields::body,
      'field_class_name'  => DefaultFields::text,
      'field_custom_css'  => DefaultFields::text_long,
      'field_image_hero'  => DefaultFields::image,
      'field_image_thumb' => DefaultFields::image,
      'field_link_text'   => DefaultFields::text,
      'field_link_url'    => DefaultFields::text,
      'field_subtitle'    => DefaultFields::text,
    ];
    self::create_block_type($section_name, $fields);
  }


  /*
   * section_hero_video
  **/
  public static function setup_hero_video() {
    $section_name = 'section_hero_video';
    $fields = [
      'body'             => DefaultFields::body,
      'field_autoplay'   => DefaultFields::toggle,
      'field_class_name' => DefaultFields::text,
      'field_video_file' => DefaultFields::file,
      'field_image_hero' => DefaultFields::image,
    ];
    self::create_block_type($section_name, $fields);
  }

  /*
  **/
  public static function setup_list_10() {
    $section_name = 'section_list_10';
    $fields = [
      'body'              => DefaultFields::body,
      'field_class_name'  => DefaultFields::text,
      'field_custom_css'  => DefaultFields::text_long,
      'field_style'       => DefaultFields::select_3style,
      // 'field_is_reverse'  => DefaultFields::toggle,

      'field_subtitle'    => DefaultFields::text,
      'field_view_ref' => DefaultFields::view_ref,
      // 'body'              => DefaultFields::body,

      // 'field_image_hero'  => DefaultFields::image,
      // 'field_image_thumb' => DefaultFields::image,

      // 'field_link_text'   => DefaultFields::text,
      // 'field_link_url'    => DefaultFields::text,
    ];
    self::create_block_type($section_name, $fields);
  }

  /*
  **/
  public static function setup_slider_images() {
    $section_name = 'section_slider_images';
    $fields = [
      'field_class_name'  => DefaultFields::text,
      'field_custom_css'  => DefaultFields::text_long,
      'field_style'       => DefaultFields::select_3style,

      'field_subtitle'    => DefaultFields::text,
      'body'              => DefaultFields::body,
      'field_view_ref'    => DefaultFields::view_ref,
      'field_image_hero'  => DefaultFields::image,
      'field_link_text'   => DefaultFields::text,
      'field_link_url'    => DefaultFields::text,
    ];
    self::create_block_type($section_name, $fields);
  }

}
