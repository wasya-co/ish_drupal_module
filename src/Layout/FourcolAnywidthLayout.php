<?php

namespace Drupal\ish_drupal_module\Layout;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Layout\LayoutDefault;
use Drupal\Core\Plugin\PluginFormInterface;

class FourcolAnywidthLayout extends LayoutDefault implements PluginFormInterface {

  /**
   * {@inheritdoc}
  **/
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'field_class_name'      => 'fixed-container',
      'field_col1_class_name' => 'col-sm-6 col-md-3',
      'field_col2_class_name' => 'col-sm-6 col-md-3',
      'field_col3_class_name' => 'col-sm-6 col-md-3',
      'field_col4_class_name' => 'col-sm-6 col-md-3',
      'label' => '4col-any',

      'field_custom_css' => '',
      'field_image_hero' => null,
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
    $form['field_class_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('field_class_name'),
      '#default_value' => $configuration['field_class_name'],
    ];
    $form['field_col1_class_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('field_col1_class_name'),
      '#default_value' => $configuration['field_col1_class_name'],
    ];
    $form['field_col2_class_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('field_col2_class_name'),
      '#default_value' => $configuration['field_col2_class_name'],
    ];
    $form['field_col3_class_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('field_col3_class_name'),
      '#default_value' => $configuration['field_col3_class_name'],
    ];
    $form['field_col4_class_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('field_col4_class_name'),
      '#default_value' => $configuration['field_col4_class_name'],
    ];
    $form['field_custom_css'] = [
      '#type' => 'textarea',
      '#title' => $this->t('field_custom_css'),
      '#default_value' => $configuration['field_custom_css'],
    ];
    $form['field_image_hero'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('field_image_hero'),
      '#upload_location' => 'public://field_image_hero/',
      '#default_value' => $configuration['field_image_hero'],
      '#upload_validators' => [
        'FileExtension' => [
          'extensions' => 'png jpg jpeg webp',
        ],
      ],
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
    $this->configuration['field_class_name']      = $form_state->getValue('field_class_name');
    $this->configuration['field_col1_class_name'] = $form_state->getValue('field_col1_class_name');
    $this->configuration['field_col2_class_name'] = $form_state->getValue('field_col2_class_name');
    $this->configuration['field_col3_class_name'] = $form_state->getValue('field_col3_class_name');
    $this->configuration['field_col4_class_name'] = $form_state->getValue('field_col4_class_name');
    $this->configuration['label']         = $form_state->getValue('label');

    $this->configuration['field_custom_css']      = $form_state->getValue('field_custom_css');

    $image = $form_state->getValue('field_image_hero');
    if (!empty($image[0])) {
      $file = File::load($image[0]);
      if ($file) {
        $file->setPermanent();
        $file->save();
      }
    }
    $this->configuration['field_image_hero'] = $image;

  }

}

