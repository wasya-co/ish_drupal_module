<?php

namespace Drupal\ish_drupal_module\Controllers;

use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

use Drupal\ish_drupal_module\Models\YoutubeChannel;

/*
 * a rails-style controller
 *
 * https://stackoverflow.com/questions/18953499/youtube-api-to-fetch-all-videos-on-a-channel
 * https://www.googleapis.com/youtube/v3/search?key={your_key_here}&channelId={channel_id_here}&part=snippet,id&order=date&maxResults=5
**/
class YoutubeChannelCtrl {

  public $node_manager;

  public function __construct() {
    $this->node_manager  = \Drupal::entityTypeManager()->getStorage('node');
  }

  /*
   * _TODO: NOT USED.
  **/
  public function check() {
    $user = \Drupal\user\Entity\User::load( 138 ); // content-donor
    $config = \Drupal::config('ish_drupal_module.settings');
    $api_key = $config->get('google_api_youtube_key');

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

      // $title = $decoded_json->items[0]->snippet->title;
      // logg($title, 'ze title');
    }

    // logg($outs, '$outs');
    return $outs;
  }

  /* public function getPagesYoutube(&$build) {
    if ('youtube_channel' == $build['uid']['#bundle'] && 'full' == $build['#view_mode']) {
      // logg($build, 'getPagesYoutube');
    }
  } */

  public static function show(&$vars) {
    if ('youtube_channel' == $vars['node']->getType() && 'full' == $vars['view_mode']) {
      // logg($vars, 'youtube_channel#show');
      $node = $vars['node'];
      $vars['channel_id'] = $node->get('field_channel_id')->value;

      // $rendered_form = \Drupal::formBuilder()->getForm('Drupal\ish_drupal_module\Form\YoutubeChannelsCheckButton', $vars['node']->id() );
      // $vars['youtube_channels_check_button'] = $rendered_form;
      //
      // $url = Url::fromRoute('ish_drupal_module.youtube_channels_check');
      // $link = Link::fromTextAndUrl('Go to Example', $url)->toString();

      // logg($node->get('field_channel_id')->value, 'channel id');

      $query = \Drupal::entityQuery('node')
        ->condition('type', 'page_youtube')
        ->condition('field_channel_id', $node->get('field_channel_id')->value )
        ->accessCheck(TRUE);
      $nids = $query->execute();
      $pages_youtube = Node::loadMultiple($nids);
      // logg($pages_youtube, '$pages_youtube');
      $view_builder = \Drupal::entityTypeManager()->getViewBuilder('node');
      $render_pages_youtube = [];
      foreach ($pages_youtube as $page_youtube) {
        $render_pages_youtube[] = $view_builder->view($page_youtube, 'teaser');
      }
      $vars['pages_youtube'] = $render_pages_youtube;
    }
  }

}
