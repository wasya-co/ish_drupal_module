<?php
namespace Drupal\ish_drupal_module\Config;

/*
 * default fields
**/
class DefaultFields {

  public const body = [
    'field_config_settings' => [
      'display_summary' => TRUE,
      'required_summary' => FALSE,
    ],
    'field_storage_config_settings' => [
      'display_summary' => TRUE,
      'required_summary' => FALSE,
    ],
    'form_display' => 'text_textarea_with_summary',
    'form_display_settings' => [
      'rows' => 9,
      'summary_rows' => 3,
      'placeholder' => '',
      'show_summary' => true,
    ],
    'default_value' =>[[ 'value' => '', 'format' => 'full_html' ]],
    'display' => 'text_default',
    'translatable' => true,
    'type' => 'text_with_summary',
  ];

  public const file = [
    'display' => 'file_url_plain',
    'field_config_settings' => [
      'file_extensions' => 'mp4 webm ogv svg',
      'description_field' => FALSE,
    ],
    'field_storage_config_settings' => [
      'target_type' => 'file',
      'uri_scheme' => 'public',
    ],
    'form_display' => 'file_generic',
    'form_display_settings' => [],
    'type' => 'file',
  ];

  public const select_3style = [
    'default_value' => ['value' => 'style_1'],
    'field_config_settings' => [],
    'field_storage_config_settings' => [
      'allowed_value' => [
        'style_1' => 'style_1',
        'style_2' => 'style_2',
        'style_3' => 'style_3',
      ],
    ],
    'form_display' => 'options_select',
    'display' => 'string',
    'type' => 'list_string',
  ];

  public const toggle = [
    'form_display' => 'boolean_checkbox',
    'default_value' => [
      ['value' => 1],
    ],
    'display' => 'boolean',
    'type' => 'boolean',
  ];

  public const image = [
    'display' => 'image',
    'field_config_settings' => [
      'file_extensions' => 'png gif jpg jpeg webp',
      'alt_field' => TRUE,
      'alt_field_required' => FALSE,
      'title_field' => FALSE,
    ],
    'field_storage_config_settings' => [
      'uri_scheme' => 'public',
    ],
    'form_display' => 'image_image',
    'form_display_settings' => [
      'progress_indicator' => 'throbber',
      'preview_image_style' => 'thumbnail',
    ],
    'type' => 'image',
  ];
  public const image_url = [
    'display' => 'image_url',
    'field_config_settings' => [
      'file_extensions' => 'png gif jpg jpeg webp',
      'alt_field' => TRUE,
      'alt_field_required' => FALSE,
      'title_field' => FALSE,
    ],
    'field_storage_config_settings' => [
      'uri_scheme' => 'public',
    ],
    'form_display' => 'image_image',
    'form_display_settings' => [
      'progress_indicator' => 'throbber',
      'preview_image_style' => 'thumbnail',
    ],
    'type' => 'image',
  ];

  public const text = [
    'display' => 'string',
    'form_display' => 'string_textfield',
    'type' => 'string',
  ];

  public const text_long = [
    'display' => 'basic_string',
    'form_display' => 'string_textarea',
    'type' => 'string_long',
  ];

  public const tags = [
    'cardinality' => -1,
    'display' => 'entity_reference_label',
    'field_config_settings' => [
      'handler' => 'default:taxonomy_term',
      'handler_settings' => [
        'target_bundles' => [
          'tags' => 'tags',
        ],
        'auto_create' => FALSE,
      ],
    ],
    'field_storage_config_settings' => [
      'target_type' => 'taxonomy_term',
    ],
    'form_display' => 'options_buttons',
    'form_display_settings' => [],
    'type' => 'entity_reference',
  ];

  public const view_ref = [
    'cardinality' => 1,
    'display' => 'viewsreference_formatter', // viewsreference_view
    'field_config_settings' => [],
    'field_storage_config_settings' => [
      'target_type' => 'viewsreference',
    ],
    'form_display' => 'viewsreference_select', // 'viewsreference_autocomplete',
    'form_display_settings' => [],
    'type' => 'viewsreference',
  ];

  /* -=--- */

  public const default_block_fields = [
    'field_tagline'    => DefaultFields::text,
    'field_subtitle'    => DefaultFields::text,
    'body'              => DefaultFields::body,

    'field_class_name'  => DefaultFields::text,
    'field_custom_css'  => DefaultFields::text_long,

    'field_icon'        => DefaultFields::file,
    'field_image_thumb' => DefaultFields::image,

    'field_link_text'   => DefaultFields::text,
    'field_link_url'    => DefaultFields::text,

  ];

  public const default_node_fields = [
    'body'              => DefaultFields::body,
    'field_image_hero'  => DefaultFields::image_url,
    'field_image_thumb' => DefaultFields::image,
    'field_tags'        => DefaultFields::tags,
  ];

}
