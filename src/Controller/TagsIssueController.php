<?php

namespace Drupal\ish_drupal_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

use Drupal\ish_drupal_module\Helpers\Youtube;


/**
 *
**/
class TagsIssueController extends ControllerBase {

  /**
   *
  **/
  public function show($slug) {
    $vocab = 'tags_issue';

    // get the tag by slug, and its children
    $query = \Drupal::entityQuery('taxonomy_term')
      ->condition('vid', $vocab)
      ->condition('name', $slug)
      ->range(0, 1) // get only 1 term
      ->accessCheck(TRUE);

    $parent_tid = $query->execute();


    if (!empty($parent_tid)) {
      $parent_tag = \Drupal\taxonomy\Entity\Term::load(reset($parent_tid));
      $child_tids = \Drupal::entityQuery('taxonomy_term')
        ->condition('vid', $vocab)
        ->condition('parent.target_id', $parent_tid)
        ->accessCheck(TRUE)
        ->execute();
      $tags = \Drupal\taxonomy\Entity\Term::loadMultiple($child_tids);
    } else {
      \Drupal::logger('custom')->notice('No term found: @slug', ['@slug' => $slug]);
    }

    return [
      '#theme' => 'tags_issue_show',
      '#parent_tag' => $parent_tag,
      '#tags' => $tags,
      '#tmp' => $tags,
    ];
  }

}

