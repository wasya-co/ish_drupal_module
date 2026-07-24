<?php

namespace Drupal\ish_drupal_module\Models;

use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;


/*
 *
**/
class YoutubeChannel {

  public $channel_id;
  public $node;
  public $node_manager;

  public function __construct($nid) {
    $this->node_manager = \Drupal::entityTypeManager()->getStorage('node');
    $this->node = Node::load($nid);
  }

  /*
   * creates a matching user. get the slug, lower case
   * insert user $slug@youtube.com if doesn't exist
   *
  **/
  public function afterCreate() {
    $slug = $this->node->get('field_slug')->value;
    $slug = strtolower(str_replace('@', '', $slug));

    $uids = \Drupal::entityQuery('user')
      ->condition('field_channel_id', $this->channel_id)
      ->accessCheck(FALSE)
      ->execute();
    if (empty($uids)) {
      $length = 16;
      $randomString = substr(bin2hex(random_bytes($length)), 0, $length);

      $user = User::create([
        'mail' => $slug . '@youtube.com',
        'pass' => $randomString,
        'status' => 1, // 1 = active
        'field_channel_id' => $this->node->get('field_channel_id')->value,
      ]);
      $user->save();
      \Drupal::messenger()->addMessage('User '. $slug .' created');
    }
  }

  /*
   * https://stackoverflow.com/questions/18953499/youtube-api-to-fetch-all-videos-on-a-channel
   * https://www.googleapis.com/youtube/v3/search?key={your_key_here}&channelId={channel_id_here}&part=snippet,id&order=date&maxResults=5
  **/
  public static function check() {
    $user = \Drupal\user\Entity\User::load( 138 ); // content-donor
    $config = \Drupal::config('ish_drupal_module.settings');
    $api_key = $config->get('google_api_youtube_key');

    $n_videos = 3;
    $url = 'https://www.googleapis.com/youtube/v3/search?key='.$api_key.'&channelId='.$channel_id.'&part=snippet,id&order=date&maxResults='.$n_videos.'&videoDuration=long&type=video';
    // logg($url, '$url');
    $json = file_get_contents($url);
    $decoded_json = json_decode($json, false);
    // logg($decoded_json, '$decoded_json');

    foreach($decoded_json->items as $item) {
      // $item = $decoded_json->items[0];
      // logg($item, '$item');

      $youtube_id = $item->id->videoId;
      $youtube_title = Youtube::youtube_title($youtube_id);
      $page_youtube = 'page_youtube';

      $outs = [];

      $issue_uuid = '35'; // '4ac9695b-0854-4972-8528-1f52e21d2235'; // taxonomy_term/35 :: 2024q1-issue
      $node_manager  = \Drupal::entityTypeManager()->getStorage('node');
      $existing = $node_manager->loadByProperties([
        'type' => $page_youtube,
        'field_youtube_id' => $youtube_id,
      ]);
      if (!$existing) {
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
          'field_issue' => [ 'target_id' => $issue_uuid ],
          'status' => 1,
          'title' => $youtube_title,
          'type' => $page_youtube,
        ]);
        $new_item->save();
        \Drupal::messenger()->addMessage('Item From Youtube has been saved.');
      }
    }
    return $outs;
  }

}
