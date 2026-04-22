<?php

namespace Drupal\ish_drupal_module\Controller;

use Drupal\node\NodeInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;


/**
 * Locations controller.
**/
class LocationsController extends ControllerBase {

  /**
   * follow()
   *
  **/
  public function follow($id, Request $request) {
    $cuId = \Drupal::currentUser()->id();

    $location = \Drupal::entityTypeManager()->getStorage('node')->load($id);
    $location->field_users[] = [ 'target_id' => $cuId ];
    $location->save();

    return [
      '#markup' => "<h1>Locations#follow()</h1>",
    ];
  }

  /**
   * index()
  **/
  public function index() {
    $cuId = \Drupal::currentUser()->id();

    $my_locations = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties([
      'type' => 'location',
      'field_users' => $cuId,
    ]);
    // logg($my_locations, 'my_locations');

    return [
      '#theme' => 'ish_locations_index',
    ];
  }


  /*
   * /my/feed
   *
  **/
  public function myFeed() {
    $cuId = \Drupal::currentUser()->id();

    // $user = \Drupal::currentUser();
    // $user = User::load(\Drupal::currentUser()->id());

    // test-1
    $bid = 35;
    $block = \Drupal\block_content\Entity\BlockContent::load($bid);
    $render = \Drupal::entityTypeManager()->getViewBuilder('block_content')->view($block);

    // @TODO: get locations that I subscribed to

    $nodes = \Drupal::entityTypeManager()->getStorage('node');
    $nodes_query  = \Drupal::entityQuery('node')->accessCheck(TRUE);

    $my_locations = $nodes_query->condition('type', 'location');
    $my_locations->condition('field_users', $cuId);

    // logg($my_locations, 'my_locations');

    return [
      '#theme' => 'my_feed',
      '#test_var_1' => $render,
      // 'content' => [
      // ],
      // 'system_main' => $render,
      // 'sidebar_first' => $render,
      // '#markup' => "<h1>myFeed()</h1>",
    ];
  }


  /**
   * show()
  **/
  public function show( $slug, Request $request ) {

    $location = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties([
      'type' => 'location',
      'field_slug' => $slug,
    ]);
    $location = $location[ array_keys($location)[0] ];

    // var_dump($location->title->value);

    return [
      '#theme'    => 'ish_locations_show',
      '#location' => $location,
      '#request'  => $request,
    ];
  }

}
