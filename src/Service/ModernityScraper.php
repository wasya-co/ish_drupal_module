<?php

namespace Drupal\ish_drupal_module\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ModernityScraper {

  protected $client;

  public function __construct(HttpClientInterface $client) {
    $this->client = $client;
  }

  public function getPosts(): array {
    $response = $this->client->request(
      'GET',
      'https://modernity.news/wp-json/wp/v2/posts'
    );

    if ($response->getStatusCode() !== 200) {
      return [];
    }
    $posts = $response->toArray();
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
