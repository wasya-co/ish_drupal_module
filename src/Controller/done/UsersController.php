<?php

namespace Drupal\ish_drupal_module\Controller;

use Drupal\node\NodeInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Users controller.
**/
class UsersController extends ControllerBase {

  /**
   * index()
  **/
  // public function index() {
  //   return [
  //     '#markup' => "<h1>Locations Index !!!</h1>",
  //   ];
  // }

  public function dance_instructor_dashboard( Request $request ) {
    $build = [
      '#theme' => 'ish_dance_instructor_dashboard',
    ];
    return $build;
  }

  /**
   * dashboard()
  **/
  public function dashboard( Request $request ) {
    $youtube_form = \Drupal::formBuilder()->getForm('Drupal\ish_drupal_module\Form\ForYoutube');

    $menu_name = 'dashboard-menu';
    $parameters = new \Drupal\Core\Menu\MenuTreeParameters();
    $tree = \Drupal::menuTree()->load($menu_name, $parameters);
    $tree = \Drupal::menuTree()->transform($tree, [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ]);
    $menu_secondary = \Drupal::menuTree()->build($tree);
    // logg($menu_secondary, '$menu_secondary');


    $build = [
      '#theme'            => 'ish_users_dashboard',
      '#for_youtube_form' => $youtube_form,
      '#menu_secondary'   => $menu_secondary,
    ];
    return $build;
  }


  public function hiring_manager_dashboard( Request $request ) {
    $build = [
      '#theme' => 'ish_hiring_manager_dashboard',
    ];
    return $build;
  }

  /**
   * edit() - myself only
  **/
  public function my_edit( Request $request ) {

    $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());

    return [
      '#theme'   => 'ish_users_edit',
      '#user'    => $user,
      '#request' => $request,
    ];
  }

}
