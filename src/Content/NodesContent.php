<?php
namespace Drupal\ish_drupal_module\Content;

use Drupal\block\Entity\Block;
use Drupal\block_content\Entity\BlockContent;
use Drupal\block_content\Entity\BlockContentType;

use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Uuid\Uuid;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

use Drupal\file\Entity\File;

use Drupal\layout_builder\Section;
use Drupal\layout_builder\SectionComponent;

use Drupal\node\Entity\Node;

/*
**/
class NodesContent {

  /*
  **/
  public static function get_node_by_path(string $path): ?\Drupal\node\Entity\Node {
    $internal_path = \Drupal::service('path_alias.manager')
      ->getPathByAlias($path);
    if (preg_match('/^\/node\/(\d+)$/', $internal_path, $matches)) {
      return \Drupal\node\Entity\Node::load($matches[1]);
    }
    return NULL;
  }

  /*
  **/
  public static function add_section_to($which, $this_section) {
    if ($which['by_title']) {
      $nodes = \Drupal::entityTypeManager()
        ->getStorage('node')
        ->loadByProperties([
          'title' => $which['by_title'],
        ]);
      $node = reset($nodes);
    } else {
      throw new \LogicException('Not implemented zzq');
    }

    $section = new Section( $this_section['type'], $this_section['config']??[] );
    foreach ($this_section['regions'] as $region_name => $region_c) {

      if ('views' == $region_c['provider']) {

        $extra = [
          'id' => "views_block:{$region_c['view_id']}",
          'label' => $region_c['label'],
          'label_display' => FALSE,
          'provider' => $region_c['provider'],
        ];
        $uuid = \Drupal::service('uuid')->generate();
        $component = new SectionComponent( $uuid, $region_name, $extra );
        $section->appendComponent($component);

      } elseif ('block_content' == $region_c['provider']) {

        $blocks = \Drupal::entityTypeManager()
          ->getStorage('block_content')
          ->loadByProperties([
            'info' => $region_c['info'],
          ]);
        $block = reset($blocks);
        $extra = [
          'id' => "block_content:{$block->uuid()}",
          'label' => $region_c['label'],
          'label_display' => FALSE,
          'provider' => $region_c['provider'],
        ];
        $uuid = \Drupal::service('uuid')->generate();
        $component = new SectionComponent( $uuid, $region_name, $extra );
        $section->appendComponent($component);

      }
    } // end foreach regions


    $layout_builder = $node->get('layout_builder__layout');
    $sections = $layout_builder->getSections();
    $sections[] = $section;
    $layout_builder->setValue($sections);
    $node->save();
  }

  /*
   * 2026-08-18 _vp_ continue
  **/
  public static function create_node($type, $path, $item) {

    $node = self::get_node_by_path($path);
    if ($node) {
      // return;
      $node->delete(); // _TODO: remove
    }

    $values = [
      'type' => $item['type'],
      'status' => 1,
      'path' => [
        'alias' => $item['path'],
        'pathauto' => 0,
      ],
    ];
    foreach ($item['fields'] as $field_name => $field_value) {
      if (str_contains($field_name, 'image')) {
        $contents = file_get_contents($field_value);
        $directory = 'public://field_image_thumb';
        \Drupal::service('file_system')->prepareDirectory( $directory, FileSystemInterface::CREATE_DIRECTORY );
        $destination = $directory . '/' . basename(parse_url($field_value, PHP_URL_PATH));
        $file = \Drupal::service('file.repository')->writeData(
          $contents,
          $destination,
          FileSystemInterface::EXISTS_RENAME
        );
        $values['field_image_thumb'] = [
          'target_id' => $file->id(),
          'alt' => '',
        ];
      } else {
        $values[$field_name] = $field_value;
      }
    } // end fields
    $node = Node::create($values);
    $node->save();


    if ($item['sections']) {
      $outs = [];
      foreach($item['sections'] as $this_section) {

        $section = new Section( $this_section['type'], $this_section['config']??[] );
        foreach ($this_section['regions'] as $region_name => $region_c) {

          switch ($region_c['provider']??'block_content') {
            case 'views':

              $extra = [
                'id' => "views_block:{$region_c['view_id']}",
                'label' => $region_c['label'],
                'label_display' => FALSE,
                'provider' => $region_c['provider'],
              ];
              $uuid = \Drupal::service('uuid')->generate();
              $component = new SectionComponent( $uuid, $region_name, $extra );
              $section->appendComponent($component);

              break;
            case 'block_content':

              $blocks = \Drupal::entityTypeManager()
                ->getStorage('block_content')
                ->loadByProperties([
                  'info' => $region_c['info'],
                ]);
              $block = reset($blocks);
              $extra = [
                'id' => "block_content:{$block->uuid()}",
                'label' => $region_c['label'],
                'label_display' => $region_c['label_display']??false,
                'provider' => $region_c['provider'],
              ];
              $uuid = \Drupal::service('uuid')->generate();
              $component = new SectionComponent( $uuid, $region_name, $extra );
              $section->appendComponent($component);

              break;
            default:
              throw new \Exception('Not implemented :: NodesContent');
          }
        }

        $outs[] = $section;
      } // end foreach sections
      $node->set('layout_builder__layout', $outs);
      $node->save();
    }
  }


  /*
  **/
  public static function create_node_by($content_type, $config) {
    $nodes = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties([
        'type' => $content_type,
        'title' => $config['fields']['title'],
      ]);
    $node = reset($nodes);
    if (!$node) {
      $node = Node::create([
        'path' => [
          'alias' => $config['path'],
          'pathauto' => 0,
        ],
        'type' => $content_type,
        'title' => $config['fields']['title'],
        'status' => 1,
      ])->save();
    }

    $sections = $config['sections'];
    $outs = [];
    foreach($sections as $section) {

      $section = new Section( $section['type'], $section['config'] );
      foreach ($section['regions'] as $region => $region_c) {

        $extra = [
          'id' => "views_block:{$region_c['view_id']}",
          'label' => $region_c['label'],
          'label_display' => FALSE,
          'provider' => 'views',
        ];
        $component = new SectionComponent( Uuid::generate(), $region, $extra );
        $section->appendComponent($component);

      }
      $outs[] = $section;
    }
    $node->set('layout_builder__layout', $outs);
    $node->save();
  }

}

