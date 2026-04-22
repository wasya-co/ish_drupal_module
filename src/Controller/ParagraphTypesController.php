<?php

namespace Drupal\ish_drupal_module\Controller;

use Drupal\Component\Utility\Html;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;

class ParagraphTypesController extends ControllerBase {

  /**
   * Lists Paragraphs types at /admin/paragraphs (core table renderer).
   */
  public function index() {
    $storage = $this->entityTypeManager()->getStorage('paragraphs_type');
    $types = $storage->loadMultiple();

    $rows = [];

    /** @var \Drupal\paragraphs\Entity\ParagraphsType $type */
    foreach ($types as $type) {
      $icon_url = $type->getIconUrl();
      $icon_cell = $icon_url
        ? [
          'data' => [
            '#markup' => '<img class="bordered" src="' . Html::escape($icon_url) . '" alt="" loading="lazy" style="border: 1px solid #000;" />',
          ],
        ]
        : ['data' => ['#markup' => '—']];

      $rows[] = [
        $icon_cell,
        $type->id(),
        $type->label(),
        $type->getDescription() ?: '—',
        Link::fromTextAndUrl(
          $this->t('Edit'),
          Url::fromRoute('entity.paragraphs_type.edit_form', [
            'paragraphs_type' => $type->id(),
          ])
        ),
      ];
    }

    $build = [
      '#attached' => [
        'library' => [
          'ish_drupal_module/main',
        ],
      ],
      '#attributes' => [
        'class' => [
          'ish-paragraphs-list',
        ],
      ],
      '#type' => 'table',
      '#header' => [
        $this->t('Icon'),
        $this->t('Machine name'),
        $this->t('Label'),
        $this->t('Description'),
        $this->t('Operations'),
      ],
      '#rows' => $rows,
      '#empty' => $this->t('No paragraph types found.'),
    ];

    $build['#cache']['tags'] = $this->entityTypeManager()
      ->getDefinition('paragraphs_type')
      ->getListCacheTags();

    return $build;
  }

}
