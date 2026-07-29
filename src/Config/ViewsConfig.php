<?php
namespace Drupal\ish_drupal_module;


use Drupal\block\Entity\Block;
use Drupal\block_content\Entity\BlockContentType;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Serialization\Yaml;
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

use Drupal\views\Entity\View;

use Symfony\Component\HttpFoundation\RedirectResponse;



/*
**/
class ViewsConfig {

  public const DEFAULT_DISPLAY = [
    'id' => 'default',
    'display_plugin' => 'default',
    'display_title' => 'Master',
    'position' => 0,
    'display_options' => [
      'title' => '',
      'access' => [
        'type' => 'perm',
        'options' => [
          'perm' => 'access content',
        ],
      ],
      'cache' => [
        'type' => 'tag',
        'options' => [],
      ],
      'query' => [
        'type' => 'views_query',
        'options' => [],
      ],
      'exposed_form' => [
        'type' => 'basic',
        'options' => [],
      ],
      'pager' => [
        'type' => 'full',
        'options' => [
          'items_per_page' => 10,
          'offset' => 0,
        ],
      ],
      'style' => [
        'type' => 'default',
        'options' => [
          'row_class' => '',
          'default_row_class' => TRUE,
        ],
      ],
      'row' => [
        'type' => 'entity:node',
        'options' => [
          'relationship' => 'none',
          'view_mode' => 'teaser',
        ],
      ],
      'filters' => [],
      'sorts' => [],
      'relationships' => [],
      'arguments' => [],
      'header' => [],
      'footer' => [],
      'empty' => [],
      'fields' => [],
      'display_extenders' => [],
    ],
  ];

  public const INHERIT_DEFAULTS = [
    'title' => TRUE,
    'css_class' => TRUE,
    'use_ajax' => TRUE,
    'use_more' => TRUE,
    'use_more_always' => TRUE,
    'use_more_text' => TRUE,
    'link_display' => TRUE,
    'link_url' => TRUE,
    'group_by' => TRUE,
    'query' => TRUE,
    'relationships' => TRUE,
    'arguments' => TRUE,
    'filters' => TRUE,
    'filter_groups' => TRUE,
    'sorts' => TRUE,
    'header' => TRUE,
    'footer' => TRUE,
    'empty' => TRUE,
    'fields' => TRUE,
    'style' => TRUE,
    'row' => TRUE,
    'pager' => TRUE,
    'exposed_form' => TRUE,
    'exposed_block' => TRUE,
    'access' => TRUE,
    'cache' => TRUE,
  ];

  public const default_teaser = [
    'fields' => [
      'body' => [
        'display' => 'text_default',
      ],
      'field_image_thumb' => [
        'display' => 'image',
        'display_settings' => [
          'image_style' => 'thumbnail', // Or another image style.
          'image_link' => 'content',     // 'content', 'file', or ''.
        ],
      ],
    ],
  ];

  /*
  **/
  public static function create_from_file($filename) {
    $config_path = \Drupal::service('extension.list.module')
      ->getPath('ish_drupal_module') . '/config/install/views.view.directory.yml';
    $data = Yaml::decode(file_get_contents($config_path));
    if (!View::load($data['id'])) {
      $view = View::create($data);
      $view->save();
    }
  }

  /*
  **/
  public static function create_from_file_2() {
    $view_id = 'services';

    $config_path = \Drupal::service('extension.list.module')
      ->getPath('ish_drupal_module') . '/config/install/views.view.directory.yml';
    $data = Yaml::decode(file_get_contents($config_path));
    $data['id'] = $view_id;
    $data['label'] = $view_id;

    $data['display']['block_b'] = self::DEFAULT_DISPLAY;
    $data['display']['block_b']['display_options'] = [
      'css_class' => 'container row',
      'filters' => [
        'status' => [
          'id' => 'status',
          'table' => 'node_field_data',
          'field' => 'status',
          'value' => '1',
          'entity_type' => 'node',
          'entity_field' => 'status',
        ],
        'type' => [
          'entity_type' => 'node',
          'entity_field' => 'type',
          'id' => 'type',
          'field' => 'type',
          'plugin_id' => 'bundle',
          'table' => 'node_field_data',
          'value' => [
            'directory_item' => 'directory_item',
          ],
        ],
      ],
      'row' => [
        'options' => [
          'view_mode' => 'teaser',
        ],
        'plugin_id' => 'entity:node',
        'type' => 'entity:node',
      ],
      'style' => [
        'type' => 'default',
        'plugin_id' => 'default',
        'options' => [
          'row_class' => 'col-sm-6 col-md-3',
          'default_row_class' => false,
        ],
      ],
    ];

    if (!View::load($data['id'])) {
      $view = View::create($data);
      $view->save();
    }
  }

