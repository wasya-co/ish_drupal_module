<?php

namespace Drupal\ish_drupal_module\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

use \Drupal\file\Entity\File;
use \Drupal\node\Entity\Node;
use \Drupal\node\Entity\User;

use Drupal\jwt\Transcoder\JwtTranscoder;


function logg($object, $label=null) {
  print($label . ":");
  echo "<br />";
  dump($object);
}

// function puts($object, $label=null) {
//   print($label . ":");
//   echo "<br />";
//   dump($object);
// }

/**
 * Literally copy pasted from AphorismsForm - should probably be in one place, not two.
 * _vp_ 2025-07-25
**/
class AphorismsNewForm extends FormBase {

  /**
   * {@inheritdoc}
  **/
  public function getFormId() {
    return 'aphorisms_form';
  }

  /**
   * {@inheritdoc}
  **/
  public function buildForm(array $form, FormStateInterface $form_state) {
    // $form_1 = \Drupal::entityTypeManager()
    //   ->getFormObject('aphorism', 'contribute');
    // return \Drupal::formBuilder()->getForm($form_1);

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => t('Title'),
      '#required' => true,
    ];
    $form['body'] = [
      '#type' => 'textarea',
      '#title' => t('Body'),
      '#required' => true,
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
    );
    return $form;
  }

  /**
   * {@inheritdoc}
  **/
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // if (strlen($form_state->getValue('phone_number')) < 3) {
    //   $form_state->setErrorByName('phone_number', $this->t('The phone number is too short. Please enter a full phone number.'));
    // }
  }

  /**
   * {@inheritdoc}
  **/
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
    $params = $form_state->getValues();
    $node_manager  = \Drupal::entityTypeManager()->getStorage('node');
    $taxonomy = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    $type = 'aphorism';

    // logg($params, 'the form values');
    // exit(0);

    $new_item = $node_manager->create([
      'title' => $params['title'],
      'body' => $params['body'],
      'type' => $type,
      'author' => $user,
    ]);
    $new_item->save();

    // $thumb = $form_state->getValue('image_thumb');
    // if (!empty($thumb[0])) {
    //     $file = File::load($thumb[0]);
    //     $file->setPermanent();
    //     $file->save;
    // }

    // var_dump( $file->id() );
    // exit(0);


    // $uri = 'https://wco-drupal-prod.s3.amazonaws.com/public/259x66%20WasyaCo%20Logo%20YellowShadow_0.png';
    // $file = File::create([
    //   'uri' => $uri,
    // ]);
    // $file->save();

    // $user->fieldImageThumb[] = [
    //   'target_id' => $file->id(),
    //   'alt' => 'tmp-alt',
    //   'title' => 'tmp-title',
    // ];
    // $user->save();
  }

}
