<?php

namespace Drupal\ish_drupal_module\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\ish_drupal_module\Service\ContentBuilder;

/**
 * @Block(
 *   id = "modernity_headlines",
 *   admin_label = "modernity_headlines",
 * )
**/
class ModernityHeadlines extends BlockBase {

  public function build() {
    $contents = \Drupal::service('ish_drupal_module.modernity_scraper')->all();
    // logg($contents, '$contents');
    $build = [
      '#theme' => 'scrape_modernity_all',
      '#contents' => $contents,
      '#cache' => [
        'max-age' => 0, // no caching
      ],
    ];
    return $build;
  }
}
