<?php
namespace Drupal\ish_drupal_module;

use Drupal\block\Entity\Block;
use Drupal\block_content\Entity\BlockContent;
use Drupal\block_content\Entity\BlockContentType;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

use Drupal\editor\Entity\Editor;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\node\NodeInterface;

use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\taxonomy\Entity\Term;

use Symfony\Component\HttpFoundation\RedirectResponse;

/*
**/
class ThisContent {

  /*
  **/
  public static function create_node_by($content_type, $config) {

    $nodes = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties([
        'type' => $content_type,
        'title' => $config['fields']['title'],
      ]);
    $node = reset($nodes);
    if (!$node) {
      $node = Node::create([
        'path' => [
          'alias' => $config['path'],
          'pathauto' => 0,
        ],
        'type' => $content_type,
        'title' => $config['fields']['title'],
        'status' => 1,
      ])->save();
    }

    $sections = $config['sections'];
    $outs = [];
    foreach($sections as $section) {

      $section = new Section( $section['type'], $section['config'] );
      foreach ($section['regions'] as $region => $region_c) {

        $extra = [
          'id' => "views_block:{$region_c['view_id']}",
          'label' => $region_c['label'],
          'label_display' => FALSE,
          'provider' => 'views',
        ];
        $component = new SectionComponent( Uuid::generate(), $region, $extra );
        $section->appendComponent($component);

      }
      $outs[] = $section;
    }
    $node->set('layout_builder__layout', $outs);
    $node->save();
  }

}


