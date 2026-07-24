<?php

namespace Drupal\ish_drupal_module\Controller;

use Drupal\node\NodeInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Aphorisms controller.
**/
class AphorismsController extends ControllerBase {

  /**
   * index()
  **/
  public function index() {
    return [
      '#markup' => "<h1>Aphorisms Index !!!</h1>",
    ];
  }

  public function new() {
    return [
      '#markup' => "<h1>New Aphorism</h1>",
    ];
  }

  /**
   * show()
  **/
  /* public function show( $location_slug, Request $request ) {

    $location = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties([
      'type' => 'location',
      'field_slug' => $location_slug,
    ]);
    $location = $location[ array_keys($location)[0] ];

    // var_dump($location->title->value);

    return [
      '#theme'    => 'ish_locations_show',
      '#location' => $location,
      '#request'  => $request,
    ];
  } // */

}
