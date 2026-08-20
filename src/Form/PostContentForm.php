<?php

namespace Drupal\ish_drupal_module\Form;

use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Uuid\Uuid;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

use Drupal\file\Entity\File;

use Drupal\layout_builder\Section;
use Drupal\layout_builder\SectionComponent;

use Drupal\node\Entity\Node;

use Drupal\ish_drupal_module\Config\BlocksConfig;
use Drupal\ish_drupal_module\Config\ViewsConfig;
use Drupal\ish_drupal_module\Content\NodesContent;

/*
 * OBSOLETE - use PUT only.
**/
class PostContentForm extends FormBase {

  public function getFormId() {
    return 'post_content_form';
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
    $yml_file = Yaml::decode(file_get_contents($file->getFileUri()));

    // foreach ($yml_file['create_content'] ?? [] as $item) {
    //   if ($item['entity_type']??null) {
    //     if ('block' == $item['entity_type']) {
    //       BlocksConfig::create_block($item);
    //     }
    //     if ('node' == $item['entity_type']) {
    //       NodesContent::create_node($item['type'], $item['path'], $item);
    //     }
    //     if ('view' == $item['entity_type']) {
    //       ViewsConfig::create_view($item['view_id'], $item['display_name'], $item);
    //     }
    //   }
    // }

    foreach ($yml_file['add_section']??[] as $item) {
      NodesContent::add_section_to($item['to_node'], $item);
    }
  }

}
