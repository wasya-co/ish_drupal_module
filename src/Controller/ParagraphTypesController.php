<?php

namespace Drupal\ish_drupal_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\paragraphs\Entity\ParagraphsType;

class ParagraphTypesController extends ControllerBase {

  public function index() {
    $storage = $this->entityTypeManager()->getStorage('paragraphs_type');
    $types = $storage->loadMultiple();

    $rows = [];

    /** @var \Drupal\paragraphs\Entity\ParagraphsType $type */
    foreach ($types as $type) {
      $rows[] = [
        'icon' => $type->get('icon_default'),
        'id' => $type->id(),
        'label' => $type->label(),
      ];
    }

    return [
      '#theme' => 'paragraphs_list',
      '#items' => $rows,
      // '#attached' => [
      //   'library' => [
      //     'paragraph_type_list/icons',
      //   ],
      // ],
    ];

    // return [
    //   '#type' => 'table',
    //   '#header' => ['Icon', 'Machine name', 'Label', 'more' ],
    //   '#rows' => $rows,
    //   '#empty' => $this->t('No paragraph types found.'),
    //   '#attached' => [
    //     'library' => [
    //       'paragraph_type_list/icons',
    //     ],
    //   ],
    // ];

  }
}