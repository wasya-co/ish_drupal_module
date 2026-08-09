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
   * create_issue_home
  **/
  public function create_issue_home() {
    ContentTypesConfig::setup_issue();   /* issue is same as advanced_page, but without image_hero. */


    $alias_storage = \Drupal::entityTypeManager()->getStorage('path_alias');
    $aliases = $alias_storage->loadByProperties([ 'alias' => '/home' ]);
    if ($aliases) {
      $this->messenger()->addWarning($this->t('Home alias already exists.'));
      return $this->redirect('ish_drupal_module.admin_home');
    }

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

    return $this->redirect('ish_drupal_module.admin_home');
  }

  /*
   * admin_home
  **/
  public function index() {
    return [
      '#theme' => 'admin_home',
      '#secondary_menu_links' => [
        'create_content_url' => Url::fromRoute('ish_drupal_module.admin_create_content')->toString(),
        'create_issue_home_url' => Url::fromRoute('ish_drupal_module.admin_create_issue_home')->toString(),
        'recreate_layout_url' => Url::fromRoute('ish_drupal_module.admin_recreate_layout')->toString(),
        'run_task_url' => Url::fromRoute('ish_drupal_module.admin_run_task')->toString(),
      ],
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
  **/
  public function recreate_layout() {
    LayoutConfig::clear();
    LayoutConfig::setup_marketing_site();
    BlocksConfig::hours_of_operation();

    $this->messenger()->addStatus($this->t('Layout recreated.'));

    return $this->redirect('ish_drupal_module.admin_home');
  }

}
