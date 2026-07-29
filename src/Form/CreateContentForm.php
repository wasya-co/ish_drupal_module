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


/*
**/
class CreateContentForm extends FormBase {

  public function getFormId() {
    return 'create_content_form';
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
  **/
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $fid = $form_state->getValue('yaml_file')[0];
    $file = File::load($fid);
    $file->setPermanent();
    $file->save();
    $data = Yaml::decode(file_get_contents($file->getFileUri()));

    foreach ($data['create_content'] ?? [] as $item) {

      $nodes = \Drupal::entityTypeManager()
        ->getStorage('node')
        ->loadByProperties([
          'type' => $item['type'],
          'title' => $item['fields']['title'],
        ]);
      $node = reset($nodes);
      if ($node) {
        // continue;
        $node->delete(); // _TODO: remove
      }

      $values = [
        'type' => $item['type'],
        'status' => 1,
        'path' => [
          'alias' => $item['path'],
          'pathauto' => 0,
        ],
      ];
      foreach ($item['fields'] as $field_name => $field_value) {
        if ('field_image_thumb' == $field_name) {
          $contents = file_get_contents($field_value);
          $directory = 'public://field_image_thumb';
          \Drupal::service('file_system')->prepareDirectory( $directory, FileSystemInterface::CREATE_DIRECTORY );
          $destination = $directory . '/' . basename(parse_url($field_value, PHP_URL_PATH));
          $file = \Drupal::service('file.repository')->writeData(
            $contents,
            $destination,
            FileSystemInterface::EXISTS_RENAME
          );
          $values['field_image_thumb'] = [
            'target_id' => $file->id(),
            'alt' => '',
          ];
        } else {
          $values[$field_name] = $field_value;
        }
      } // end fields
      $node = Node::create($values);
      $node->save();


      if ($item['sections']) {
        $outs = [];
        foreach($item['sections'] as $this_section) {

          $section = new Section( $this_section['type'], $this_section['config'] );
          foreach ($this_section['regions'] as $region => $region_c) {

            $extra = [
              'id' => "views_block:{$region_c['view_id']}",
              'label' => $region_c['label'],
              'label_display' => FALSE,
              'provider' => 'views',
            ];
            $uuid = \Drupal::service('uuid')->generate();
            $component = new SectionComponent( $uuid, $region, $extra );
            $section->appendComponent($component);

          }
          $outs[] = $section;
        }
        $node->set('layout_builder__layout', $outs);
        $node->save();
      }

    }
  }

}
