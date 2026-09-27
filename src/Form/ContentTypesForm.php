<?php

namespace Drupal\ish_drupal_module\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ish_drupal_module\Config\ContentTypesConfig;
use Drupal\ish_drupal_module\Config\DefaultFields;

/*
 * Create a content type from DefaultFields presets.
 *
 * Available at: /admin/ish_drupal_module/content_types/edit
**/
class ContentTypesForm extends FormBase {

  /**
   * OK
  **/
  public function getFormId() {
    return 'ish_drupal_module_new_content_type';
  }

  /**
   * {@inheritdoc}
  **/
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Content type name'),
    ];

    $rows = $form_state->get('field_rows');
    if ($rows === NULL) {
      $rows = $this->defaultRows();
      $form_state->set('field_rows', $rows);
    }

    $type_options = ['nil' => ''] + array_combine(
      array_keys(DefaultFields::$list),
      array_keys(DefaultFields::$list)
    );

    $form['fields'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Field name'),
        $this->t('Field type'),
        $this->t('Operations'),
      ],
      '#empty'  => $this->t('No fields.'),
      '#prefix' => '<div id="content-types-fields">',
      '#suffix' => '</div>',
    ];

    foreach ($rows as $delta => $row) {
      $form['fields'][$delta]['name'] = [
        '#type'          => 'textfield',
        '#title'         => $this->t('Field name'),
        '#title_display' => 'invisible',
        '#default_value' => $row['name'],
      ];
      $form['fields'][$delta]['type'] = [
        '#type'          => 'select',
        '#title'         => $this->t('Field type'),
        '#title_display' => 'invisible',
        '#options'       => $type_options,
        '#default_value' => $row['type'],
      ];
      $form['fields'][$delta]['remove'] = [
        '#type'   => 'submit',
        '#value'  => $this->t('Delete'),
        '#name'   => 'remove_field_' . $delta,
        '#submit' => ['::removeRow'],
        '#limit_validation_errors' => [],
        '#ajax' => [
          'callback' => '::refreshFields',
          'wrapper'  => 'content-types-fields',
        ],
      ];
    }

    $form['add'] = [
      '#type'   => 'submit',
      '#value'  => $this->t('Add row'),
      '#submit' => ['::addRow'],
      '#limit_validation_errors' => [],
      '#ajax' => [
        'callback' => '::refreshFields',
        'wrapper'  => 'content-types-fields',
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#attributes' => [
        'onclick' => "return confirm('" . $this->t('Are you sure you want to create this content type?') . "');",
      ],
    ];

    return $form;
  }

  /* OK
  **/
  public function refreshFields(array &$form, FormStateInterface $form_state) {
    return $form['fields'];
  }

  /* OK
  **/
  public function addRow(array &$form, FormStateInterface $form_state) {
    $rows = $this->rowsFromInput($form_state);
    $rows[] = [
      'name' => '',
      'type' => 'text',
    ];
    $this->storeRows($form_state, $rows);
  }

  /*
  **/
  public function removeRow(array &$form, FormStateInterface $form_state) {
    $delta = $form_state->getTriggeringElement()['#parents'][1];
    $rows  = $this->rowsFromInput($form_state);
    unset($rows[$delta]);
    $this->storeRows($form_state, array_values($rows));
  }

  /**
   * {@inheritdoc}
  **/
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // logg($form_state, 'form state');
    $name = strtolower(trim($form_state->getValue('name')));
    $name = preg_replace('/[^a-z0-9_]+/', '_', $name);
    $name = substr(trim($name, '_'), 0, 32);
    if ($name === '') {
      return;
    }

    $fields = [];
    foreach ($form_state->getValue('fields') ?? [] as $field) {
      if (!is_array($field) || empty($field['name']) || !isset(DefaultFields::$list[$field['type']])) {
        continue;
      }
      $fields[$field['name']] = DefaultFields::$list[$field['type']];
    }

    ContentTypesConfig::setup_content_type($name, $fields);
    $this->messenger()->addStatus($this->t('Content type `@type` saved.', ['@type' => $name]));
  }

  /**
   * One row per DefaultFields::default_node_fields entry.
  **/
  protected function defaultRows() {
    $rows = [];
    foreach (DefaultFields::default_node_fields_list as $name => $type) {
      $rows[] = [
        'name' => $name,
        'type' => $type,
      ];
    }
    return $rows;
  }

  /**
   * Read the current table values from the submitted form.
  **/
  protected function rowsFromInput(FormStateInterface $form_state) {
    // logg($form_state, 'form_state');

    $input = $form_state->getUserInput();
    $rows = [];
    foreach ($input['fields'] as $row) {
      $rows[] = [
        'name' => $row['name'],
        'type' => $row['type'],
      ];
    }
    return $rows;
  }

  /*
   * This doesn't save, acts *before* saving.
  **/
  protected function storeRows(FormStateInterface $form_state, array $rows) {
    $input = $form_state->getUserInput();
    unset($input['fields']);
    $form_state->setUserInput($input);
    $form_state->unsetValue('fields');
    $form_state->set('field_rows', $rows);
    $form_state->setRebuild();
  }

}
