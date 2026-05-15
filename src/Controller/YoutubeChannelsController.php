<?php

namespace Drupal\ish_drupal_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Drupal\ish_drupal_module\Models\YoutubeVideo;

/*
 *
**/
class YoutubeChannelsController extends ControllerBase {


  /*
   * 2025-10-02 This is used!
  **/
  public function check(Request $request) {
    $config = \Drupal::config('ish_drupal_module.settings');
    $api_key = $config->get('google_api_youtube_key');

    $nid = $request->attributes->get('node');
    $youtube_channel = Node::load($nid);
    logg($youtube_channel, '$youtube_channel');

    $tags_issue_ids = $youtube_channel->get('field_tags_issue')->getValue();
    $tags_issue_ids = array_column($tags_issue_ids, 'target_id');
    logg($tags_issue_ids, '$tags_issue_ids');

    $channel_id      = $youtube_channel->get('field_channel_id')->value;
    $include_streams = (bool) $youtube_channel->get('field_include_streams')->value;
    logg($include_streams, '$include_streams');
    $n_videos = 10;
    $url = 'https://www.googleapis.com/youtube/v3/search?key='
      . $api_key
      . '&channelId='.$channel_id
      . '&part=snippet,id&order=date&maxResults='.$n_videos
      . '&videoDuration=long&type=video';
    logg($url, '$url');

    $json = file_get_contents($url);
    $decoded_json = json_decode($json, false);
    logg($decoded_json, '$decoded_json');

    $outs = [];

    foreach($decoded_json->items as $item) {
      logg($item, '$item');

      $youtube_id = $item->id->videoId;
      $youtube_title = YoutubeVideo::title($youtube_id);

      /*
       * Check if this was a livestream
      **/
      if (!$include_streams) {
        $video_url = 'https://www.googleapis.com/youtube/v3/videos'
          . '?key=' . $api_key
          . '&id=' . $youtube_id
          . '&part=liveStreamingDetails';
        $video_json = file_get_contents($video_url);
        $video_data = json_decode($video_json);
        logg($video_data, '$video_data');
        if (!empty($video_data->items[0]->liveStreamingDetails)) {
          continue;
        }
      }


      // $issue_uuid = '35'; // '4ac9695b-0854-4972-8528-1f52e21d2235'; // taxonomy_term/35 :: 2024q1-issue
      $node_manager  = \Drupal::entityTypeManager()->getStorage('node');
      $existing_page_youtube = $node_manager->loadByProperties([
        'type' => 'page_youtube',
        'field_youtube_id' => $youtube_id,
      ]);
      if (!$existing_page_youtube) {
        $outs[ $item->id->videoId ] = $youtube_title;

        $body = <<<AOL
          <div class="embed-responsive embed-responsive-16by9">
            <iframe class='embed-responsive-item' src="https://www.youtube.com/embed/$youtube_id" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write;
              encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin"
              allowfullscreen></iframe>
          </div>
        AOL;

        $new_item = $node_manager->create([
          'uid' => $youtube_channel->getOwnerId(),
          'body' => [
            'value' => $body,
            'format' => 'full_html',
          ],
          'field_youtube_id' => $youtube_id,
          // 'field_issue' => [ 'target_id' => $issue_uuid ],
          'field_tags_issue' => $tags_issue_ids,
          'status' => 1, // is published
          'title' => $youtube_title,
          'type' => 'page_youtube',
        ]);
        $new_item->save();
        logg($new_item, '$new_item');
        \Drupal::messenger()->addMessage('Item From Youtube has been saved.');
      }
    }

    logg($outs, '$outs');

    return [
      '#theme' => 'youtube_channels_check',
      '#decoded_json' => $json,
      '#outs' => $outs,
      '#abba' => 'given abba',
    ];
  }

  /*
  **/
  public function index(Request $request) {
    return [
      '#theme' => 'youtube_channels_index',
      // '#videos' => $outs,
    ];
  }

}

