<?php

namespace Drupal\ish_drupal_module\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements an example form.
**/
class ForYoutube extends FormBase {

  /**
   * {@inheritdoc}
  **/
  public function getFormId() {
    return 'for_youtube';
  }

  /**
   * {@inheritdoc}
  **/
  public function buildForm(array $form, FormStateInterface $form_state) {
    // $form_1 = \Drupal::entityTypeManager()
    //   ->getFormObject('aphorism', 'contribute');
    // return \Drupal::formBuilder()->getForm($form_1);

    $vocabulary_id = 'tagscontrib';
    $taxonomy = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vocabulary_id);
    $options = [];
    foreach ($taxonomy as $tag) {
      $options[$tag->tid] = $tag->name;
    }
    // logg($options, '$options');

    $form['youtube_url'] = [
      '#type' => 'textfield',
      '#title' => t('youtube url'),
      '#required' => true,
    ];
    $form['tags'] = [
      '#type' => 'checkboxes',
      '#title' => t('Tags'),
      '#options' => $options,
      '#required' => false,
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
    );

    return $form;
  }

  function youtube_title(string $id) {
    $config = \Drupal::config('ish_drupal_module.settings');
    $api_key = $config->get('google_api_youtube_key');

    $url = 'https://www.googleapis.com/youtube/v3/videos?part=snippet&id='.$id.'&key='.$api_key;
    $json = file_get_contents($url);
    $decoded_json = json_decode($json, false);
    // logg($decoded_json, '$decoded_json');
    $title = $decoded_json->items[0]->snippet->title;
    // logg($title, 'ze title');
    return $title;
  }

  /**
   * {@inheritdoc}
   * Example: https://www.youtube.com/watch?v=pjgFYQMWtqo
  **/
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
    $node_manager  = \Drupal::entityTypeManager()->getStorage('node');
    // $taxonomy = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    $params = $form_state->getValues();
    // logg($params, '$params');

    $tmp = parse_url($form_state->getValue('youtube_url'), PHP_URL_QUERY);
    parse_str($tmp, $tmp);
    // logg($tmp);
    $youtube_id = $tmp['v'];
    $youtube_title = $this->youtube_title($youtube_id);

    $body = <<<AOL
      <iframe width="560" height="315" src="https://www.youtube.com/embed/$youtube_id" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write;
        encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin"
        allowfullscreen></iframe>
    AOL;

    $type = 'page_youtube';
    $issue_uuid = '35'; // '4ac9695b-0854-4972-8528-1f52e21d2235'; // taxonomy_term/35 :: 2024q1-issue
    $new_item = $node_manager->create([
      'author' => $user,
      'body' => [
        'value' => $body,
        'format' => 'full_html',
      ],
      'field_youtube_id' => $youtube_id,
      'field_issue' => [ 'target_id' => $issue_uuid ],
      'status' => 1,
      'title' => $youtube_title,
      'type' => $type,
    ]);
    foreach (array_filter(array_values($params['tags'])) as $val) {
      $new_item->field_tags[] = [ 'target_id' => $val ];
    }
    $new_item->save();
    \Drupal::messenger()->addMessage('Item From Youtube has been saved.');
  }

}

