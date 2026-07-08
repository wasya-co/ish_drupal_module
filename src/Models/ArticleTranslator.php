<?php

namespace Drupal\ish_drupal_module\Models;

use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

/*
 *

curl -X POST "https://translate.wasyaco.com/translate" \
  -H "Content-Type: application/json" \
  -d '{
    "q": "this queue",
    "source": "auto",
    "target": "pt",
    "format": "text",
    "alternatives": 3,
    "api_key": ""
  }'

 *
**/
class ArticleTranslator {

  public static function runCron() {
    $max   = 3;
    $queue = \Drupal::queue('translate_queue');
    $user  = \Drupal\user\Entity\User::load( 138 ); // content-donor on piousbox_com

    $query = \Drupal::entityQuery('node')
      ->condition('type', 'article')
      ->condition('status', 1)
      ->condition('langcode', 'en')
      ->sort('created', 'DESC') // newest first
      ->accessCheck(FALSE);

    $no_translate = \Drupal::entityTypeManager()->getStorage('taxonomy_term')
      ->loadByProperties([
        'name' => 'no-translate',
      ]);
    $no_translate = reset($no_translate);
    if ($no_translate) {
      $query->condition('field_tags', $no_translate->id(), '<>');
    }

    $missing_es = [];
    $nids = $query->execute();
    if (!empty($nids)) {
      $nodes = Node::loadMultiple($nids);
      foreach ($nodes as $node) {
        if (!$node->hasTranslation('es')) {
          $missing_es[] = $node;
        }
        if (count($missing_es) >= $max) {
          break;
        }
      }
    }

    foreach ($missing_es as $item) {
      \Drupal\ish_drupal_module\Models\Article::translateTo($item->id(), 'es');
    }



  }

}
