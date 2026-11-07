<?php
namespace Drupal\ish_drupal_module\Config;


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

  /*
   * 2026-07-30 continue
  **/
  public static function create_view($view_id, $display_name, $config) {
    $view = View::load($view_id);
    // if ($view) { $view->delete(); } // _TODO: remove

    if (!$view) {
      $path = \Drupal::service('extension.list.module')
        ->getPath('ish_drupal_module') . '/config/install/views.view.master.yml';
      $defaults = Yaml::decode(file_get_contents($path));
      $defaults['id'] = $view_id;
      $defaults['label'] = $view_id;

      $view = View::create($defaults);
      $view->save();
      $view = View::load($view_id);
    }

    $display = $view->get('display');

    // if ($config['default']['display_options']['filters']) {
    //   $display['default']['display_options']['filters'] = $config['default']['display_options']['filters'];
    // }
    // $display[$display_name] = [
    //   'id' => $display_name,
    //   'display_options' => $config['display_options']??[],
    //   'display_plugin' => $config['display_plugin'],
    //   'display_title' => $config['display_title'],
    //   'defaults' => $config['defaults']??[],
    // ];
    // $view->set('display', $display);

    $view->addDisplay($config['display_plugin'] ?? 'block', "title: $display_name", $display_name);
    $view->save();
  }

  /*
   * 2026-11-05 continue
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






/* trash, use yml */
/* $view_config = [
  'default' => [
    'display_options' => [
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
      ],
    ],
  ],
  'defaults' => [
    'row' => false,
    'style' => false,
  ],
  'display_options' => [
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
  ],
  'display_plugin' => 'block', // block or page
  'display_title' => 'block_frontpage',
]; */
// ViewsConfig::create_view('directory', 'block_frontpage', $view_config);


