<?php

namespace Drupal\ish_drupal_module\Models;

use Drupal\node\Entity\Node;
use Drupal\user\Entity\User as DrupalUser;

/*
 * User
**/
class User {

  public static function milesMathis(): \Drupal\user\Entity\User {

    $users = \Drupal::entityTypeManager()
      ->getStorage('user')
      ->loadByProperties([
        'name' => 'Miles Mathis',
      ]);

    $user = reset($users);

    if (!$user) {
      $user = \Drupal\user\Entity\User::create([
        'name' => 'Miles Mathis',
        'mail' => 'milesmathis@protonmail.com',
        'status' => 1,
      ]);

      $user->setPassword(\Drupal::service('password_generator')->generate());
      $user->save();
    }

    return $user;
  }
}
