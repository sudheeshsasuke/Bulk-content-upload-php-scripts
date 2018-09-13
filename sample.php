<?php
set_time_limit(0);
/**
 * @file
 * The PHP page that serves all page requests on a Drupal installation.
 *
 * The routines here dispatch control to the appropriate handler, which then
 * prints the appropriate page.
 *
 * All Drupal code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt.
 */

/**
 * Root directory of Drupal installation.
 */
define('DRUPAL_ROOT', getcwd());

require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
// menu_execute_active_handler();

function create_node($node_type) {
  global $user;
  
  // entity_create replaces the procedural steps in the first example of
  // creating a new object $node and setting its 'type' and uid property
  $values = array(
    'type' => $node_type,
    'uid' => $user->uid,
    'status' => 1,
    'comment' => 1,
    'promote' => 0,
  );
  $entity = entity_create('node', $values);
  // The entity is now created, but we have not yet simplified use of it.
  // Now create an entity_metadata_wrapper around the new node entity
  // to make getting and setting values easier
  $ewrapper = entity_metadata_wrapper('node', $entity);

  // Using the wrapper, we do not have to worry about telling Drupal
  // what language we are using. The Entity API handles that for us.
  $ewrapper->title->set('YOUR TITLE');

  // // Setting the body is a bit different from other properties or fields
  // // because the body can have both its complete value and its
  // // summary
  // $my_body_content = 'A bunch of text about things that interest me';
  // $ewrapper->body->set(array('value' => $my_body_content));
  // $ewrapper->body->summary->set('Things that interest me');

  // // Setting the value of an entity reference field only requires passing
  // // the entity id (e.g., nid) of the entity to which you want to refer
  // // The nid 15 here is just an example.
  // $ref_nid = 15;
  // // Note that the entity id (e.g., nid) must be passed as an integer not a
  // // string
  // $ewrapper->field_my_entity_ref->set(intval($ref_nid));

  // // Entity API cannot set date field values so the 'old' method must
  // // be used
  // $my_date = new DateTime('January 1, 2013');
  // $entity->field_my_date[LANGUAGE_NONE][0] = array(
  //   'value' => date_format($my_date, 'Y-m-d'),
  //   'timezone' => 'UTC',
  //   'timezone_db' => 'UTC',
  // );

  // Now just save the wrapper and the entity
  // There is some suggestion that the 'true' argument is necessary to
  // the entity save method to circumvent a bug in Entity API. If there is
  // such a bug, it almost certainly will get fixed, so make sure to check.
  $ewrapper->save();
}

// create_node('product_serial_numbers');

// Getting the node details of type story
$results = db_select('node', 'n')
  ->condition('type', 'product_serial_numbers')
  ->fields('n')
  ->execute()
  ->fetchAll();

foreach ($results as $result) {
  $nid = $result->nid;
  $node = node_load($nid);
  echo '<pre>' . print_r($node, TRUE) . '</pre>';
}
exit;