<?php

namespace Drupal\ish_drupal_module\Controller;

use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;


class Scene3dController extends ControllerBase {

  public function touch(NodeInterface $node) {
    // logg($node, '$node');

    return [
      '#theme' => 'scene3d_touch',
      '#node' => $node,
    ];
  }
}

