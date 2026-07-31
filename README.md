
= ish_drupal_module 2.0.0 =

This module (version 2-major) provides utilities and methods for administerig marketing sites. It provides shared styling, as well as multiple javascript and style libraries. It is meant to be used with a bootstrap_barrio_subtheme.

It allows automatic content creation. It allows automatic content updating. It formalizes some content types, fields and block types, although you are more than welcome to diverge from the recomendation. The structure is pretty flexible, although naming is very opinionated in order to keep everything nicely glued together.

The module is under active development, so everything is subject to change. The below documentation of functionality and features should be up to date. You can also see the readme at https://github.com/wasya-co/ish_drupal_module/tree/2.0.0 in case that one is the most recent.

Requirements: php 8, drupal 10. It should Just Work (TM) on Drupal 9 - please submit a feature request if you need support for Drupal 9.

Note: This module assumes that you are an advanced user. You are encouraged to overwrite content type definitions, update_xxxx functions and configuration yml files. You may end up doing a lot of "drush ... updb" to re-run methods that update structure and content. This module is meant to update content on sites that are live. So... with great power comes great responsibility.

See changelog.txt , see doc/ and the development-grade README.txt

= Install =

Install the module. Then, review ish_drupal_module.install keep what you want, discard what you don't want, then run 'drush ... updb'. A lot of the available functionality is available at /admin/ish_drupal_module

== Configure ==
  * enable or disable fancy_header, it's up to you.

= Use =

  # == Content Types ==

    content types: advanced_page, issue, slide

    The Issue is different from AdvancedPage in that the Issue starts rendering at the top-most of the page, and the fancy header overlays whatever is the first element of the Issue. Issue does not display page title. This way you can create elegant home pages where the header overlays the content. However on any AdvancedPage, the header does not overlay the content, there are no overlaying elements, the page title is present, and therefore everything is clearly visible and readable. You want to use AdvancedPage most of the time, unless you specifically need an overlaying, absolute header.

    You can create any new content type with expected fields (and field sets):

      \Drupal\ish_drupal_module\Config\ContentTypeConfig::create_content_type('directory_item',
        \Drupal\ish_drupal_module\Config\DefaultFields::advanced_node_fields);


  # == Fields ==

    fields:
      * field_image_thumb
      * field_image_hero
      * field_class_name
      * field_subtitle

  # == block types ==

    === advanced_block ===
    === section_hero_video ===
    === section_callout_parallax ===

  # == Taxonomy ==

    The distinction between tags and tags_contrib is that the first are admin-level and invisible, whereas the latter are displayed as common tags.

    The naming convention for taxonomies is: tags_contrib, tags_issue, tags_city so e.g. the taxonomy listing cities has the word city (singular) after word tags (plural), and these are joined by an underscore. (city_tags would be something else, sounds like it would be some tags relating to this specific city. In contrast, tags_city is independent of any city.)

  # == CSS ==

    === Card ===

    A bordered, padded container with a different background.

      .Card

    === Mobile vs Desktop ===

      The following classes control visibility at 768px breakpoint.

        .only-mobile
        .no-mobile

    === Slider ===

      * https://ganlanyuan.github.io/tiny-slider/demo/#non-loop_wrapper
      * https://ganlanyuan.github.io/tiny-slider/

      The slider class .my-slider should be on a ul, with li's as immediate children. The slides are immediate children of .my-slider and any number of config classes can be added to the *wrapper* parent of the slider:

        /* scss syntax */
        .autowidth,
        .no-navigation {
          .my-slider {}
        }

    === Animations ===

      .fade-up
      .slide-right

  == Sections ==

  After installing and updating the module, you can see all sections with thumbnails, usage and descriptions of fields at /admin/ish_drupal_module/sections . However, the list in this README may be more up to date with the edge development branch.

  === Section Hero Video ===
    The body accepts full html, so you can copy-paste the design there.

    The autoplay checkbox is meant for development environments, so that the video isn't annoying.

    Currently, the section accepts an image file for mobile display. In the future we can improve the functionality and capture the first frame of the video, as the image, automatically. This will be implemented as a feature request - please submit an offer! Alternatively, if you would like to volunteer in developing this particular piece, please submit a proposal!

  === Section Callout Parallax ===
    The fields are:

      'body' => [],
      'field_class_name' => [],
      'field_custom_css' => [],
      'field_image_bg' => [],
      'field_image_thumb' => [],
      'field_title' => [],
      'field_link_text' => [],
      'field_link_url' => [],

    And you can custom-style each block by means of the field_class_name.
