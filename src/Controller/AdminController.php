<?php

namespace Drupal\ish_drupal_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;

use Drupal\ish_drupal_module\Config\BlocksConfig;
use Drupal\ish_drupal_module\Config\ContentTypesConfig;
use Drupal\ish_drupal_module\Config\LayoutConfig;

/*
**/
class AdminController extends ControllerBase {


  /*
   * admin_home
  **/
  public function index() {
    return [
      '#secondary_menu_links' => [
        [
          'confirm' => false,
          'title' => 'Replace content yaml',
          'url' => Url::fromRoute('ish_drupal_module.admin_put_content')->toString(),
        ],
        [
          'confirm' => false,
          'title' => 'Add content yaml',
          'url' => Url::fromRoute('ish_drupal_module.admin_post_content')->toString(),
        ],
        [
          'title' => 'Replace issue home',
          'url' => Url::fromRoute('ish_drupal_module.admin_recreate_issue_home')->toString(),
        ],
        [
          'title' => 'Recreate layout',
          'url' => Url::fromRoute('ish_drupal_module.admin_recreate_layout')->toString(),
        ],
      ],
      '#theme' => 'admin_home',
    ];
  }

  /*
  **/
  public function run_task() {
    // LayoutConfig::clear();
    // LayoutConfig::setup_marketing_site();
    $this->messenger()->addStatus($this->t('Run Task complete.'));
    return $this->redirect('ish_drupal_module.admin_home');
  }

  /*
   * create_issue_home
  **/
  public function recreate_issue_home() {
    ContentTypesConfig::setup_issue();   /* issue is same as advanced_page, but without image_hero. */

    $alias_storage = \Drupal::entityTypeManager()->getStorage('path_alias');
    $aliases = $alias_storage->loadByProperties([ 'alias' => '/home' ]);
    /* this deletes, re-creates home */
    if ($aliases) {
      foreach ($aliases as $alias) {
        $path = $alias->getPath();
        if (preg_match('#^/node/(\d+)$#', $path, $matches)) {
          $existing_node = \Drupal\node\Entity\Node::load($matches[1]);
          if ($existing_node) { $existing_node->delete(); }
        }
        $alias->delete();
      }
    }
    /* this skips and exits if home already exists */
    // if ($aliases) {
    //   $this->messenger()->addWarning($this->t('Home alias already exists.'));
    //   return $this->redirect('ish_drupal_module.admin_home');
    // }


    $node = \Drupal\node\Entity\Node::create([
      'type' => 'issue',
      'title' => 'Home',
    ]);
    $node->save();

    \Drupal\path_alias\Entity\PathAlias::create([
      'path' => '/node/' . $node->id(),
      'alias' => '/home',
      'langcode' => 'en',
    ])->save();

    \Drupal::configFactory()
      ->getEditable('system.site')
      ->set('page.front', '/node/' . $node->id())
      ->save();

    $this->messenger()->addStatus($this->t('Issue home created.'));

    $this->recreate_issue_home_sections($node);

    return $this->redirect('ish_drupal_module.admin_home');
  }

  public function recreate_issue_home_sections($node) {
    BlocksConfig::setup_callout_parallax();

    $storage = \Drupal::entityTypeManager()->getStorage('block_content');
    $existing = $storage->loadByProperties([
      'type' => 'section_callout_parallax',
      'info' => 'home_section_1',
    ]);
    foreach ($existing as $old_block) {
      $old_block->delete();
    }

    $block = \Drupal\block_content\Entity\BlockContent::create([
      'type' => 'section_callout_parallax',
      'info' => 'home_section_1',
      'reusable' => TRUE,
    ]);
    $block->save();

    $section = new \Drupal\layout_builder\Section('layout_onecol_any');
    $component = new \Drupal\layout_builder\SectionComponent(
      \Drupal::service('uuid')->generate(),
      'main',
      [
        'id' => 'block_content:' . $block->uuid(),
        'label' => 'home_section_1',
        'label_display' => FALSE,
        'provider' => 'block_content',
      ]
    );
    $section->appendComponent($component);

    $layout_builder = $node->get('layout_builder__layout');
    $sections = $layout_builder->getSections();
    $sections[] = $section;
    $layout_builder->setValue($sections);
    $node->save();
  }

  /*
  **/
  public function recreate_layout() {
    LayoutConfig::clear();
    LayoutConfig::setup_marketing_site();
    BlocksConfig::hours_of_operation();

    $this->messenger()->addStatus($this->t('Layout recreated.'));

    return $this->redirect('ish_drupal_module.admin_home');
  }

}
