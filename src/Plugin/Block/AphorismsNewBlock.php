<?php

namespace Drupal\ish_drupal_module\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\ish_drupal_module\Form\AphorismsForm;

/*
 * @Block(
 *   id = "aphorisms_new_block",
 *   admin_label = "aphorisms new block",
 *   category = "aphorisms"
 * )
**/
class AphorismsNewBlock extends BlockBase {

  public function build() {
    $form = \Drupal::formBuilder()->getForm('Drupal\ish_drupal_module\Form\AphorismsForm');
    return $form;

    //   or use this method
    //    return [
    //      '#theme' => 'mytheme',
    //      "#form" => $form,
    //      "#data" => $data,
    //    ];
    //
    //    in mytheme.html.twig use {{ form }} and {{ data }}

  }

  /**
   * {@inheritdoc}
  **/
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'some_arr' => [],
    ];
  }

}
