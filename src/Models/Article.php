<?php

namespace Drupal\ish_drupal_module\Models;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;

/*
 * Article
**/
class Article {

  public static function findOrBuildBy(string $field_name, string $field_value): Node {
    $one = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties([
          'type' => 'article',
          $field_name => $field_value,
      ]);
    $node = reset($one);
    if (!$node) {
      $node = \Drupal\node\Entity\Node::create([
        'type' => 'article',
        $field_name => $field_value,
      ]);
    }
    return $node;
  }

  /*
   * args: nid, langcode
  **/
  public static function translateTo($nid, $langcode) {
    $config = \Drupal::config('ish_drupal_module.settings');
    $apiKey = $config->get('libretranslate_api_key');
    $apiUrl = "https://translate.wasyaco.com/translate";

    $item = Node::load($nid);
    $data = [ /* body */
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

        CURLOPT_VERBOSE => true,
    ]);
    $response = curl_exec($ch);
    puts($response, '$response');
    if ($response === false) {
        die("cURL Error: " . curl_error($ch));
    }
    $tr_body = json_decode($response, true)['translatedText'];


    $data = [ /* title */
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

    $_translation = $item->addTranslation($langcode, [
      'title' => $tr_title,
      'body' => [
        'value' => $tr_body,
        'format' => 'basic_html',
        'summary' => ' ',
      ],
    ]);

    $item->save();
  }


}
