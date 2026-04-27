<?php

namespace Drupal\ish_drupal_module\Layout;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Layout\LayoutDefault;
use Drupal\Core\Plugin\PluginFormInterface;

class CollapseExpandLayout extends LayoutDefault implements PluginFormInterface {


  public function build(array $regions) {
    $build = parent::build($regions);
    $build['#attached']['library'][] = 'ish_drupal_module/collapse_expand';

    // Attach configuration (already available in Twig)
    $build['#settings'] = $this->getConfiguration();

    // Attach a stable unique ID (section instance ID if available)
    if (!empty($this->configuration['uuid'])) {
      $build['#section_uuid'] = $this->configuration['uuid'];
    }
    else {
      // fallback: generate deterministic runtime ID
      $build['#section_uuid'] = $this->getPluginId() . '-' . spl_object_hash($this);
    }

    return $build;
  }

  /**
   * {@inheritdoc}
  **/
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $configuration = $this->getConfiguration();
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $configuration['label'],
    ];
    $form['extra_classes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Extra classes'),
      '#default_value' => $configuration['extra_classes'],
    ];
    return $form;
  }


  /**
   * {@inheritdoc}
  **/
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'extra_classes' => 'fullwidth',
      'label' => 'collapse-expand',
    ];
  }

  /**
   * {@inheritdoc}
  **/
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['extra_classes'] = $form_state->getValue('extra_classes');
    $this->configuration['label']         = $form_state->getValue('label');
  }

  /**
   * {@inheritdoc}
  **/
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    // any additional form validation that is required
  }

}

