<?php

namespace Drupal\ish_drupal_module\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * @Block(
 *   id = "MenuUserAuthenticated",
 *   admin_label = "MenuUserAuthenticated",
 * )
**/
class MenuUserAuthenticated extends BlockBase {

  /**
   *  {@inheritdoc}
  **/
  public function build() {
    return [
      '#theme' => 'menu_user_authenticated',
      '#testvar' => 'My Name',
    ];
  }

}


