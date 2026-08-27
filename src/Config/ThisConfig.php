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

use Drupal\webform\Entity\Webform;

use Symfony\Component\HttpFoundation\RedirectResponse;

use Drupal\ish_drupal_module\Content\NodesContent;

/*
**/
class ThisConfig {

  /*
  **/
  public static function configure_contact_us() {
    $webform = Webform::load('contact');
    if (!$webform) {
      throw new \RuntimeException('Webform "contact" was not found.');
    }

    $handlers = $webform->getHandlers();

    if ($handlers->get('email_admin')) {
      return;
    }

    $handlers->addHandler([
      'id' => 'email',
      'handler_id' => 'email_admin',
      'label' => 'Email admin',
      'category' => 'Notification',
      'status' => TRUE,
      'weight' => 0,
      'settings' => [
        'states' => [
          'completed' => [
            'completed' => TRUE,
          ],
        ],
        'to_mail' => \Drupal::config('system.site')->get('mail'),
        'to_options' => [],
        'cc_mail' => '',
        'cc_options' => [],
        'bcc_mail' => '',
        'bcc_options' => [],
        'from_mail' => '',
        'from_options' => [],
        'from_name' => '',
        'reply_to' => '',
        'return_path' => '',
        'sender_mail' => '',
        'sender_name' => '',
        'subject' => '[webform_submission:created] :: [site:name] :: [webform:title] :: New webform submission',
        'body' => "A new submission has been received.\n\n[webform_submission:values]",
        'excluded_elements' => [],
        'included_elements' => [],
        'exclude_empty' => FALSE,
        'exclude_empty_checkbox' => FALSE,
        'exclude_unset' => FALSE,
        'exclude_markup' => FALSE,
        'exclude_options' => FALSE,
        'exclude_access' => FALSE,
        'exclude_sensitive' => FALSE,
        'attachments' => FALSE,
        'html' => FALSE,
        'twig' => FALSE,
      ],
    ]);

    $webform->save();
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
  **/
  public static function put_block_layout($regions) {
    $theme   = \Drupal::config('system.theme')->get('default');
    $storage = \Drupal::entityTypeManager()->getStorage('block');

    foreach ($regions as $r_name => $blocks) {
      foreach ($blocks as $c) {
        logg($c, "c in put_block_layout");

        [$type, $name] = explode(':', $c, 2);
        switch ($type) {
          case 'core':

            $plugin_id = $name;

            break;
          case 'menu':

            $plugin_id = "system_menu_block:$name";

            break;
          case 'basic':
          case 'advanced_block':

            $block     = BlocksConfig::create_block(['type' => $type, 'info' => $name ]);
            $plugin_id = "block_content:{$block->uuid()}";

            break;
          default:
            throw new \Exception('zz5 - Not implemented');
        }

        $existing = $storage->loadByProperties([
          'plugin' => $plugin_id,
          'theme'  => $theme,
          'region' => $r_name,
        ]);
        if (!$existing) {
          $block = $storage->create([
            'id'     => "{$r_name}_{$type}_{$name}",
            'plugin' => $plugin_id,
            'theme'  => $theme,
            'region' => $r_name,
            'status' => 1,
            'weight' => 0,
          ]);
          $block->save();
        }
      }
    }
  }

  /*
  **/
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
  **/
  public static function put_theme_config($config) {
    $theme   = \Drupal::config('system.theme')->get('default');

    if ($config['logo']) {
      $file = NodesContent::file_from_url($config['logo']);
      \Drupal::configFactory()
        ->getEditable($theme . '.settings')
        ->set('logo.use_default', FALSE)
        ->set('logo.path', $file->getFileUri())
        ->save();
    }

    \Drupal::messenger()->addMessage('finished put_theme_config');
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

