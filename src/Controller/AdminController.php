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
          'title' => 'Replace content yaml',
          'url' => Url::fromRoute('ish_drupal_module.admin_put_content')->toString(),
        ],
        [
          'confirm' => true,
          'title' => 'Recreate layout',
          'url' => Url::fromRoute('ish_drupal_module.admin_recreate_layout')->toString(),
        ],
        [
          'title' => 'Settings',
          'url' => Url::fromRoute('ish_drupal_module.admin_settings')->toString(),
        ],
      ],
      '#theme' => 'admin_home',
    ];
  }


  /*
  **/
  public function recreate_layout() {
    LayoutConfig::clear();
    LayoutConfig::setup_marketing_site();

    $this->messenger()->addStatus($this->t('Layout recreated.'));
    return $this->redirect('ish_drupal_module.admin_home');
  }

}


