<?php

namespace Drupal\ish_drupal_module\Service;

use GuzzleHttp\ClientInterface;
use Symfony\Component\DomCrawler\Crawler;

class ZerohedgeScraper {

  protected ClientInterface $httpClient;

  public function __construct(ClientInterface $http_client) {
    $this->httpClient = $http_client;
  }

  public function all() {
    $response = $this->httpClient->request('GET', 'https://www.zerohedge.com/', [
      'timeout' => 10,
      'headers' => [
        'User-Agent' => 'Mozilla/5.0',
      ],
    ]);

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
      ->filter("article[class*='NewArticle_teaser__']")
      ->each(function (Crawler $item) use (&$headlines) {
        if ($item->filter("div[class*='PremiumBadge_premium__']")->count() > 0) {
          return;
        }
        if ($item->filter("div[class*='PremiumBadge_ns__']")->count() > 0) {
          return;
        }
        // $itemHtml = $item->getNode(0)->ownerDocument->saveHTML($item->getNode(0));
        // logg($itemHtml, 'itemHtml');

        $titleNode = $item->filter('h2');
        $linkNode  = $item->filter('h2 a');
        $subtitleNode = $item->filter("div[class*='Article_desktopLineClamp__']");
        $summaryNode  = $item->filter('p');
        $summaryText = implode("\n", $item->filter('p')->each(function ($node) {
          return trim($node->text());
        }));
        $headlines[] = [
          'title'    => $titleNode->count() ? trim($titleNode->text()) : '',
          'link'     => $linkNode->count() ? $linkNode->attr('href') : '',
          'subtitle' => $subtitleNode->count() ? trim($subtitleNode->text()) : '',
          'summary'  => $summaryText,
        ];
      });

    return $headlines;
  }

  public function one($zhPath) {
    $response = $this->httpClient->request('GET', 'https://www.zerohedge.com' . $zhPath, [
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

    $titleNode = $crawler->filter("[class*='ArticleFull_header__'] h1");
    if ($titleNode->count() === 0) {
        $titleNode = $crawler->filter("[class*='ContributorArticleFull_header__'] h1");
    }
    $contents['title'] = $titleNode->count() ? trim($titleNode->text()) : '';
    // logg($contents['title'], "title");

    $bodyNode = $crawler->filter("[class*='NodeContent_body__']");
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

        // Get innerHTML
        $innerHtml = '';
        foreach ($domNode->childNodes as $child) {
          $innerHtml .= $domNode->ownerDocument->saveHTML($child);
        }
        $contents['html'] = $innerHtml;

        // Get text with double newlines
        $contents['text'] = trim(preg_replace("/\n/", "\n\n", $bodyNode->text()));

        $contents['summary'] = $crawler->filter('meta[name="twitter:description"]')->attr('content');
    } else {
        $contents['html']    = '';
        $contents['text']    = '';
        $contents['summary'] = '';
    }
    // logg($contents, 'contents');

    return $contents;
  }

}
