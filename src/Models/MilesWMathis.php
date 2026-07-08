<?php

namespace Drupal\ish_drupal_module\Models;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/*
 * MilesWMathis Model
**/
class MilesWMathis {

  public static function churnOneSlug(string $slug): Node {
    $source_url = "https://mileswmathis.com/$slug.pdf";

    $state = \Drupal::state();
    if (!$state->get('mileswmathis_next_publish_on')) {
      $state->set( 'mileswmathis_next_publish_on', (new DrupalDateTime('+1 day'))->getTimestamp() );
    }

    $article =  \Drupal\ish_drupal_module\Models\Article::findOrBuildBy('field_source_url', $source_url);
    // puts($article, '$article');

    $file_system = \Drupal::service('file_system');
    $tmp_dir = "/tmp/$slug";
    $file_system->prepareDirectory($tmp_dir, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS );

    file_put_contents("$tmp_dir/$slug.pdf", file_get_contents($source_url));

    exec("pdftohtml $tmp_dir/$slug.pdf $tmp_dir/$slug.html 2>&1", $_out, $_code);
    puts($_out, 'ze $_out');

    $html = file_get_contents("/tmp/$slug/{$slug}s.html");
    $html = str_replace('&#160;', ' ', $html);
    // puts($html, 'ze html fin');

    $dom = new \DOMDocument();
    libxml_use_internal_errors(TRUE);
    $dom->loadHTML($html);
    libxml_clear_errors();

    /*
     * images
    **/
    $first = true;
    foreach ($dom->getElementsByTagName('img') as $img) {
      $src = $img->getAttribute('src');

      if (!str_starts_with($src, '/tmp/')) {
        continue;
      }

      $source = $src;
      if (!file_exists($source)) {
        continue;
      }

      $filename = basename($source);
      $thisDir = 'public://mileswmathis';
      $destination = "$thisDir/$filename";
      $file_system->prepareDirectory($thisDir, FileSystemInterface::CREATE_DIRECTORY);

      $new_url = $file_system->copy( $source, $destination, FileSystemInterface::EXISTS_REPLACE );
      // puts($new_url, '$new_url');

      $file = File::create([
        'uri' => $new_url,
        'status' => 1,
      ]);
      $file->save();
      $url = \Drupal::service('file_url_generator')->generateAbsoluteString($new_url);
      // puts($url, '$url');

      $img->setAttribute('src', $url);

      if ($first) {
        $article->set('field_image_thumb', [
          'target_id' => $file->id(),
          'alt'       => $article->label(),
          'title'     => $article->label(),
        ]);
        $first = false;
      }
    }

    /* title, path */
    $body = $dom->getElementsByTagName('body')->item(0);
    $title = '';
    foreach ($body->childNodes as $node) {
      if ($node->nodeType === XML_TEXT_NODE && trim($node->textContent) !== '') {
        $title = trim($node->textContent);
        break;
      }
    }
    puts($title, '$title');
    $article->set('title', $title);
    $article->set('path', [
      'alias' => "/mileswmathis_com/{$slug}_pdf",
      'pathauto' => 0,
    ]);

    /* body, author, status */
    $body = $dom->saveHTML($body);
    $body = preg_replace('~^<body>|</body>$~', '', trim($body));
    // puts($body, '$body');
    $article->set('body', [
      'value' => (string) $body,
      'format' => 'basic_html',
    ]);
    $uid = \Drupal\ish_drupal_module\Models\User::milesMathis();
    $uid = $uid->id();
    $article->set('uid', $uid);
    $article->set('status', 0);

    // publish_on
    $next_publish_on = $state->get('mileswmathis_next_publish_on');
    $article->set('publish_on', $next_publish_on);
    $next_publish_on = strtotime('+1 day', $next_publish_on);
    $state->set( 'mileswmathis_next_publish_on', $next_publish_on );

    // $tagContrib = \Drupal\ish_drupal_module\Models\Tag::find('tags_contrib', 'miles-mathis');
    $tagIssue   = \Drupal\ish_drupal_module\Models\Taxonomy::findOrCreateByName('2025q2-1ne', 'tags_issue'); // slug, taxonomy
    $article->set('field_tags_issue', [
        ['target_id' => $tagIssue->id()],
    ]);

    $article->save();
    puts($article, 'saved article');
    return $article;
  }

}
