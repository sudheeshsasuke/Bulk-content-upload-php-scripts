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
		return $numbers;
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

// Set path to CSV file
$csvFile = 'numbers.csv';
 
$numbers = readCSV($csvFile);

//echo '<pre>' . print_r($numbers, TRUE) . '</pre>';

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

