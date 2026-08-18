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

use Drupal\webform\Entity\Webform;

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
  public static function image_field_from_url(string $field_name, string $url): array {
    $response = \Drupal::httpClient()->get($url);
    $contents = (string) $response->getBody();
    $directory = 'public://' . $field_name;
    \Drupal::service('file_system')->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY);
    $destination = $directory . '/' . basename(parse_url($url, PHP_URL_PATH));
    $file = \Drupal::service('file.repository')->writeData(
      $contents,
      $destination,
      FileSystemInterface::EXISTS_RENAME
    );
    $file->setPermanent();
    $file->save();

    return [
      'target_id' => $file->id(),
      'alt' => '',
    ];
  }

  /*
  **/
  public static function prepare_field_values(array $fields): array {
    $values = [];
    foreach ($fields as $field_name => $field_value) {
      if (str_contains($field_name, 'image') && is_string($field_value) && filter_var($field_value, FILTER_VALIDATE_URL)) {
        $values[$field_name] = self::image_field_from_url($field_name, $field_value);
        continue;
      }
      if ($field_name === 'body' && is_array($field_value) && !isset($field_value['format'])) {
        $field_value['format'] = 'basic_html';
      }
      $values[$field_name] = $field_value;
    }
    return $values;
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
      throw new \LogicException('zzq - Not implemented');
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
      if ($item['meta']['existing'] == 'destroy') {
        $node->delete();
      } else {
        \Drupal::messenger()->addStatus("Node `$path` already exists.");
        return;
      }
    }

    $values = [
      'type' => $item['type'],
      'status' => 1,
      'path' => [
        'alias' => $item['path'],
        'pathauto' => 0,
      ],
    ];
    $values = array_merge($values, self::prepare_field_values($item['fields']));
    $node = Node::create($values);
    $node->save();


    if ($item['sections']) {
      $outs = [];
      foreach($item['sections'] as $this_section) {

        $section = new Section( $this_section['type'], $this_section['config']??[] );
        foreach ($this_section['regions'] as $region_name => $region_blocks) {
          foreach ($region_blocks as $block_c) {
            if (is_string($block_c)) {
              [$provider, $name] = explode(':', $block_c, 2);
              error_log("+++ +++ block_c: $block_c");

              switch($provider) {
                case 'field':


                  $extra = [
                    'id' => "field_block:node:{$node->bundle()}:$name",
                    'label' => $block_c,
                    'label_display' => false,
                    'provider' => 'layout_builder',
                    'context_mapping' => [
                      'entity' => 'layout_builder.entity',
                    ],
                    'formatter' => [
                      'type' => 'text_default',
                      'label' => 'hidden',
                      'settings' => [],
                      'third_party_settings' => [],
                    ],
                  ];
                  $uuid = \Drupal::service('uuid')->generate();
                  $component = new SectionComponent( $uuid, $region_name, $extra );
                  $section->appendComponent($component);


                  break;
                case 'webform':


                  $extra = [
                    'id' => 'webform_block',
                    'label' => $block_c,
                    'label_display' => FALSE,
                    'provider' => 'webform',
                    'webform_id' => $name,
                  ];
                  $uuid = \Drupal::service('uuid')->generate();
                  $component = new SectionComponent($uuid, $region_name, $extra);
                  $section->appendComponent($component);


                  break;
                default:
                  throw new \Exception('iot - this should never happen');
              }
            } elseif (is_array($block_c)) {

              $provider = $block_c['provider']??'block_content';
              error_log("+++ +++ explicit provider: $provider");

              switch ($provider) {
                case 'views':


                  $extra = [
                    'id' => "views_block:{$block_c['view_id']}",
                    'label' => $block_c['label'],
                    'label_display' => FALSE,
                    'provider' => $block_c['provider'],
                  ];
                  $uuid = \Drupal::service('uuid')->generate();
                  $component = new SectionComponent( $uuid, $region_name, $extra );
                  $section->appendComponent($component);


                  break;
                case 'block_content':


                  $blocks = \Drupal::entityTypeManager()
                    ->getStorage('block_content')
                    ->loadByProperties([
                      'info' => $block_c['info'],
                    ]);
                  $block = reset($blocks);
                  $extra = [
                    'id' => "block_content:{$block->uuid()}",
                    'label' => $block_c['label'],
                    'label_display' => $block_c['label_display']??false,
                    'provider' => $provider,
                  ];
                  $uuid = \Drupal::service('uuid')->generate();
                  $component = new SectionComponent( $uuid, $region_name, $extra );
                  $section->appendComponent($component);


                  break;
                default:
                  throw new \Exception('iou - this should never happen');
              } // end switch $provider

            }
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
  public static function create_webform($item) {
    if (Webform::load($item['id'])) {
      return;
    }
    $elements = [];
    foreach($item['elements'] as $name => $config) {
      $elements[$name] = [
        '#type' => $config['type'] ?? 'textfield',
        '#title' => $config['title'] ?? $name,
        '#required' => $config['required'] ?? false,
      ];
    }
    $elements['captcha'] = [
      '#type' => 'captcha',
      '#captcha_type' => 'hcaptcha/hCaptcha',
    ];
    $elements['actions'] = [
      '#type' => 'webform_actions',
      '#title' => 'Submit',
      '#submit__label' => 'Send',
    ];

    $settings = Webform::getDefaultSettings();

    $webform = Webform::create([
      'id' => $item['id'],
      'title' => $item['title'] ?? $item['id'],
      'elements' => Yaml::encode($elements),
      'settings' => $settings,
    ]);

    $webform->save();
  }

}

