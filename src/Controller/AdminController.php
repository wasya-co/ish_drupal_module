<?php

namespace Drupal\ish_drupal_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;

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
        'create_content_url' => Url::fromRoute('ish_drupal_module.admin_create_content')->toString(),
        'recreate_issue_home_url' => Url::fromRoute('ish_drupal_module.admin_recreate_issue_home')->toString(),
        'recreate_layout_url' => Url::fromRoute('ish_drupal_module.admin_recreate_layout')->toString(),
        'run_task_url' => Url::fromRoute('ish_drupal_module.admin_run_task')->toString(),
      ],
      '#theme' => 'admin_home',
    ];
  }

  /*
  **/
  public function run_task() {
    LayoutConfig::clear();
    LayoutConfig::setup_marketing_site();

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
    $block = \Drupal\block_content\Entity\BlockContent::create([
      'type' => 'section_callout_parallax',
      'info' => 'home_section_1',
    ]);
    $block->save();

    $layout = $node->get('layout_builder__layout')->getValue();
    $section = new \Drupal\layout_builder\Section('layout_onecol');

    $section->appendComponent(
      \Drupal\layout_builder\SectionComponent::fromArray([
        'uuid' => \Drupal::service('uuid')->generate(),
        'region' => 'content',
        'configuration' => [
          'id' => 'section_callout_parallax:home_section_1',
          'label' => 'home_section_1',
          'label_display' => '0',
          'provider' => 'layout_builder',
          'view_mode' => 'full',
          'block_revision_id' => $block->getRevisionId(),
          'block_serialized' => NULL,
        ],
      ])
    );

    $layout[] = $section->toArray();

    $node->set('layout_builder__layout', $layout);
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
