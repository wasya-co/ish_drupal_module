<?php
namespace Drupal\ish_drupal_module\Config;

use Drupal\block\Entity\Block;
use Drupal\block_content\Entity\BlockContentType;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\Core\Form\FormStateInterface;
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

use Symfony\Component\HttpFoundation\RedirectResponse;

class ModulesConfig {

  /*
   * install_modules
  **/
  public static function install_modules() {
    $modules = array_filter([
      'admin_toolbar', 'admin_toolbar_tools',
      'devel',
      'hcaptcha',
      'layout_builder', 'layout_discovery',
      // 'paragraphs',
      // 's3fs',
      'superfish',
      'toolbar', 'twig_tweak',
      'viewsreference',
      'webform', 'webform_ui',
    ], static fn ($module) => !\Drupal::moduleHandler()->moduleExists($module) );

    if ($modules) {
      \Drupal::service('module_installer')->install($modules);
    }
  }



}
