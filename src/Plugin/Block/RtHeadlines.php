<?php

namespace Drupal\ish_drupal_module\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\ish_drupal_module\Service\ContentBuilder;

/**
 * @Block(
 *   id = "rt_headlines",
 *   admin_label = "rt_headlines",
 * )
**/
class RtHeadlines extends BlockBase {

  public function build() {
    $contents = \Drupal::service('ish_drupal_module.rt_scraper')->all();
    // logg($contents, '$contents');
    $build = [
      '#theme' => 'scrape_rt_all',
      '#contents' => $contents,
      '#cache' => [
        'max-age' => 0, // no caching
      ],
    ];
    return $build;
  }
}
