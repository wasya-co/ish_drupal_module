<?php

namespace Drupal\ish_drupal_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;

/*
**/
class ConfigController extends ControllerBase {

  /*
  **/
  public function index() {
    return [
      '#theme' => 'item_list',
      '#items' => [
        Link::fromTextAndUrl(
          $this->t('Create Content'),
          Url::fromRoute('ish_drupal_module.create_content')
        ),
      ],
    ];
  }

}
