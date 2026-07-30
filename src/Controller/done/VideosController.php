<?php

namespace Drupal\ish_drupal_module\Controller;

use \Drupal\Core\Controller\ControllerBase;
use \Drupal\node\NodeInterface;
use \Drupal\taxonomy\Entity\Term;
use \Symfony\Component\HttpFoundation\Request;


/**
 * Videos controller.
**/
class VideosController extends ControllerBase {

  /*
   * showlegacy()
  **/
  public function showlegacy( $id, $slug, Request $request ) {
    $manager  = \Drupal::entityTypeManager()->getStorage('node');
    $video_type = 'v1';
    $existing = $manager->load($id);
    $view_builder = \Drupal::entityTypeManager()->getViewBuilder('node');


    // logg($existing, 'ex');

    return [
      '#theme'  => 'videos_show2',
      '#content'   => $existing,
      '#title'  => $existing->getTitle(),
    ];
  }


}