  /*
  **/
  public static function create_view($view_id, $display_name, $config) {
    $view = View::load($view_id);
    // if ($view) { $view->delete(); } // _TODO: remove

    /* if (!$view) {
      $view = View::create([
        'id' => $view_id,
        'label' => $view_id,
        'base_table' => 'node_field_data',
        'base_field' => 'nid',
        'description' => '',
        'core' => '10.x',

        'display' => [
          'default' => [
            'id' => 'default',
            'display_plugin' => 'default',
            'position' => 0,
            'display_title' => 'Master',
            'display_options' => [
              'access' => [
                'type' => 'none',
              ],
              'cache' => [
                'type' => 'tag',
              ],
              'query' => [
                'type' => 'views_query',
              ],
              'pager' => [
                'type' => 'none',
              ],
              'style' => [
                'type' => 'default',
              ],
              'row' => [
                'type' => 'entity:node',
                'options' => [
                  'view_mode' => 'teaser',
                ],
              ],
              'filters' => [
                'status' => [
                  'id' => 'status',
                  'table' => 'node_field_data',
                  'field' => 'status',
                  'entity_type' => 'node',
                  'entity_field' => 'status',
                  'value' => 1,
                  'plugin_id' => 'boolean',
                ],
              ],
            ],
          ],
          // 'block_1' => [
          //   'id' => 'block_1',
          //   'display_plugin' => 'block',
          //   'position' => 1,
          //   'display_title' => 'Block',
          //   'display_options' => [],
          // ],
        ],
      ]);
      $view->save();
      $view = View::load($view_id);
    } */

    $display = $view->get('display');

    // error_log( var_export( $display, true ) );
    // var_dump($display); exit;

    // if ($config['default']['display_options']['filters']) {
    //   $display['default']['display_options']['filters'] = $config['default']['display_options']['filters'];
    // }


    $display[$display_name] = [
      'id' => $display_name,
      'display_options' => $config['display_options'],
      'display_plugin' => $config['display_plugin'],
      'display_title' => $config['display_title'],
      'defaults' => $config['defaults'],
    ];

    // error_log( var_export( $display, true ) );

    $view->set('display', $display);
    $view->save();
    // $view = View::load($view_id);
  }

  /*
  **/
  public static function create_one() {
    $view_id = 'directory';
    $view_mode = 'teaser';

    $view = View::load($view_id);
    if ($view) { $view->delete(); } // _TODO: remove

    if (!$view) {
      $view = View::create([
        'id' => $view_id,
        'label' => $view_id,
        'base_table' => 'node_field_data',
        'base_field' => 'nid',
        'description' => '',
        'core' => '10.x',
        'display' => [],
      ]);
      $view->save();
    }
    $view->set('display', [
      'default' => [
        'id' => 'default',
        'display_plugin' => 'default',
        'display_title' => 'Master',
        'position' => 0,
        'display_options' => [
          'access' => [
            'type' => 'perm',
            'options' => [
              'perm' => 'access content',
            ],
          ],
          'query' => [
            'type' => 'views_query',
          ],
          'pager' => [
            'type' => 'full',
            'options' => [
              'items_per_page' => 10,
            ],
          ],
          'style' => [
            'type' => 'default',
          ],
          'row' => [
            'type' => 'entity:node',
            'options' => [
              'view_mode' => $view_mode,
            ],
          ],
          'filters' => [
            'status' => [
              'id' => 'status',
              'table' => 'node_field_data',
              'field' => 'status',
              'value' => '1',
              'entity_type' => 'node',
              'entity_field' => 'status',
            ],
            'type' => [
              'entity_type' => 'node',
              'entity_field' => 'type',
              'id' => 'type',
              'field' => 'type',
              'plugin_id' => 'bundle',
              'table' => 'node_field_data',
              'value' => [
                'directory_item' => 'directory_item',
              ],
            ],
          ],
        ],
      ],
      'page_1' => [
        'id' => 'page_1',
        'display_plugin' => 'page',
        'display_title' => 'Page',
        'position' => 1,
        'display_options' => [
          'path' => 'directory',
        ],
      ],
      'block_1' => [
        'display_plugin' => 'block',
        'display_title' => 'Block',
        'display_options' => [
          'css_class' => 'container row',
          'style' => [
            'type' => 'default',
            'options' => [
              'row_class' => 'col-sm-6 col-md-3',
              'default_row_class' => false,
            ],
          ],
        ],
        'id' => 'block_1',
        'position' => 2,
      ],
    ]);

    $view->save();
  }

  /*
  **/
  public static function setup_display_for($content_type, $display_mode, $conf) {
    $fields = $conf['fields'];

    $display = EntityViewDisplay::load("node.$content_type.$display_mode");
    if (!$display) {
      $display = EntityViewDisplay::create([
        'targetEntityType' => 'node',
        'bundle' => $content_type,
        'mode' => $display_mode,
        'status' => TRUE,
      ]);

      foreach($fields as $field => $field_c) {
        // $storage = FieldStorageConfig::loadByName('node', $field);
        // if (!$storage) {
        //   $storage = FieldStorageConfig::create([
        //     'field_name' => $field,
        //     'entity_type' => 'node',
        //     'type' => $field_c['type'],
        //     'cardinality' => $field_c['cardinality'] ?? 1,
        //     'settings' => $field_c['field_storage_config_settings'] ?? [],
        //   ]);
        //   $storage->save();
        // }
        // $field_cfg = FieldConfig::loadByName('node', $content_type, $field);
        // if (!$field_cfg) {
        //   $field_cfg = FieldConfig::create([
        //     'field_storage' => $storage,
        //     'bundle' => $content_type,
        //     'label' => $field,
        //     'required' => FALSE,
        //     'translatable' => !!$field_c['translatable'],
        //     'settings' => $field_c['field_config_settings'] ?? [],
        //   ])->save();
        // }
        if ($field_c['display'] ?? null) {
          $display->setComponent($field, [
            'type' => $field_c['display'],
            'label' => 'hidden',
            'weight' => 20,
            'region' => 'content',
            'settings' => $field_c['display_settings'] ?? [],
          ])->save();
        }

      } // end fields loop


    }

  }

}

