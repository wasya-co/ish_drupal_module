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
  public function index($category = NULL) {
    $storage = $this->entityTypeManager()->getStorage('paragraphs_type');
    $types = $storage->loadMultiple();

    $rows = [];

    var_dump( $category );

    /** @var \Drupal\paragraphs\Entity\ParagraphsType $type */
    foreach ($types as $type) {

      if ($category) {
        $categories = $type->getThirdPartySetting('paragraphs_ee', 'paragraphs_categories', []);
        if (!isset($categories[$category])) {
          continue;
        }
      }


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

    $entity_type_manager = $this->entityTypeManager();
    $list_cache_tags = $entity_type_manager
      ->getDefinition('paragraphs_type')
      ->getListCacheTags();

    $build = [
      '#attached' => [
        'library' => [
          'ish_drupal_module/main',
        ],
      ],
      '#cache' => [
        'tags' => $list_cache_tags,
      ],
    ];

    if ($entity_type_manager->hasDefinition('paragraphs_category')) {
      $list_cache_tags = array_merge(
        $list_cache_tags,
        $entity_type_manager->getDefinition('paragraphs_category')->getListCacheTags()
      );
      $build['#cache']['tags'] = array_values(array_unique($list_cache_tags));

      $categories = $entity_type_manager
        ->getStorage('paragraphs_category')
        ->loadMultiple();
      if ($categories !== []) {
        usort($categories, static function ($a, $b) {
          return strnatcasecmp($a->label(), $b->label());
        });

        $items = [];
        foreach ($categories as $category) {
          $items[] = Link::fromTextAndUrl(
            $category->label(),
            Url::fromUserInput('/admin/paragraphs/' . rawurlencode($category->id()))
          )->toRenderable();
        }

        $build['categories'] = [
          '#theme' => 'item_list',
          '#list_type' => 'ul',
          '#items' => $items,
          '#attributes' => [
            'class' => ['ish-paragraphs-categories'],
          ],
        ];
      }
    }

    $build['table'] = [
      '#type' => 'table',
      '#attributes' => [
        'class' => [
          'ish-paragraphs-list',
        ],
      ],
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

    return $build;
  }

}
