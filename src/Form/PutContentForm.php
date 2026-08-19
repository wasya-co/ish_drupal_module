<?php

namespace Drupal\ish_drupal_module\Form;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Uuid\Uuid;

use Drupal\file\Entity\File;

use Drupal\layout_builder\Section;
use Drupal\layout_builder\SectionComponent;

use Drupal\node\Entity\Node;

use Drupal\webform\Entity\Webform;

use Drupal\ish_drupal_module\Config\BlocksConfig;
use Drupal\ish_drupal_module\Config\ThisConfig;
use Drupal\ish_drupal_module\Content\NodesContent;

/*
**/
class PutContentForm extends FormBase {

  public function getFormId() {
    return 'put_content_form';
  }

  /*
  **/
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['yaml_file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('YAML file'),
      '#upload_location' => 'private://imports/',
      '#upload_validators' => [
        'FileExtension' => ['extensions' => 'yml yaml'],
      ],
      '#required' => TRUE,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import'),
    ];
    return $form;
  }

  /*
   * only creates a block or a node so far.
  **/
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $fid = $form_state->getValue('yaml_file')[0];
    $file = File::load($fid);
    $file->setPermanent();
    $file->save();
    $yaml_file = Yaml::decode(file_get_contents($file->getFileUri()));

    foreach ($yaml_file ?? [] as $item) {
      logg($item, 'item');

      // [$entity_type, $name] = explode(':', 'advanced_block:copyright', 2);

      switch($item['type']) {
        case 'advanced_block':
        case 'basic':
        case 'section_callout_parallax':
          BlocksConfig::create_block($item);

          break;
        case 'issue':
          NodesContent::create_node($item);

          break;
        case 'menu':

          ThisConfig::put_menu_links($item['id'], $item['links']);

          break;
        case 'webform':
          NodesContent::create_webform($item);
          break;

        // case 'view':
        //   ViewsConfig::create_view($item['view_id'], $item['display_name'], $item);
        //   break;

        default:
          throw new \Exception('zz3 - Not implemented');
      }
    }

  }

}
