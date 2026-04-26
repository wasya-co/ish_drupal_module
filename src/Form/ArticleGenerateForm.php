<?php

namespace Drupal\ish_drupal_module\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * ArticleGenerateForm
**/
class ArticleGenerateForm extends FormBase {
  protected ClientInterface $httpClient;
  public function __construct(ClientInterface $http_client) {
    $this->httpClient = $http_client;
  }
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client')
    );
  }

  public function getFormId() {
    return 'article_generate_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['topic'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Topic'),
      '#required' => TRUE,
      '#maxlength' => 255,
    ];

    $terms = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree('tags_issue');
    $options = [];
    foreach ($terms as $term) {
      $options[$term->tid] = $term->name;
    }
    $form['tags_issue'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Tags Issue'),
      '#options' => $options,
      '#required' => TRUE,
    ];

    $terms = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree('tagscontrib');
    $options = [];
    foreach ($terms as $term) {
      $options[$term->tid] = $term->name;
    }
    $form['tags_contrib'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Tags Contrib'),
      '#options' => $options,
      '#required' => TRUE,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Generate Article'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::config('ish_drupal_module.settings');
    $apiKey = $config->get('llm_api_key');
    $apiUrl = "https://llm.wasya.co/api/generate";

    $topic = $form_state->getValue('topic');
    $issue = array_filter($form_state->getValue('tags_issue'));
    $issue = array_values($issue);
    $tags  = array_filter($form_state->getValue('tags_contrib'));
    $tags  = array_values($tags);

    try {
      $response = $this->httpClient->post( $apiUrl,
        [
          'headers' => [
            'Authorization' => 'Bearer '.$apiKey,
            'Accept' => 'application/json',
          ],
          'json' => [
            'model' => 'qwen2.5:32b',
            'stream' => False,
            'prompt' => "Write an article about: ".$topic." Format as html.",
          ],
          'timeout' => 1000,
        ]
      );
      // logg($response, '$response');
      $data = json_decode($response->getBody()->getContents(), TRUE);
      // logg($data, '$data');
      if (empty($data['response'])) {
        throw new \Exception('Empty body returned from service.');
      }
      $body = $data['response'];
    }
    catch (\Exception $e) {
      $this->messenger()->addError($this->t(
        'Failed to generate article body: @message',
        ['@message' => $e->getMessage()]
      ));
      return;
    }

    $node = Node::create([
      'body' => [
        'value' => $body,
        'format' => 'basic_html',
        'summary' => ' ',
      ],
      'type' => 'article',
      'title' => $topic,
      'field_tags_issue' => $issue,
      'field_tags_contrib' => $tags,
      'status' => 1,
    ]);
    $node->save();

    $this->messenger()->addStatus($this->t('Article "%title" created.', [
      '%title' => $topic,
    ]));

    $form_state->setRedirect('entity.node.canonical', [
      'node' => $node->id(),
    ]);
  }
}
