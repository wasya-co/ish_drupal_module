<?php

namespace Drupal\ish_drupal_module\Models;

class YoutubeVideo {

  public static function title(string $id) {
    $config = \Drupal::config('ish_drupal_module.settings');
    $api_key = $config->get('google_api_youtube_key');

    $url = 'https://www.googleapis.com/youtube/v3/videos?part=snippet&id='.$id.'&key='.$api_key;
    $json = file_get_contents($url);
    $decoded_json = json_decode($json, false);
    // logg($decoded_json, '$decoded_json');
    $title = $decoded_json->items[0]->snippet->title;
    // logg($title, 'ze title');
    return $title;
  }

}
