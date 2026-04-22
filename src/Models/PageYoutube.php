<?php

namespace Drupal\ish_drupal_module\Models;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;


class PageYoutube {

  public static function run_cron() {

    $youtube_queue = \Drupal::queue('youtube_queue');

    // $user = \Drupal\user\Entity\User::load( 138 ); // content-donor on piousbox_com

    // Find channels attached to each issue *block*, and put page_youtube's into each block. _vp_ 2025-12-17
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'youtube_channel')
      ->exists('field_tags_issue')
      ->condition('field_tags_issue', NULL, 'IS NOT NULL')
      ->accessCheck(FALSE)
      ->condition('status', 1); // is published
    $nids = $query->execute();

    foreach ($nids as $nid) {
      $youtube_queue->createItem([
        'nid' => $nid,
      ]);
    }

  }

}
