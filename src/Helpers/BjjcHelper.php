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

class BjjcHelper {

  public static function computeRelatedVideos(&$build) {
    if ('v2' == $build['uid']['#bundle'] && 'full' == $build['#view_mode']) {
      $this_node = Node::load($build['#node']->nid[0]->value);
      $tags = [];
      foreach( $this_node->field_tags2 as $k => $v ) {
        array_push( $tags, $v->target_id );
      }
      $nids = \Drupal::entityQuery('node')->condition('field_tags2', $tags, 'in')
        ->accessCheck(TRUE)
        ->execute();
      if (($key = array_search($build['#node']->nid[0]->value, $nids)) !== false) { ## https://stackoverflow.com/questions/7225070/php-array-delete-by-value-not-key
        unset($nids[$key]);
      }
      $relatedNodes = \Drupal\node\Entity\Node::loadMultiple($nids);
      $relatedNodesView = \Drupal::entityTypeManager()
        ->getViewBuilder('node')
        ->viewMultiple($relatedNodes, 'card');
      $build['relatedNodes'] = $relatedNodesView;
    }
  }

}
