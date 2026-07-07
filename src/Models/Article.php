<?php

namespace Drupal\ish_drupal_module\Models;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;

/*
 * Article
**/
class Article {

  /*
   * _TODO rename: findOrBuildBy
  **/
  public static function findOrCreateBy(string $field_name, string $field_value): Node {
    $one = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties([
          'type' => 'article',
          $field_name => $field_value,
      ]);
    $node = reset($one);

    if (!$node) {
      $node = \Drupal\node\Entity\Node::create([
        'type' => 'article',
        $field_name => $field_value,
      ]);

      // $node->save();
    }

    return $node;
  }

}
