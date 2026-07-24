<?php

namespace Drupal\ish_drupal_module\Plugin\QueueWorker;


use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\Url;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Drupal\ish_drupal_module\Helpers\BjjcHelper;
use Drupal\ish_drupal_module\Helpers\MainHelper;
use Drupal\ish_drupal_module\Controllers\YoutubeChannelCtrl;
use Drupal\ish_drupal_module\Models\PageYoutube;
use Drupal\ish_drupal_module\Models\YoutubeVideo;

/**
 * Translate an article.
 *
 * @QueueWorker(
 *   id = "translate_queue",
 *   title = @Translation("Article Translate Queue Worker"),
 *   cron = {"time" = 420}
 * )
**/
class ArticleTranslatorQueueWorker extends QueueWorkerBase {

  /**
   * {@inheritdoc}
  **/
  public function processItem($data) {
    $config = \Drupal::config('ish_drupal_module.settings');
    $api_key = $config->get('google_api_youtube_key');

    $youtube_channel = Node::load($data['nid']);
    // logg($youtube_channel, 'one youtube channel from cron');

    $tags_issue_ids = $youtube_channel->get('field_tags_issue')->getValue();
    $tags_issue_ids = array_column($tags_issue_ids, 'target_id');
    // logg($tags_issue_ids, '$tags_issue_ids');

    $channel_id     = $youtube_channel->get('field_channel_id')->value;
    // logg($channel_id, 'channel_id');

    $last_polled_at = $youtube_channel->get('field_last_polled_at')->value;
    if (!$last_polled_at || strtotime($last_polled_at) < time() - 24*60*60) { // daily

      $n_videos = 10;
      $includeShorts = $youtube_channel->get('field_include_shorts')->value ? '' : '&videoDuration=long';
      $url = 'https://www.googleapis.com/youtube/v3/search?key='.$api_key.'&channelId='.$channel_id.'&part=snippet,id&order=date&maxResults='.$n_videos.$includeShorts.'&type=video';
      // logg($url, '$url');

      $json = file_get_contents($url);
      $decoded_json = json_decode($json, false);
      // logg($decoded_json, '$decoded_json');

      foreach($decoded_json->items as $item) {
        $youtube_id = $item->id->videoId;
        $youtube_title = YoutubeVideo::title($youtube_id);

        $outs = [];

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
          \Drupal::messenger()->addMessage('Item From Youtube has been saved.');
        }
      }
      $youtube_channel->set('field_last_polled_at', date('c') );
      $youtube_channel->save();
    }
  }

}
