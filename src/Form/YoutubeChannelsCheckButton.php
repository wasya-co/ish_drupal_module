<?php

namespace Drupal\ish_drupal_module\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;

class YoutubeChannelsCheckButton extends FormBase {
  protected $node;

  // public function __construct(NodeInterface $node) {
  //   $this->node = $node;
  // }

  public function getFormId() {
      return 'youtube_channels_check_button';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $nid = NULL) {
    // $nid = $this->node ? $this->node->id() : 0;
    $form['nid'] = [
      '#type' => 'hidden',
      '#value' => $nid,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Check'),
    ];
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $nid = $form_state->getValue('nid');
    // logg($nid, 'form submitted');
    $form_state->setRedirect('ish_drupal_module.youtube_channels_check', ['node' => $nid]);
  }
}
