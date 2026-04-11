<?php

namespace Drupal\ish_drupal_module\Service;

use Drupal\Component\Serialization\Json;
use GuzzleHttp\ClientInterface;

class Pexels {
  protected ClientInterface $httpClient;
  protected string $apiKey;

  public function __construct(ClientInterface $http_client) {
    $this->httpClient = $http_client;

    $config = \Drupal::config('ish_drupal_module.settings');
    $this->apiKey = $config->get('pexels_api_key');
  }

  /**
   * getImageFromPrompt
  **/
  public function imageUrlFromPrompt(string $prompt, string $size = 'small') {
    $url = 'https://api.pexels.com/v1/search?query=' . urlencode($prompt) . '&per_page=1';

    try {
      $response = $this->httpClient->request('GET', $url, [
        'headers' => [
          'Authorization' => $this->apiKey,
        ],
      ]);

      if ($response->getStatusCode() !== 200) {
        return null;
      }

      $data = Json::decode($response->getBody()->getContents());

      if (!empty($data['photos'][0]['src'][$size])) {
        return $data['photos'][0]['src'][$size];
      }
    }
    catch (\Exception $e) {
      // $this->logger->error('Pexels API error: ' . $e->getMessage());
    }

    return null;
  }

}
