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

$metal_option_arr = array(116,117,118,119,120,121);

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


function updateProductOptions($product_id) {

    global $db, $metal_option_arr;

	$truncate_opt = $db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id='".$product_id."' AND option_id = '14' ");
	$truncate_opt_val = $db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id='".$product_id."' AND option_id = '14' ");
	
	$insert = $db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '".$product_id."', option_id = '14', required='1' ");
		
	$product_option_id = $db->getLastId();
	
	foreach($metal_option_arr as $metal_id){
		if($metal_id == '119'){
			$default_val = '1';
		} else {
			$default_val = '0';
		}
		$insert = $db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '".$product_option_id."', product_id = '".$product_id."', option_id = '14', option_value_id = '".$metal_id."', quantity = '100', subtract='0', `default`='".$default_val."' ");
		
	}
}

$query_products = $db->query("SELECT product_id FROM " . DB_PREFIX . "product WHERE 1 ");
if($query_products->num_rows) {
	foreach($query_products->rows as $product){
		
        $product_options = array();
		$product_options = updateProductOptions($product['product_id']);
	}

    echo 'Completed';
}


?>