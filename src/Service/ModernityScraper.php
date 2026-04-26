<?php

namespace Drupal\ish_drupal_module\Service;

use GuzzleHttp\ClientInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ModernityScraper {

  protected ClientInterface $httpClient;

  public function __construct(ClientInterface $http_client) {
    $this->httpClient = $http_client;
  }

  public function all() {
    $response = $this->httpClient->request('GET', 'https://modernity.news/wp-json/wp/v2/posts');

    if ($response->getStatusCode() !== 200) {
      return [];
    }
    // var_dump( $response );

    $posts = $response->getBody()->getContents();
    $posts = json_decode($posts, TRUE);
    // var_dump( $posts );

    $contents = [];

    foreach ($posts as $post) {
      $contents[] = [
        'title' => $post['title']['rendered'] ?? '',
        'summary' => $post['excerpt']['rendered'] ?? '',
        'link' => $post['link'] ?? '',
      ];
    }

    return $contents;
  }
}
