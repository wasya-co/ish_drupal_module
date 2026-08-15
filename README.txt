

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

    git tag -a v2.3.3 -m "Release v2.3.3"
    git push origin v2.3.3

  * update version in composer.json

  * ssh into node, login to docker and run:
  * the container_name is piousbox_com, service is app_*

    composer update wasya-co/ish_drupal_module \
      --ignore-platform-req=ext-bcmath --ignore-platform-req=ext-gd ;
    drush cr

