

  /*
  **/
  public static function create_node_by($content_type, $config) {
    $nodes = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties([
        'type' => $content_type,
        'title' => $config['fields']['title'],
      ]);
    $node = reset($nodes);
    if (!$node) {
      $node = Node::create([
        'path' => [
          'alias' => $config['path'],
          'pathauto' => 0,
        ],
        'type' => $content_type,
        'title' => $config['fields']['title'],
        'status' => 1,
      ])->save();
    }

    $sections = $config['sections'];
    $outs = [];
    foreach($sections as $section) {

      $section = new Section( $section['type'], $section['config'] );
      foreach ($section['regions'] as $region => $region_c) {

        $extra = [
          'id' => "views_block:{$region_c['view_id']}",
          'label' => $region_c['label'],
          'label_display' => FALSE,
          'provider' => 'views',
        ];
        $component = new SectionComponent( Uuid::generate(), $region, $extra );
        $section->appendComponent($component);

      }
      $outs[] = $section;
    }
    $node->set('layout_builder__layout', $outs);
    $node->save();
  }
