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


use Drupal\ish_drupal_module\Config\DefaultFields;

class SectionsConfig {

  /*
   * section hero video
  **/
  public static function section_hero_video() {
    $section_name = 'section_hero_video';
    $fields = [
      'body'              => DefaultFields::body,
      'field_autoplay' => DefaultFields::toggle,
      'field_video_file' => DefaultFields::file,
      'field_image' => DefaultFields::image,
    ];
    self::setup_section($section_name, $fields);
  }

  /*
  **/
  public static function setup_section($section_name, $fields) {
    if (!BlockContentType::load($section_name)) {
      BlockContentType::create([
        'id' => $section_name,
        'label' => $section_name,
        'revision' => TRUE,
      ])->save();
    }

    $form_display = EntityFormDisplay::load("block_content.$section_name.default");
    if (!$form_display) {
      $form_display = EntityFormDisplay::create([
        'targetEntityType' => 'block_content',
        'bundle' => $section_name,
        'mode' => 'default',
        'status' => TRUE,
      ]);
    }

    $display = EntityViewDisplay::load("block_content.$section_name.full");
    if (!$display) {
      $display = EntityViewDisplay::create([
        'targetEntityType' => 'block_content',
        'bundle' => $section_name,
        'mode' => 'full',
        'status' => TRUE,
      ]);
    }

    foreach ($fields as $field_name => $field_c) {
      if (!FieldStorageConfig::loadByName('block_content', $field_name)) {
        FieldStorageConfig::create([
          'field_name' => $field_name,
          'entity_type' => 'block_content',
          'type' => $field_c['type'],
          'cardinality' => $field_c['cardinality'] ?? 1,
        ])->save();
      }
      if (!FieldConfig::loadByName('block_content', $section_name, $field_name)) {
        FieldConfig::create([
          'field_name' => $field_name,
          'entity_type' => 'block_content',
          'bundle' => $section_name,
          'label' => $field_name,
          'required' => FALSE,
          'translatable' => $field_c['translatable'] ?? false,
          'settings' => $field_c['field_config_settings'] ?? [],
        ])->save();
      }
      $form_display->setComponent($field_name, [
        'type' => $field_c['form_display'],
      ])->save();
      $display->setComponent($field_name, [
        'label' => 'hidden',
        'type' => $field_c['display'],
      ])->save();
    } // end foreach

  }

  /*
   * section_callout_parallax
  **/
  public static function section_callout_parallax() {
    $section_name = 'section_callout_parallax';
    $fields = [
      'body'              => DefaultFields::body,
      'field_class_name'  => DefaultFields::text,
      'field_custom_css'  => DefaultFields::text_long,
      'field_image_bg'    => DefaultFields::image,
      'field_image_thumb' => DefaultFields::image,
      'field_link_text'   => DefaultFields::text,
      'field_link_url'    => DefaultFields::text,
      'field_title'       => DefaultFields::text,
    ];
    self::setup_section($section_name, $fields);
  }

}



