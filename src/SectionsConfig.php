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

class SectionsConfig {

  /*
   * section hero video
  **/
  public static function section_hero_video() {

    if (!BlockContentType::load('section_hero_video')) {
      BlockContentType::create([
        'id' => 'section_hero_video',
        'label' => 'Section Hero Video',
        'revision' => TRUE,
      ])->save();
    }

    $form_display = EntityFormDisplay::load('block_content.section_hero_video.default');
    if (!$form_display) {
      $form_display = EntityFormDisplay::create([
        'targetEntityType' => 'block_content',
        'bundle' => 'section_hero_video',
        'mode' => 'default',
        'status' => TRUE,
      ]);
    }

    $display = EntityViewDisplay::load('block_content.section_hero_video.full');
    if (!$display) {
      $display = EntityViewDisplay::create([
        'targetEntityType' => 'block_content',
        'bundle' => 'section_hero_video',
        'mode' => 'full',
        'status' => TRUE,
      ]);
    }



    if (!FieldStorageConfig::loadByName('block_content', 'body')) {
      FieldStorageConfig::create([
        'field_name' => 'body',
        'entity_type' => 'block_content',
        'type' => 'text_with_summary',
        'cardinality' => 1,
        'translatable' => TRUE,
      ])->save();
    }
    if (!FieldConfig::loadByName('block_content', 'section_hero_video', 'body')) {
      FieldConfig::create([
        'field_name' => 'body',
        'entity_type' => 'block_content',
        'bundle' => 'section_hero_video',
        'label' => 'body',
        'required' => FALSE,
        'translatable' => TRUE,
        'settings' => [
          'display_summary' => TRUE,
          'required_summary' => FALSE,
        ],
      ])->save();
    }
    $form_display->setComponent('body', [
      'type' => 'text_textarea_with_summary',
      'weight' => 10,
    ])->save();
    $display->setComponent('body', [
      'label' => 'hidden',
      'type' => 'text_default',
      'weight' => 10,
    ])->save();


    if (!FieldStorageConfig::loadByName('block_content', 'field_video_file')) {
      FieldStorageConfig::create([
        'field_name' => 'field_video_file',
        'entity_type' => 'block_content',
        'type' => 'file',
        'cardinality' => 1,
        'settings' => [
          'target_type' => 'file',
          'uri_scheme' => 'public',
        ],
      ])->save();
    }
    if (!FieldConfig::loadByName('block_content', 'section_hero_video', 'field_video_file')) {
      FieldConfig::create([
        'field_name' => 'field_video_file',
        'entity_type' => 'block_content',
        'bundle' => 'section_hero_video',
        'label' => 'field_video_file',
        'required' => FALSE,
        'settings' => [
          'file_extensions' => 'mp4 webm ogv',
          'description_field' => FALSE,
        ],
      ])->save();
    }
    $form_display->setComponent('field_video_file', [
      'type' => 'file_generic',
      'weight' => 10,
    ])->save();
    $display->setComponent('field_video_file', [
      'label' => 'hidden',
      'type' => 'file_url_plain',
      'weight' => 10,
    ])->save();


    if (!FieldStorageConfig::loadByName('block_content', 'field_image')) {
      FieldStorageConfig::create([
        'field_name' => 'field_image',
        'entity_type' => 'block_content',
        'type' => 'file',
        'cardinality' => 1,
        'settings' => [
          'target_type' => 'file',
          'uri_scheme' => 'public',
        ],
      ])->save();
    }
    if (!FieldConfig::loadByName('block_content', 'section_hero_video', 'field_image')) {
      FieldConfig::create([
        'field_name' => 'field_image',
        'entity_type' => 'block_content',
        'bundle' => 'section_hero_video',
        'label' => 'field_image',
        'required' => FALSE,
        'settings' => [
          'file_extensions' => 'jpg jpeg png gif',
          'description_field' => FALSE,
        ],
      ])->save();
    }
    $form_display->setComponent('field_image', [
      'type' => 'file_generic',
      'weight' => 10,
    ])->save();
    $display->setComponent('field_image', [
      'label' => 'hidden',
      'type' => 'file_url_plain',
      'weight' => 10,
    ])->save();
  }

}



