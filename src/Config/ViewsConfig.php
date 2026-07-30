<?php
namespace Drupal\ish_drupal_module\Config;


use Drupal\block\Entity\Block;
use Drupal\block_content\Entity\BlockContentType;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Serialization\Yaml;
use Drupal\Core\Url;

use Drupal\editor\Entity\Editor;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\filter\Entity\FilterFormat;

use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\node\NodeInterface;

use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\taxonomy\Entity\Term;

use Drupal\views\Entity\View;

use Symfony\Component\HttpFoundation\RedirectResponse;



/*
**/
class ViewsConfig {

  /*
   * 2026-07-30 continue
  **/
  public static function create_view($view_id, $display_name, $config) {
    $view = View::load($view_id);
    // if ($view) { $view->delete(); } // _TODO: remove

    if (!$view) {
      $path = \Drupal::service('extension.list.module')
        ->getPath('ish_drupal_module') . '/config/install/views.view.master.yml';
      $defaults = Yaml::decode(file_get_contents($path));
      $defaults['id'] = $view_id;

      $view = View::create($defaults);
      $view->save();
      $view = View::load($view_id);
    }

    $display = $view->get('display');

    // if ($config['default']['display_options']['filters']) {
    //   $display['default']['display_options']['filters'] = $config['default']['display_options']['filters'];
    // }


    // $display[$display_name] = [
    //   'id' => $display_name,
    //   'display_options' => $config['display_options']??[],
    //   'display_plugin' => $config['display_plugin'],
    //   'display_title' => $config['display_title'],
    //   'defaults' => $config['defaults']??[],
    // ];
    // $view->set('display', $display);

    $view->addDisplay($config['display_plugin'], "title: $display_name", $display_name);
    $view->save();
  }

}

