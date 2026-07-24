<?php

namespace Drupal\ish_drupal_module\Layout;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Layout\LayoutDefault;
use Drupal\Core\Plugin\PluginFormInterface;

class TwocolAnywidthLayout extends LayoutDefault implements PluginFormInterface {

  /**
   * {@inheritdoc}
  **/
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'extra_classes' => 'fullwidth',
      'col1_classes' => 'col-sm-12 col-md-6',
      'col2_classes' => 'col-sm-12 col-md-6',
      'label' => '2col-any',
    ];
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
    $form['col1_classes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('col1 classes'),
      '#default_value' => $configuration['col1_classes'],
    ];
    $form['col2_classes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('col2 classes'),
      '#default_value' => $configuration['col2_classes'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
  **/
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    // any additional form validation that is required
  }

  /**
   * {@inheritdoc}
  **/
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['extra_classes'] = $form_state->getValue('extra_classes');
    $this->configuration['col1_classes'] = $form_state->getValue('col1_classes');
    $this->configuration['col2_classes'] = $form_state->getValue('col2_classes');
    $this->configuration['label']         = $form_state->getValue('label');
  }

}

