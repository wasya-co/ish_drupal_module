<?php

namespace Drupal\ish_drupal_module\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Available at: /admin/config/ish_drupal_module/settings
**/
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
  **/
  public function getFormId() {
    return 'ish_drupal_module_settings_form';
  }

  /**
   * {@inheritdoc}
  **/
  protected function getEditableConfigNames() {
    return ['ish_drupal_module.settings'];
  }

  /**
   * {@inheritdoc}
  **/
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ish_drupal_module.settings');

    $form['load_libraries'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Load libraries'),
      '#options' => [
        'bootstrap4'      => 'bootstrap4',
        'bootstrap5'      => 'bootstrap5',
        'card_and_chip'   => 'card_and_chip',
        'collapse_expand' => 'collapse_expand',
        'elegant_icons'   => 'elegant_icons',
        'fancy_header'    => 'fancy_header',
        'swiper_cdn'      => 'swiper_cdn',
        'scrollreveal'    => 'scrollreveal',
      ],
      '#default_value' => $config->get('load_libraries') ?? [],
      '#description' => $this->t('Selected libraries will be loaded on all pages.'),
    ];

    $form['google_api_youtube_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('google_api_youtube_key'),
      '#default_value' => $config->get('google_api_youtube_key'),
      '#description' => $this->t('Zze google_api_youtube_key'),
    ];

    $form['libretranslate_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('libretranslate_api_key'),
      '#default_value' => $config->get('libretranslate_api_key'),
      '#description' => $this->t('Zze libretranslate_api_key'),
    ];

    $form['llm_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('llm_api_key'),
      '#default_value' => $config->get('llm_api_key'),
      '#description' => $this->t('Zze llm_api_key'),
    ];

    $form['pexels_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('pexels_api_key'),
      '#default_value' => $config->get('pexels_api_key'),
      '#description' => $this->t('Zze pexels_api_key'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
  **/
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $libraries = [];
    foreach ($form_state->getValue('load_libraries') as $library => $loaded) {
      $libraries[$library] = $loaded ? $library : 0;
    }

    $this->config('ish_drupal_module.settings')
      ->set('load_libraries',         $libraries )
      ->set('google_api_youtube_key', $form_state->getValue('google_api_youtube_key'))
      ->set('libretranslate_api_key', $form_state->getValue('libretranslate_api_key'))
      ->set('llm_api_key',            $form_state->getValue('llm_api_key'))
      ->set('pexels_api_key',         $form_state->getValue('pexels_api_key'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
