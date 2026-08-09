<?php

namespace Drupal\ish_drupal_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;

use Drupal\ish_drupal_module\Config\LayoutConfig;

/*
**/
class AdminController extends ControllerBase {

  /*
   * admin_home
  **/
  public function index() {
    return [
      '#theme' => 'admin_home',
      '#create_content_url'  => Url::fromRoute('ish_drupal_module.admin_create_content')->toString(),
      '#recreate_layout_url' => Url::fromRoute('ish_drupal_module.admin_recreate_layout')->toString(),
      '#run_task_url' => Url::fromRoute('ish_drupal_module.admin_run_task')->toString(),
    ];
    // return [
    //   '#theme' => 'item_list',
    //   '#items' => [
    //     Link::fromTextAndUrl(
    //       $this->t('Create Content'),
    //       Url::fromRoute('ish_drupal_module.create_content')
    //     ),
    //   ],
    // ];
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
  }

}
