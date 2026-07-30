<?php

namespace Drupal\ish_drupal_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\node\Entity\Node;

class WorklogsController extends ControllerBase {

  public function showRedirect($year) {
    $nids = \Drupal::entityQuery('node')
      ->condition('type', 'worklog')
      ->condition('field_datestr', $year)
      ->range(0, 1)
      ->accessCheck(TRUE)
      ->execute();

    if (!empty($nids)) {
      $nid = reset($nids);
      $node = Node::load($nid);
      return new RedirectResponse($node->toUrl()->toString());
    }
    return new RedirectResponse('/worklog');
  }

}
