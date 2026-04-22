<?php

namespace Drupal\ish_drupal_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

use Drupal\ish_drupal_module\Helpers\Youtube;


/**
 *
**/
class TmpController extends ControllerBase {

  /**
   * ish_drupal_module_cron() {
   *
   * get youtube channels witih non-empty field_tags_issue, and
   * create a page_youtube for each video, with appropriate tags_issue (pl)
  **/
  public function tmp(Request $request) {
    $user = \Drupal\user\Entity\User::load( 138 ); // content-donor
    $config = \Drupal::config('ish_drupal_module.settings');
    $api_key = $config->get('google_api_youtube_key');
    $youtube_channel = 'youtube_channel';

    $query = \Drupal::entityQuery('node')
      ->condition('type', $youtube_channel)
      ->exists('field_tags_issue')
      ->condition('field_tags_issue', NULL, 'IS NOT NULL')
      ->accessCheck(TRUE)
      ->condition('status', 1); // is published
    $nids = $query->execute();
    $nodes = Node::loadMultiple($nids);

    foreach ($nodes as $youtube_channel) {
      logg($youtube_channel, 'one youtube channel');

      $tags_issue_ids = $youtube_channel->get('field_tags_issue')->getValue();
      $tags_issue_ids = array_column($tags_issue_ids, 'target_id');
      logg($tags_issue_ids, '$tags_issue_ids');

      $channel_id     = $youtube_channel->get('field_channel_id'    )->value;
      // logg($channel_id, 'channel_id');

      $last_polled_at = $youtube_channel->get('field_last_polled_at')->value;
      if (!$last_polled_at || strtotime($last_polled_at) < time() - 86400) {

        $n_videos = 4;
        $url = 'https://www.googleapis.com/youtube/v3/search?key='.$api_key.'&channelId='.$channel_id.'&part=snippet,id&order=date&maxResults='.$n_videos.'&videoDuration=long&type=video';
        // logg($url, '$url');

        $json = file_get_contents($url);
        $decoded_json = json_decode($json, false);
        // logg($decoded_json, '$decoded_json');

        foreach($decoded_json->items as $item) {
          $youtube_id = $item->id->videoId;
          $youtube_title = Youtube::youtube_title($youtube_id);
          $page_youtube = 'page_youtube';

          $outs = [];

          // $issue_uuid = '35'; // '4ac9695b-0854-4972-8528-1f52e21d2235'; // taxonomy_term/35 :: 2024q1-issue
          $node_manager  = \Drupal::entityTypeManager()->getStorage('node');
          $existing_page_youtube = $node_manager->loadByProperties([
            'type' => $page_youtube,
            'field_youtube_id' => $youtube_id,
          ]);
          if (!$existing_page_youtube) {
            $outs[ $item->id->videoId ] = $youtube_title;

            $body = <<<AOL
              <iframe width="560" height="315" src="https://www.youtube.com/embed/$youtube_id" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write;
                encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin"
                allowfullscreen></iframe>
            AOL;

            $new_item = $node_manager->create([
              'author' => $user,
              'body' => [
                'value' => $body,
                'format' => 'full_html',
              ],
              'field_youtube_id' => $youtube_id,
              // 'field_issue' => [ 'target_id' => $issue_uuid ],
              'field_tags_issue' => $tags_issue_ids,
              'status' => 1, // is published
              'title' => $youtube_title,
              'type' => $page_youtube,
            ]);
            $new_item->save();
            \Drupal::messenger()->addMessage('Item From Youtube has been saved.');
          }
        }
        $node->set('field_last_polled_at', date('c') );
        $node->save();
      }
    }

    return [
      '#theme' => 'tmp',
      '#tmp' => $nodes,
    ];
  }

}

