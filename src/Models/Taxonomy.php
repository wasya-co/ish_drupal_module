<?php

namespace Drupal\ish_drupal_module\Models;

use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\Entity\User;


/*
 *
**/
class Taxonomy {

  public static function findOrCreateByName($name, $vocabulary = 'tagscontrib') {
    $tids = \Drupal::entityQuery('taxonomy_term')
      ->condition('vid', $vocabulary)
      ->condition('name', $name)
      ->accessCheck(FALSE)
      ->execute();
    if (!empty($tids)) {
      return Term::load(reset($tids));
    }
    $term = Term::create([
      'vid' => $vocabulary,
      'name' => $name,
    ]);
    $term->save();
    return $term;
  }


  public static function findOrCreateBySlug($slug, $vocabulary = 'tagscontrib') {

    $tids = \Drupal::entityQuery('taxonomy_term')
      ->condition('vid', $vocabulary)
      ->condition('field_slug', $slug)
      ->accessCheck(FALSE)
      ->execute();

    if (!empty($tids)) {
      return Term::load(reset($tids));
    }

    $term = Term::create([
      'vid' => $vocabulary,
      'name' => $slug, // or generate a proper label
      'field_slug' => $slug,
    ]);
    $term->save();

    return $term;
  }
}
