
drush ev '
$nids = \Drupal::entityQuery("node")
  ->accessCheck(FALSE)
  ->condition("type", "article")
  ->condition("uid", 257)
  ->condition("status", 0)
  ->exists("body")
  ->execute();

$nodes = \Drupal\node\Entity\Node::loadMultiple($nids);

foreach ($nodes as $node) {
  if ($body = $node->get("body")->first()) {
    $body->summary = " ";
    $node->setNewRevision(FALSE);
    $node->save();
    print "Updated node {$node->id()}\n";
  }
}

print "Done. Updated " . count($nodes) . " article(s).\n";
'

