<?php

namespace Drupal\ish_drupal_module\Controller;

use \Drupal\Core\Controller\ControllerBase;
use \Drupal\node\NodeInterface;
use \Drupal\taxonomy\Entity\Term;
use \Symfony\Component\HttpFoundation\Request;

/**
 * Bjjc controller.
**/
class BjjcController extends ControllerBase {

  /*
   * /bjjc/create-categories
  **/
  public function createCategories() {
    $handle = fopen($_SERVER['DOCUMENT_ROOT'] . "/modules/ish_drupal_module/data/tbx_category.csv", "r");
    $count = 0;
    $vocab_name = 't1';
    while (($data = fgetcsv($handle)) !== FALSE) {
      if ($count == 0) {
        ; // nothing
        dump($data);
      } else {
        // if ($count == 6) { break; }
        if ($count < 2) { dump($data); }

        $oid   = $data[0];
        $names = array_diff( explode(" - ", $data[1]), ["Top", "Bottom", "Front", "Back", "Normal" ]);
        $name = $data[1];
        $slug = $data[2];

        $manager = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
        $taxonomy_tree = $manager->loadTree(
          $vocab_name, // The taxonomy term vocabulary machine name.
          0,           // The "tid" of parent using "0" to get all.
          NULL,        // Get all available levels.
          false );     // Get full load of taxonomy term entity.

        /*
        $counter_k = 0;
        $term_k    = null;
        $parent_k  = null;
        foreach ($names as $name) {
          if ($counter_k == 0) {

            $term_k = $manager->loadByProperties([
              'name' => $name,
              'vid' => $vocab_name,
            ]);
            if (!$term_k) {
              $term_k = Term::create([
                'field_oid' => $data[0],
                'field_slug' => $slug,
                'name' => $name,
                'vid' => $vocab_name,
              ]);
              $term_k->save();
              puts($term_k, 'created a new term');
            } else {
              $term_k = reset($term_k);
              // puts($term_k, "already exists");
            }

          } else {
            $parent_k = $term_k;
            $taxonomy_tree = $manager->loadTree(
              $vocab_name, // The taxonomy term vocabulary machine name.
              $parent_k->id(),           // The "tid" of parent using "0" to get all.
              1,           // levels
              false      // Get full load of taxonomy term entity.
            );

            $term_k = null;
            foreach ($taxonomy_tree as $t) {
              // puts($name, 'zzis name');
              // puts($t->name, 'zzis t');

              if ($t->name == $name) {
                // puts($t->tid, 'zze t');
                $term_k = $manager->load($t->tid);
                // puts($term_k, "existing term_k");
              }
            }
            if (!$term_k) {
              $term_k = Term::create([
                'field_slug' => $slug,
                'field_oid'  => $oid,
                'name'       => $name,
                'vid'        => $vocab_name,
              ]);
              $term_k->parent = [ $parent_k->id() ];
              $term_k->save();
            }

          }
          $counter_k++;
        }
        */

        $term_k = Term::create([
          'field_oid' => $data[0],
          'field_slug' => $slug,
          'name' => $name,
          'vid' => $vocab_name,
        ]);
        $term_k->save();
        puts($term_k, 'created a new term');



      }
      $count++;
    }

    return [
      '#markup' => "<h1>createCategories()</h1>",
    ];
  }


  /*
   * /bjjc/create-videos
  **/
  public function createVideos() {
    $hash = [];
    $vid = 't1';

    /* videos */
    $count = 0;
    $videos_handle = fopen($_SERVER['DOCUMENT_ROOT'] . "/modules/ish_drupal_module/data/tbx_video.csv", "r");
    while (($data = fgetcsv($videos_handle)) !== FALSE) {
      $count++;
      if ($count == 0) {
        ; // nothing
        // dump($data);
      } else {
        // if ($count == 1) { dump($data); }

        $obj = [
          'video_id'    => $data[0],
          'name'        => $data[2],
          'category_id' => $data[11],
        ];
        if (!$obj['name']) {
          $obj['name'] = 'NO NAME';
        }
        $hash[$obj['video_id']] = $obj;
      }

    }
    // logg($hash, 'ze hash');

    /* clips */
    $count = 0;
    $clips_handle  = fopen($_SERVER['DOCUMENT_ROOT'] . "/modules/ish_drupal_module/data/tbx_video_clip.csv", "r");
    while (($data = fgetcsv($clips_handle)) !== FALSE) {
      if ($count == 0) {
        ; // nothing
        // dump($data);
      } else {
        // if ($count == 1) { dump($data); }

        $youtube_id = substr( $data[3], strpos($data[3], "/embed/")+7, 11 );
        // logg($youtube_id, 'youtube_id');
        $hash[$data[1]]['youtube_id'] = $youtube_id;
      }
      $count++;
    }
    // logg($hash, 'ze hash');

    $skip = 0;
    $max_idx = 1000000;
    $video_type = 'v1';
    $count = 0;
    $manager  = \Drupal::entityTypeManager()->getStorage('node');
    $taxonomy = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    foreach($hash as $item) {
      // logg($item, 'item '.$count);
      if ($count == $max_idx) { break; }

      if ($count < $skip) {
        ; // nothing
        // dump($data);

      } else {
        if ($item['video_id']) {

          $existing = $manager->loadByProperties([
            'type' => $video_type,
            'field_oid' => $item['video_id'],
          ]);
          if (!$existing) {

            $category = $taxonomy->loadByProperties([ 'vid' => $vid, 'field_oid' => $item['category_id'] ]);
            $category = reset($category);
            // logg($category->id(), 'category');

            $new_item = $manager->create([
              'field_oid' => $item['video_id'],
              'field_youtube_id' => $item['youtube_id'],
              'title' => $item['name'],
              'type' => $video_type,
            ]);
            if ($category) {
              $new_item->field_tags[] = ['target_id' => $category->id()];
            }
            $new_item->save();

            // logg($new_item, 'saved');
          }

        }
      }
      $count = $count + 1;
    }

    return [
      '#markup' => "<h1>createVideos()</h1>",
    ];
  }

  public function deleteVideos() {
    $video_type = 'video';
    $limit = 3000;

    $manager  = \Drupal::entityTypeManager()->getStorage('node');
    $query  = \Drupal::entityQuery('node')->accessCheck(TRUE);

    $existings = $query->condition(
      'type', $video_type
    )->range(0, $limit)->execute();
    logg($existings, 'existings');

    $counter = 0;
    $ids = [];
    foreach($existings as $e) {
      $counter = $counter++;
      array_push( $ids , $e );
    }
    logg($ids, 'ids');

    $entities = $manager->loadMultiple($ids);
    \Drupal::entityTypeManager()->getStorage('node')->delete($entities);

    return [
      '#markup' => "<h1>deleteVideos()</h1>",
    ];
  }

}
