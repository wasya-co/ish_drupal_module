
= About =

Version 1.2.3 is for drupal 9 and piousbox_com.
Version 2.0.0 is for drupal 10 and col-re, rebel-cycles, and everything else going forwrad.

Also see changelog.txt

= Develop =

  ./vendor/drush/drush/drush cc router

  ffmpeg -i motoshop-1.960x506.mp4 -frames:v 1 frame.jpg
  ffmpeg -i $inn -frames:v 1 $inn.frame.jpg

  drush -l col-re.local php:eval "echo \Drupal::keyValue('system.schema')->get('ish_drupal_module'); "

  drush -l col-re.local php:eval "\Drupal::keyValue('system.schema')->set('ish_drupal_module', 9000); "

  drush -l col-re.local updb -y

  drush -l rebelcycles10.local config:export

  drush
  SELECT collection, name, value
    FROM key_value
    WHERE collection = 'system.schema'
      AND name = 'ish_drupal_module';

= Test =

  export PATH="$PATH:/var/www/html/vendor/bin"

  composer require --dev phpunit/phpunit
  composer require --dev phpunit/phpunit:^9.5 --with-all-dependencies
  composer require --dev drupal/core-dev
  composer require --dev behat/mink:1.8.0 --with-all-dependencies
  composer require --dev symfony/phpunit-bridge
  composer require --dev phpspec/prophecy-phpunit
  composer require --dev behat/mink-browserkit-driver
  composer require --dev drupal/core-dev
  composer require --dev drupal/core-dev --update-with-all-dependencies

  phpunit --testsuite unit --filter CurrentUserBlock
  ../vendor/bin/phpunit        -c ../phpunit.xml modules/ish_drupal_module/tests/src/Functional/CurrentUserBlockTest.php
  ../../../vendor/bin/phpunit  -c ./phpunit.xml  tests/src/Functional/WorklogsControllerTest.php

  == this test works ==

    ./vendor/bin/phpunit -c phpunit.xml  -d memory_limit=1G web/modules/ish_drupal_module/tests/src/Functional/WorklogsControllerTest.php --debug

    $response = $this->getSession()->getDriver()->getClient()->request('GET', '/worklogs/2025a', [], [], ['max_redirects' => 0]);
    echo('+++ $response');
    var_dump($response);

= Deploy =

  * tag, push to wasya-co remote:

    git tag -a v2.1.3 -m "Release v2.1.3"
    git push origin v2.1.3

  * update version in composer.json

  * ssh into node, login to docker and run:
  * the container_name is piousbox_com, service is app_*

    composer update wasya-co/ish_drupal_module \
      --ignore-platform-req=ext-bcmath --ignore-platform-req=ext-gd ;
    drush cr

= Use =

  == css ==

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
