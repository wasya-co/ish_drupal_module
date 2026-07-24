<?php

namespace Drupal\ish_drupal_module\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\Attribute\Block;

/**
 * @Block(
 *   id = "current_user_block",
 *   admin_label = "current_user_block",
 * )
**/
class CurrentUserBlock extends BlockBase {

  /**
   * {@inheritdoc}
  **/
  public function build() {
    $uid = \Drupal::currentUser()->id();
    $user = \Drupal\user\Entity\User::load($uid);
    $current_user_email = $user->getEmail();

    return [
      '#theme' => 'current_user_block',
      '#current_user_email' => $current_user_email,
      '#cache' => [
        'contexts' => ['user'],
      ],
    ];
  }

}
