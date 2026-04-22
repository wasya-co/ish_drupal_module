<?php

namespace Drupal\ish_drupal_module\Helpers;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;


/*
 * https://stackoverflow.com/questions/18953499/youtube-api-to-fetch-all-videos-on-a-channel
 * https://www.googleapis.com/youtube/v3/search?key={your_key_here}&channelId={channel_id_here}&part=snippet,id&order=date&maxResults=5
**/
class MainHelper {

  public static function computeRelatedArticles(&$build) {
    if ($build['node']->getType() === 'article') {
      // logg($build, 'computeRelatedArticles - build');

      $node = $build['node'];
      // logg($node, 'node');

      if ($node->hasField('field_tags_contrib')) {
        $tag_ids = [];
        foreach ($node->get('field_tags_contrib')->referencedEntities() as $term) {
          $tag_ids[] = $term->id();
        }
        // logg( $tag_ids, 'tag_ids' );

        if (!empty($tag_ids)) {
          $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
          $query = \Drupal::entityQuery('node')
            ->condition('type', 'article')
            ->condition('status', 1) // only published
            ->condition('nid', $node->id(), '<>') // exclude current node
            ->condition('field_tags_contrib.target_id', $tag_ids, 'IN')
            ->condition('langcode', $langcode)
            ->sort('created', 'DESC')
            ->accessCheck(TRUE)
            ->range(0, 3);

          $nids = $query->execute();

          $related_articles = Node::loadMultiple($nids);
          // logg($related_articles, '$related_articles');
          $build['related_articles'] = $related_articles;
        }
      }
    }
  }
}

