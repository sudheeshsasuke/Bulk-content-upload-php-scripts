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

function readCSV($csvFile){
	if (($handle = fopen($csvFile, 'r')) !== FALSE) {
		fgetcsv($handle);
		// Error count variable.
		$errors = 0;
		$serial_node_details_count = 0;
		$serial_node_details = [];
		$numbers = [];
		while (($data = fgetcsv($handle, 0, ',')) !== FALSE) {
			if ($serial_node_details_count < 3) {
				$serial_node_details[] = $data[0];
				$serial_node_details_count++;
			}
			else {
				$numbers[] = str_replace($serial_node_details[2], "", $data[0]);
			}
		}
		//echo '<pre>' . print_r($numbers, TRUE) . '</pre>';
    fclose($handle);
    $csv_data['numbers'] = $numbers;
    $csv_data['node_title'] = $serial_node_details[0];
    $csv_data['product_title'] = $serial_node_details[1];
    $csv_data['prefix'] = $serial_node_details[2];
		return $csv_data;
	}
}

function find_end_value($i, $limit, $index, $numbers) {
	//$count = 0;
	for ($j = $i; $j < $limit; $j++) {
		if ($j < $limit - 1) {
			if ($numbers[$j] + 1 != $numbers[$j+1]) {
				$index += 1;
				$count += 1;
				$data['end_value'] = $numbers[$j];
				$data['index'] = $index;
				$data['count'] = $count;
				return $data;
			}
			else {
				$count++;
			}
		}
		else {
			$index++;
			$count++;
			$data['end_value'] = $numbers[$j];
			$data['index'] = $index;
			$data['count'] = $count;
			return $data;
		}
	}
}

function edit_node($node_title, $product_id, $prefix, $serial_number_set) {
  // Load the node
  $node_entities = entity_load('node', FALSE, array('type' => 'product_serial_numbers', 'title' => $node_title));
  $keys = array_keys($node_entities);
  $latest_entity_index = $keys[count($keys) - 1];
  $node_entity = $node_entities[$latest_entity_index];
  $ewrapper = entity_metadata_wrapper('node', $node_entity);
  $ewrapper->field_product->set($product_id);
  $ewrapper->save();
  $en = $ewrapper->value();

  $count = 0;
  $limit = count($serial_number_set);
  for ($i = 0; $i < $limit; $i++) {
    $node = node_load($en->nid);
    $fc_item = entity_create('field_collection_item', array('field_name' => 'field_serial_number_range'));
    $fc_item->setHostEntity('node', $node);
    $fc_wrapper = entity_metadata_wrapper('field_collection_item', $fc_item);
    $fc_wrapper->field_prefix->set($prefix);
    $fc_wrapper->field_starting_number->set($serial_number_set[$i]['start']);
    $fc_wrapper->field_ending_number->set($serial_number_set[$i]['end']);
    node_save($node);
    $count++;
  }
  echo "<h1>". $count . " serial number sets has been updated to " . $node_title . "</h1>";
}

// Set path to CSV file
$csvFile = 'numbers.csv';
 
$csv_data = readCSV($csvFile);

$numbers = $csv_data['numbers'];
$node_title = $csv_data['node_title'];
$product_title = $csv_data['product_title'];
$prefix = $csv_data['prefix'];

// Getting the node details of type products
$products_results = db_select('node', 'n')
  ->condition('type', 'product')
  ->condition('title', $product_title)
  ->fields('n')
  ->execute()
  ->fetchAll();

// Get the latest node from results.
$pid = $products_results[count($products_results) - 1]->nid;

$limit = count($numbers);
$serial_number_set = [];
$index = 0;
$count = 0;
for ($i = 0; $i < $limit; $i += $count) {
	$count = 0;
	$serial_number_set[$index]['start'] = $numbers[$i];
	$data = find_end_value($i, $limit, $index, $numbers);
	$serial_number_set[$index]['end'] = $data['end_value'];
	$index = $data['index'];
	$count = $data['count'];
}

edit_node($node_title, $pid, $prefix, $serial_number_set);