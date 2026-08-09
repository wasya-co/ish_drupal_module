<?php
namespace Drupal\ish_drupal_module\Config;

use Drupal\block\Entity\Block;
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
use Drupal\filter\Entity\FilterFormat;

use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\node\NodeInterface;

use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\taxonomy\Entity\Term;

use Symfony\Component\HttpFoundation\RedirectResponse;


use Drupal\ish_drupal_module\Config\DefaultFields;

/*
**/
class ContentTypesConfig {


  /*
  **/
  public static function enable_layout_builder_for($content_type, $display_mode) {
    $default_display = EntityViewDisplay::load("node.$content_type.default");
    if (!$default_display) {
      $default_display = EntityViewDisplay::create([
        'targetEntityType' => 'node',
        'bundle' => $content_type,
        'mode' => 'default',
        'status' => TRUE,
      ]);
    }
    $default_display->setThirdPartySetting('layout_builder', 'enabled', TRUE);
    $default_display->setThirdPartySetting('layout_builder', 'allow_custom', TRUE);
    $default_display->save();

    if ('default' != $display_mode) {
      $display = EntityViewDisplay::load("node.$content_type.$display_mode");
      if (!$display) {
        $display = EntityViewDisplay::create([
          'targetEntityType' => 'node',
          'bundle' => $content_type,
          'mode' => $display_mode,
          'status' => TRUE,
        ]);
      }
      $display->setThirdPartySetting('layout_builder', 'enabled', TRUE);
      $display->setThirdPartySetting('layout_builder', 'allow_custom', TRUE);
      $display->save();
    }
  }

  /*
  **/
  public static function setup_content_type($content_type, $fields) {
    if (!NodeType::load($content_type)) {
      NodeType::create([
        'type' => $content_type,
        'name' => $content_type,
        'description' => $content_type,
        'new_revision' => FALSE,
        'preview_mode' => DRUPAL_OPTIONAL,
        'display_submitted' => FALSE,
      ])->save();
    }

    $form_display = EntityFormDisplay::load("node.$content_type.default");
    if (!$form_display) {
      $form_display = EntityFormDisplay::create([
        'targetEntityType' => 'node',
        'bundle' => $content_type,
        'mode' => 'default',
        'status' => TRUE,
      ]);
    }

    $display = EntityViewDisplay::load("node.$content_type.full");
    if (!$display) {
      $display = EntityViewDisplay::create([
        'targetEntityType' => 'node',
        'bundle' => $content_type,
        'mode' => 'full',
        'status' => TRUE,
      ]);
    }


    foreach($fields as $field => $field_c) {

      $storage = FieldStorageConfig::loadByName('node', $field);
      if (!$storage) {
        $storage = FieldStorageConfig::create([
          'field_name' => $field,
          'entity_type' => 'node',
          'type' => $field_c['type'],
          'cardinality' => $field_c['cardinality'] ?? 1,
          'settings' => $field_c['field_storage_config_settings'] ?? [],
        ]);
        $storage->save();
      }
      $field_cfg = FieldConfig::loadByName('node', $content_type, $field);
      if (!$field_cfg) {
        $field_cfg = FieldConfig::create([
          'field_storage' => $storage,
          'bundle' => $content_type,
          'label' => $field,
          'required' => FALSE,
          'translatable' => !!$field_c['translatable'],
          'settings' => $field_c['field_config_settings'] ?? [],
        ])->save();
      }

      $form_display ->setComponent($field, [
        'type' => $field_c['form_display'],
        'weight' => 20,
        'region' => 'content',
        'settings' => $field_c['form_display_settings'] ?? [],
      ])->save();

      if ($field_c['display'] ?? null) {
        $display->setComponent($field, [
          'type' => $field_c['display'],
          'label' => 'hidden',
          'weight' => 20,
          'region' => 'content',
          'settings' => [],
        ])->save();
      }

    } // end fields loop
  }

  /*
   * issue is same as advanced_page, but without image_hero.
  **/
  public static function setup_issue() {
    $fields = DefaultFields::default_node_fields;
    unset( $fields['field_image_hero'] );
    self::setup_content_type('issue', $fields);
    self::enable_layout_builder_for('issue', 'full');
  }

  /*
   * slide is the same as advanced_page, but without layout_builder
  **/
  public static function setup_slide() {
    $content_type_c = self::content_types['advanced_page'];
    unset( $content_type_c['layout_builder'] );
    self::setup_content_type('slide', $content_type_c);
  }

}





/* paragraphs */
/*
  $storage = FieldStorageConfig::loadByName('node', 'field_paragraphs');
  if (!$storage) {
    $storage = FieldStorageConfig::create([
      'field_name' => 'field_paragraphs',
      'entity_type' => 'node',
      'type' => 'entity_reference_revisions',
      'cardinality' => -1,
      'settings' => [
        'target_type' => 'paragraph',
      ],
      'translatable' => TRUE,
    ]);
    $storage->save();
  }
  $field = FieldConfig::loadByName('node', 'advanced_page', 'field_paragraphs');
  if (!$field) {
    $field = FieldConfig::create([
      'field_storage' => $storage,
      'bundle' => 'advanced_page',
      'label' => 'Paragraphs',
      'description' => '',
      'required' => FALSE,
      'translatable' => TRUE,
      'settings' => [],
    ]);
    $field->save();
  }
  $form_display ->setComponent('field_paragraphs', [
    'type' => 'paragraphs',
    'weight' => 20,
    'region' => 'content',
    'settings' => [],
  ])->save();
  $display->setComponent('body', [
    'type' => 'entity_reference_revisions_entity_view',
    'label' => 'hidden',
    'weight' => 20,
    'region' => 'content',
    'settings' => [],
  ])->save();
*/
