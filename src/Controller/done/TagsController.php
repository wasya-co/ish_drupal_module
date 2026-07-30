<?php

namespace Drupal\ish_drupal_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\HttpFoundation\Request;

/*
 * /tag/au
 *
 * From: https://www.drupal.org/docs/drupal-apis/routing-system/introductory-drupal-routes-and-controllers-example
 * From: https://www.drupaleasy.com/blogs/ultimike/2020/07/entityquery-examples-everybody
 * From: https://www.drupal.org/docs/8/api/database-api/dynamic-queries/conditions
 * From: https://www.drupal.org/docs/drupal-apis/entity-api/working-with-the-entity-api
 *
 * au = 53
 *
**/
class TagsController extends ControllerBase {

  public function index(Request $request) {
    $build = [
      '#theme' => 'tags_index',
    ];
    return $build;
  }

  /*
   * show
  **/
  public function show(Request $request) {
    $viewmode = 'default';
    $nodeStorage = \Drupal::entityTypeManager()->getStorage('node');
    $tagStorage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    $build = [
      '#theme' => 'tags_show',
    ];

    // $tagFeatureQ = $query = \Drupal::entityQuery('taxonomy_term')
    //   ->condition('field_slug', 'feature')
    //   ->accessCheck(true)->execute();
    // $tagFeatureId = reset($tagFeatureQ);
    $tagFeatureId = '205';
    // dpm($tagFeatureId, 'tagFeatureId');


    /* get the tag */
    $tagname = $request->attributes->get('tagname');
    $query = \Drupal::entityQuery('taxonomy_term')
      ->condition('field_slug', $tagname)
      ->accessCheck(true);
    $result = $query->execute();
    $tagId = reset($result);
    // dpm($tagId, 'tagId');
    if ($tagId) {
      $tag = $tagStorage->load($tagId);
      // dpm($tag, 'tag');
      $build['#tag'] = $tag;
    } else {
      $build['#theme'] = 'error_404';
      return $build;
    }


    /* get the feature nodes */
    // $query = \Drupal::database()->select('taxonomy_index', 'ti');
    // $query->fields('ti', array('nid'));
    // $query->condition('ti.tid', $tagId);
    // $query->leftJoin('taxonomy_index', 'ti2', 'ti.nid = ti2.nid');
    // $query->condition('ti2.tid', $tagFeatureId);
    // $query->distinct(TRUE);
    // $query->range(0, 9);
    // $result = $query->execute();
    // $featureNodeIds = $result->fetchCol();
    // // dpm($featureNodeIds, 'featureNodeIds');
    // if (!empty($featureNodeIds)) {
    //   $features = $nodeStorage->loadMultiple($featureNodeIds);
    //   $build['#features'] = $features;
    // }

    /* get newsfeed */
    // $query = \Drupal::database()->select('taxonomy_index', 'ti');
    // $query->fields('ti', array('nid'));
    // $query->condition('ti.tid', $tagId);
    // if ($featureNodeIds) {
    //   $query->condition('ti.nid', $featureNodeIds, 'NOT IN');
    // }
    // $query->distinct(TRUE);
    // $query->range(0, 9);
    // $result = $query->execute();
    // $newsitemNodeIds = $result->fetchCol();
    // dpm($newsitemNodeIds, 'newsitemNodeIds');
    // if (!empty($newsitemNodeIds)) {
    //   $newsitems = $nodeStorage->loadMultiple($newsitemNodeIds);
    //   $build['#newsitems'] = $newsitems;
    // }

    return $build;
  }
}
