<?php

namespace Drupal\ish_drupal_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

use Drupal\ish_drupal_module\Models\Taxonomy;

class ScrapeZerohedgeController extends ControllerBase {

  /**
   * all
  **/
  public function all(Request $request) {
    $contents = \Drupal::service('ish_drupal_module.zerohedge_scraper')->all();
    // logg($contents, '$contents');

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

    preg_match('#^/([^/]+)/#', $zhPath, $matches);
    $tag_slug = $matches[1];
    $tag = (new Taxonomy())->findOrCreateBySlug($tag_slug);

    $contents = \Drupal::service('ish_drupal_module.zerohedge_scraper')->one($zhPath);
    // logg($contents, '$contents');

    $tags_issue_ids = [ 304 ] ; // 2025q2-1ne

    $imageUrl = \Drupal::service('ish_drupal_module.pexels')->imageUrlFromPrompt($contents['title']);
    $client = \Drupal::httpClient();
    $response = $client->get($imageUrl);
    $imageData = $response->getBody()->getContents();
    $file = file_save_data( $imageData, 'public://'. time() . '.jpg', FILE_EXISTS_RENAME );

    $node_manager = \Drupal::entityTypeManager()->getStorage('node');
    $new_item = $node_manager->create([
      'uid' => $uid,
      'body' => [
        'format' => 'full_html',
        'summary' => $contents['summary'],
        'value' => $contents['html'],
      ],
      'field_tags_contrib' => [ $tag->id() ],
      'field_tags_issue' => $tags_issue_ids,
      'field_image_thumb' => [
        'target_id' => $file->id(),
        'alt' => $contents['title'],
        'title' => $contents['title'],
      ],
      'status' => 1, // is published
      'title' => $contents['title'],
      'type' => 'article',
    ]);
    $new_item->save();
    \Drupal::messenger()->addMessage('Item From zerohedge has been saved.');

    $url = Url::fromRoute('entity.node.canonical', ['node' => $new_item->id()]);
    return new RedirectResponse($url->toString());

    // logg($contents, '$contents');

    // $build = [
    //   '#theme' => 'scrape_zerohedge_one',
    //   '#contents' => $contents,
    //   '#zhPath' => $zhPath,
    // ];
    // return $build;
  }

}
