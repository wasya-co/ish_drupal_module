<?php

namespace Drupal\ish_drupal_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/*
 * Miles W Mathis Controller
**/
class MilesWMathisController extends ControllerBase {

  public function churnOne($slug, Request $request) {
    $source_url = "https://mileswmathis.com/$slug.pdf";

    // does the article already exist?
    $nid = \Drupal::entityQuery('node')
      ->condition('type', 'article')
      ->condition('field_source_url', $source_url)
      ->range(0, 1)
      ->accessCheck(FALSE)
      ->execute();

    // if ($nid) {
    //   return;
    // }

    $file_system = \Drupal::service('file_system');
    $temp_dir = 'temporary://' . $slug;
    $file_system->prepareDirectory(
      $temp_dir,
      FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS
    );
    $real_dir = $file_system->realpath($temp_dir);

    $pdf_file = $real_dir . '/' . $slug . '.pdf';
    $html_file = $real_dir . '/' . $slug . '.html';
    $html_file_2 = $real_dir . '/' . $slug . 's.html';
    $contents = file_get_contents($source_url);
    // puts($contents, 'ze contents');
    file_put_contents($pdf_file, $contents);

    $command = sprintf(
      'pdftohtml %s %s 2>&1',
      escapeshellarg($pdf_file),
      escapeshellarg($html_file)
    );
    exec($command, $output, $return);

    if ($return !== 0) {
      throw new \Exception(implode("\n", $output));
    }

    if (!file_exists($html_file)) {
      throw new \Exception('HTML file was not created.');
    }

    $html = file_get_contents($html_file_2);
    $html = str_replace('&#160;', ' ', $html);
    // puts($html, 'ze html');



    $dom = new \DOMDocument();
    libxml_use_internal_errors(TRUE);
    $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
    libxml_clear_errors();

    foreach ($dom->getElementsByTagName('img') as $img) {
      $src = $img->getAttribute('src');

      // Only process images created by pdftohtml.
      if (!str_starts_with($src, '/tmp/')) {
        continue;
      }

      $source = $src;

      if (!file_exists($source)) {
        continue;
      }

      $filename = basename($source);

      $destination = 'public://mileswmathis/' . $filename;
      $thisDir = 'public://mileswmathis';
      $file_system->prepareDirectory($thisDir, FileSystemInterface::CREATE_DIRECTORY);

      $new_url = $file_system->copy(
        $source,
        $destination,
        FileSystemInterface::EXISTS_REPLACE
      );

      $file = File::create([
        'uri' => $new_url,
        'status' => 1,
      ]);
      $file->save();
      $url = \Drupal::service('file_url_generator')->generateAbsoluteString($new_url);

      $img->setAttribute('src', $url);
    }

    $body = $dom->saveHTML($dom->getElementsByTagName('body')->item(0));

    // Remove the surrounding <body> tags.
    $body = preg_replace('~^<body>|</body>$~', '', trim($body));


    $node = Node::create([
      'type' => 'article',
      'title' => ucfirst($slug),
      'body' => [
        'value' => $html,
        'format' => 'full_html',
      ],
      'status' => 1,
    ]);

    $node->save();

    return new Response("Created Article {$node->id()}");

    // substitute whitespace
    // save image thumb
    // save the article
    // header, footer, author?
    // tags?
  }

}

