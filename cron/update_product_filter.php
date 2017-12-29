<?php
set_time_limit(0);
ini_set('memory_limit', '-1');
ini_set('max_execution_time', 3600);

# Declaration of rest variables
clearstatcache();
$start_time = microtime(true);
$debug = false;
date_default_timezone_set("Europe/London");

require_once(dirname(__FILE__) . '/../config.php');
require_once(DIR_SYSTEM . 'library/db/mysqli.php');
require_once(DIR_SYSTEM . 'library/db.php');

$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);

$metal_arr = array(1,2,3,4,5,6,7);

function getConfig($key,$store_id) {
    // Settings
	global $db;
	
    $setting = array();
    $query = $db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE `code` = 'config' AND `key`='" . $db->escape($key) . "' AND store_id = '".(int)$store_id."' ");
    if ($query->num_rows) {
		if (@unserialize($query->row['value']) !== false) {
            return unserialize($query->row['value']);
        } else {
            return $query->row['value'];
        }
    }
    return false;
}

function getFilters(){
	global $db;
	
	$filters = array();
	$query = $db->query("SELECT * FROM " . DB_PREFIX . "filter_description WHERE language_id='1' AND filter_group_id IN (4,5,6) ");
	if ($query->num_rows) {
		foreach($query->rows as $filter){
			$filters[$filter['filter_id']] = $filter['name'];
		}
    }
	return $filters;
}

$filters = getFilters();

function updateProductFilters($product_id, $product_name) {

    global $db, $metal_arr, $filters;

	$truncate = $db->query("DELETE FROM " . DB_PREFIX . "product_filter WHERE product_id='".$product_id."' ");
	
	foreach($metal_arr as $metal_id){
		$insert = $db->query("INSERT INTO " . DB_PREFIX . "product_filter SET product_id = '".$product_id."', filter_id = '".$metal_id."' ");
	}
	
	if(!empty($filters)){
		foreach($filters as $filter_id => $filter_name){
			if(stripos($product_name, str_ireplace(array("Rings","Earrings","Pendants","Bracelet","Setting"),"",$filter_name)) !== false){
				$insert = $db->query("INSERT INTO " . DB_PREFIX . "product_filter SET product_id = '".$product_id."', filter_id = '".$filter_id."' ");
			}
		}
	}
}

$query_products = $db->query("SELECT product_id, name FROM " . DB_PREFIX . "product_description WHERE language_id=1 ");
if($query_products->num_rows) {
	foreach($query_products->rows as $product){
		
        $product_filters = array();
		$product_filters = updateProductFilters($product['product_id'],$product['name']);
	}

    echo 'Completed';
}


?>