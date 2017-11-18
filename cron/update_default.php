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

$default_arr = array(
            '1' => '1',
            '2' => '4',
            '3' => '8',
            '4' => '10',
            '5' => '30',
            '6' => '59',
            '7' => '63',
            '9' => '68',
            '10' => '81',
            '11' => '94',
            '12' => '105',
            '13' => '108',
            '14' => '119',
            '16' => '124',
            '17' => '130',
            '18' => '142',
            '19' => '160',
            '20' => '195',
            '22' => '172',
            '23' => '207'
    );

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

    global $db, $default_arr;

    $product_option_data = array();

    $product_option_query = $db->query("SELECT * FROM " . DB_PREFIX . "product_option po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '1' ORDER BY o.sort_order");

    foreach ($product_option_query->rows as $product_option) {
        $p = 0;
        $product_option_value_data = array();

        $product_option_value_query = $db->query("SELECT * FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' AND ovd.language_id = '1' ORDER BY ov.sort_order");

        foreach ($product_option_value_query->rows as $product_option_value) {
            
            if($product_option_value['option_value_id'] == '86' || $product_option_value['option_value_id'] == '99'){
                $update = $db->query("UPDATE " . DB_PREFIX . "product_option_value SET `default` = '1' WHERE product_option_value_id = '".$product_option_value['product_option_value_id']."' ");
                $p++;
                continue;
            }elseif(isset($default_arr[$product_option['option_id']]) && $product_option_value['option_value_id'] == $default_arr[$product_option['option_id']]){
                $update = $db->query("UPDATE " . DB_PREFIX . "product_option_value SET `default` = '1' WHERE product_option_value_id = '".$product_option_value['product_option_value_id']."' ");
                $p++;
                continue;
            }
        }
        
        if($product_option_value_query->num_rows && $p == 0){
             $update = $db->query("UPDATE " . DB_PREFIX . "product_option_value SET `default` = '1' WHERE product_option_value_id = '".$product_option_value['product_option_value_id']."' ");
        }
    }
}

$query_products = $db->query("SELECT * FROM " . DB_PREFIX . "product WHERE 1 ");
if($query_products->num_rows) {
	foreach($query_products->rows as $product){
		
        $product_options = array();
		$product_options = updateProductOptions($product['product_id']);
	}

    echo 'Completed';
}


?>