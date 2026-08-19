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

class ThisConfig {

  public static function put_menu_links($menu_name, $links) {
    foreach($links as $c) {
      $storage = \Drupal::entityTypeManager()->getStorage('menu_link_content');
      $existing = $storage->loadByProperties([
        'title' => $c['title'],
        'menu_name' => $menu_name,
      ]);
      if (!$existing) {
        $link = \Drupal\menu_link_content\Entity\MenuLinkContent::create([
          'title' => $c['title'],
          'link' => [
            'uri' => str_starts_with($c['url'], 'http') ? $c['url'] : "internal:{$c['url']}",
          ],
          'menu_name' => $menu_name,
        ]);
        $link->save();
      }
    }
    \Drupal::messenger()->addMessage('finished');
  }

  /*
   * configure text editor ckeditor5
  **/
  public static function configure_text_editor() {
    $formats = ['basic_html', 'full_html'];
    foreach ($formats as $format_id) {
      $editor = Editor::load($format_id);
      if (!$editor) { continue; }
      $settings = $editor->getSettings();

      if (!in_array('alignment', $settings['toolbar']['items'])) {
        $settings['toolbar']['items'][] = 'alignment';
      }
      $settings['plugins']['ckeditor5_alignment'] ??= [];

      $editor->setSettings($settings);
      $editor->save();
    }
  }

  /*
   *
  **/
  public static function setup_permissions() {
    $role = \Drupal\user\Entity\Role::load('anonymous');
    if ($role && !$role->hasPermission('access user profiles')) {
      $role->grantPermission('access user profiles')->save();
    }
  }

  /*
  **/
  public static function setup_tags() {
    $vocabulary = 'tags';
    $tag_names = [ 'premium', 'Slidesets.' ];

    foreach ($tag_names as $tag_name) {
      $existing = \Drupal::entityTypeManager()
        ->getStorage('taxonomy_term')
        ->loadByProperties([
          'vid' => $vocabulary,
          'name' => $tag_name,
        ]);
      if (!$existing) {
        $term = Term::create([
          'vid' => $vocabulary,
          'name' => $tag_name,
        ]);
        $term->save();
      }
    }
  }


  /*
   * view_modes: Card, ImageThumb, Section
  **/
  public static function setup_view_modes() {
    $config_factory = \Drupal::configFactory();

    $view_modes = [
      'card' => 'Card',
      'image_thumb' => 'Image Thumb',
      'section' => 'Section',
    ];
    foreach ($view_modes as $machine_name => $label) {
      $config_name = "core.entity_view_mode.node.$machine_name";

      $config = $config_factory->getEditable($config_name);
      if (!$config->isNew()) { continue; }

      $config->setData([
        'langcode' => 'en',
        'status' => TRUE,
        'dependencies' => [
          'module' => ['node'],
        ],
        'id' => "node.$machine_name",
        'label' => $label,
        'targetEntityType' => 'node',
        'cache' => TRUE,
      ])->save();
    }
  }

  public static function setup_views_config() {
    $user_id = 1;
    \Drupal::service('user.data')->set(
      'views_ui',
      $user_id,
      'show_advanced',
      TRUE
    );
  }

}

