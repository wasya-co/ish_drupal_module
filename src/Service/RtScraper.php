<?php

namespace Drupal\ish_drupal_module\Service;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DomCrawler\Crawler;

class RtScraper {

  protected ClientInterface $httpClient;

  public function __construct(ClientInterface $http_client) {
    $this->httpClient = $http_client;
  }

  /**
   * all(), not implemented
  **/
  public function all() {
    try {
      $response = $this->httpClient->request('GET', 'https://rt.com/', [
        'timeout' => 10,
        'headers' => [
          'User-Agent' => 'Mozilla/5.0',
        ],
      ]);
    }
    catch (GuzzleException $e) {
      \Drupal::messenger()->addMessage('curl failed');
      return [];
    }

    $html = (string) $response->getBody();
    // var_dump( $html );

    $crawler = new Crawler($html);

    $headlines = [];

    // $node = $crawler->filter('body');
    // return $node->html();

    /* 3 top promoted articles */
    // $crawler
    //   ->filter("div[class*='ContributorArticleFeatured_container__']")
    //   ->each(function (Crawler $item) use (&$headlines) {
    //     $titleNode = $item->filter('h2');
    //     $linkNode  = $item->filter('h2 a');
    //     $authorNode = $item->filter("[class*='ContributorArticleFeatured_author__']");
    //     $subtitleNode = $item->filter("[class*='ContributorArticleFeatured_text__']");
    //     $headlines[] = [
    //       'title'    => $titleNode->count() ? trim($titleNode->text()) : '',
    //       'link'     => $linkNode->count() ? $linkNode->attr('href') : '',
    //       'author'   => $authorNode->count() ? trim($authorNode->text()) : '',
    //       'subtitle' => $subtitleNode->count() ? trim($subtitleNode->text()) : '',
    //     ];
    //   });


    /* sticky articles */
    /* $crawler
      ->filter("article[class*='NewArticle_sticky__']")
      ->each(function (Crawler $item) use (&$headlines) {
        // var_dump($item);

        if ($item->filter("div[class*='PremiumBadge_premium__']")->count() > 0) {
          return;
        }
        if ($item->filter("div[class*='PremiumBadge_ns__']")->count() > 0) {
          return;
        }

        $titleNode    = $item->filter('h2');
        $linkNode     = $item->filter('h2 a');
        $subtitleNode = $item->filter("div[class*='Article_desktopLineClamp__']");
        $summaryNode  = $item->filter('p');
        $summaryText  = implode("\n", $item->filter('p')->each(function ($node) {
          return trim($node->text());
        }));
        // logg($summaryNode->text(), '$summaryNode');
        $headlines[] = [
          'title'    => $titleNode->count()    ? trim($titleNode->text())    : '',
          'link'     => $linkNode->count()     ? $linkNode->attr('href')     : '',
          'subtitle' => $subtitleNode->count() ? trim($subtitleNode->text()) : '',
          'summary'  => $summaryText,
        ];
      }); */


    $crawler
      ->filter(".main-promobox .main-promobox__item")
      ->each(function (Crawler $item) use (&$headlines) {

        // if ($item->filter("div[class*='PremiumBadge_premium__']")->count() > 0) {
        //   return;
        // }
        // if ($item->filter("div[class*='PremiumBadge_ns__']")->count() > 0) {
        //   return;
        // }

        $titleNode = $item->filter('.main-promobox__link');
        $linkNode  = $item->filter('.main-promobox__heading');
        // $subtitleNode = $item->filter("div[class*='Article_desktopLineClamp__']");
        // $summaryNode  = $item->filter('p');
        // $summaryText = implode("\n", $item->filter('p')->each(function ($node) {
        //   return trim($node->text());
        // }));
        $headlines[] = [
          'title'    => $titleNode->count() ? trim($titleNode->text()) : '',
          'link'     => $linkNode->count() ? $linkNode->attr('href') : '',
          // 'subtitle' => $subtitleNode->count() ? trim($subtitleNode->text()) : '',
          // 'summary'  => $summaryText,
        ];
      });

    return $headlines;
  }

  /**
   * one()
  **/
  public function one($rtPath) {
    $response = $this->httpClient->request('GET', 'https://rt.com' . $rtPath, [
      'timeout' => 10,
      'headers' => [
        'User-Agent' => 'Mozilla/5.0',
      ],
    ]);

    $html = (string) $response->getBody();

    $crawler = new Crawler($html);
    $bodyHtml = $crawler->getNode(0)->ownerDocument->saveHTML($crawler->getNode(0));
    // logg($bodyHtml, 'bodyHtml');

    $contents = [];

    $titleNode = $crawler->filter("h1.article__heading")->first();
    $contents['title'] = $titleNode->count() ? trim($titleNode->text()) : '';
    // logg($contents['title'], "title");

    $contents['summary'] = $crawler->filter('.article__summary')->first()->text();
    $contents['summary'] = $contents['summary'] ? trim($contents['summary']) : $contents['summary'];

    $bodyNode = $crawler->filter(".article__text");
    if ($bodyNode->count() > 0) {
        $domNode = $bodyNode->getNode(0);

        $xpath = new \DOMXPath($domNode->ownerDocument);
        $imgs = $xpath->query('.//img', $domNode);
        foreach ($imgs as $k) {
          $k->parentNode->removeChild($k);
        }
        $pictures = $xpath->query('.//picture', $domNode);
        foreach ($pictures as $k) {
          $k->parentNode->removeChild($k);
        }
        $iframes = $xpath->query('.//iframe', $domNode);
        foreach ($iframes as $k) {
          $k->parentNode->removeChild($k);
        }

        $bodyNode->filter('.read-more')->each(function(Crawler $node) {
          $domNode = $node->getNode(0);
          $domNode->parentNode->removeChild($domNode);
        });
        $bodyNode->filter('.Read-more-text-only')->each(function(Crawler $node) {
          $domNode = $node->getNode(0);
          $domNode->parentNode->removeChild($domNode);
        });


        // Get innerHTML
        $innerHtml = '';
        foreach ($domNode->childNodes as $child) {
          $innerHtml .= $domNode->ownerDocument->saveHTML($child);
        }
        $contents['html'] = $innerHtml;

        // Get text with double newlines
        $contents['text'] = trim(preg_replace("/\n/", "\n\n", $bodyNode->text()));


    } else {
        $contents['html']    = '';
        $contents['text']    = '';
        $contents['summary'] = '';
    }
    // logg($contents, 'contents');

    return $contents;
  }

}
