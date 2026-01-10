<?php

namespace Drupal\ish_drupal_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\HttpFoundation\Request;


class ScrapeZerohedgeController extends ControllerBase {

  /**
   * all
  **/
  public function all(Request $request) {
    $contents = \Drupal::service('ish_drupal_module.zerohedge_scraper')->all();
    $build = [
      '#theme' => 'scrape_zerohedge_all',
      '#contents' => $contents,
      '#cache' => [
        'max-age' => 0, // no caching
      ],
    ];
    return $build;
  }

  /**
   * one
  **/
  public function one(Request $request) {
    // logg($request, 'request');

    $uid = 138; // content-donor
    // $user = User::load($uid);

    $zhPath = $request->get('zhPath');
    // logg($zhPath, 'zhPath');

    $contents = \Drupal::service('ish_drupal_module.zerohedge_scraper')->one($zhPath);
    // logg($contents, '$contents');

    $tags_issue_ids = [ 304 ] ; // 2025q2-1ne

    $node_manager = \Drupal::entityTypeManager()->getStorage('node');
    $new_item = $node_manager->create([
      'uid' => $uid,
      'body' => [
        'value' => $contents['html'],
        'format' => 'full_html',
      ],
      // 'field_tags_contrib' => $tags_contrib_ids,
      'field_tags_issue' => $tags_issue_ids,
      'status' => 1, // is published
      'title' => $contents['title'],
      'type' => 'article',
    ]);
    $new_item->save();
    \Drupal::messenger()->addMessage('Item From zerohedge has been saved.');

    $build = [
      '#theme' => 'scrape_zerohedge_one',
      '#contents' => 'nonez',
      '#zhPath' => $zhPath,
    ];
    return $build;
  }

}
