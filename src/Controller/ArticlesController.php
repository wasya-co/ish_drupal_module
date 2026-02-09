<?php

namespace Drupal\ish_drupal_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/*
 * ltmanage keys add 1000
**/
class ArticlesController extends ControllerBase {

  /**
   * List *all* articles.
  **/
  public function missingTranslation() {
    $storage = $this->entityTypeManager()->getStorage('node');

    $query = $storage->getQuery()
      ->condition('type', 'article')
      ->condition('status', 1)
      ->sort('created', 'ASC')
      ->accessCheck(TRUE)
      ->pager(100);

    $these_nodes = [];
    $nids = $query->execute();
    if (!empty($nids)) {
      $nodes = $storage->loadMultiple($nids);
      foreach ($nodes as $node) {
        $these_nodes[] = [
          'nid' => $node->id(),
          'title' => $node->label(),
          'trs' => array_keys($node->getTranslationLanguages()),
        ];
      }
    }

    return [
      '#cache' => ['max-age' => 0],
      '#items' => $these_nodes,
      '#pager' => [
        '#type' => 'pager',
      ],
      '#theme' => 'articles_index',
      '#title' => $this->t('Articles missing Spanish translation'),
    ];
  }

  /**
   * ai-translate one to spanish
  **/
  public function translateOne($id, $langcode) {
    $config = \Drupal::config('ish_drupal_module.settings');
    $apiKey = $config->get('libretranslate_api_key');
    $apiUrl = "https://translate.wasyaco.com/translate";

    // $token = $request->request->get('csrf_token');
    // if (!$csrf->validate($token, 'ish_drupal_module.article_translate')) {
    //   throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
    // }

    $item = Node::load($id);
    $data = [
        "q" => $item->get('body')->value,
        "source" => "en",
        "target" => explode('-', $langcode)[0],
        "format" => "html",
        "api_key" => $apiKey,
    ];
    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [ "Content-Type: application/json" ],
        CURLOPT_POSTFIELDS => json_encode($data),
    ]);
    $response = curl_exec($ch);
    if ($response === false) {
        die("cURL Error: " . curl_error($ch));
    }
    $tr_body = json_decode($response, true)['translatedText'];


    $data = [
      "q" => $item->get('title')->value,
      "source" => "en",
      "target" => explode('-', $langcode)[0],
      "format" => "text",
      "api_key" => $apiKey,
    ];
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POST => true,
      CURLOPT_HTTPHEADER => [ "Content-Type: application/json" ],
      CURLOPT_POSTFIELDS => json_encode($data),
    ]);
    $response = curl_exec($ch);
    if ($response === false) {
        die("cURL Error: " . curl_error($ch));
    }
    $tr_title = json_decode($response, true)['translatedText'];

    curl_close($ch);

    $translation = $item->addTranslation($langcode, [
      'title' => $tr_title,
      'body' => [
        'value' => $tr_body,
        'format' => 'basic_html',
        'summary' => ' ',
      ],
    ]);

    $item->save();

    return [
      '#item' => $item,
      '#cache' => ['max-age' => 0],
      '#title' => $this->t('Translate this article'),
      '#theme' => 'article_translate',
    ];
  }

}
