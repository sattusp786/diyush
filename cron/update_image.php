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

function getProductOptions($product_id) {

    global $db;

    $metal = 'ww';
    $stonetype = 'di';
    $shape = 'rnd';
    $carat = '0.0000-1.0000';


    $product_option_data = array();

    $product_option_query = $db->query("SELECT * FROM " . DB_PREFIX . "product_option po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '1' ORDER BY o.sort_order");

    foreach ($product_option_query->rows as $product_option) {
        $product_option_value_data = array();

        $product_option_value_query = $db->query("SELECT * FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' AND ovd.language_id = '1' ORDER BY ov.sort_order");

        foreach ($product_option_value_query->rows as $product_option_value) {
        	//Metal
            if($product_option['option_id'] == '14' && $product_option_value['default'] == '1'){
            	if(stripos($product_option_value['name'],'Rose') !== false){
            		$metal = 'rr';
            	} elseif(stripos($product_option_value['name'],'Yellow') !== false){
            		$metal = 'yy';
            	} else {
            		$metal = 'ww';
            	}
            }
            //Stonetype
            if($product_option['option_id'] == '22' && $product_option_value['default'] == '1'){
            	if(stripos($product_option_value['name'],'Ruby') !== false){
            		$stonetype = 'rb';
            	} elseif(stripos($product_option_value['name'],'Emerald') !== false){
            		$stonetype = 'em';
            	} elseif(stripos($product_option_value['name'],'Blue Sapphire') !== false){
            		$stonetype = 'bs';
            	} elseif(stripos($product_option_value['name'],'Pink Sapphire') !== false){
            		$stonetype = 'ps';
            	} elseif(stripos($product_option_value['name'],'Black Diamond') !== false){
            		$stonetype = 'bd';
            	} elseif(stripos($product_option_value['name'],'Diamond') !== false){
            		$stonetype = 'di';
            	} else {
            		$stonetype = 'di';
            	}
            }
            //Shape
            if($product_option['option_id'] == '20' && $product_option_value['default'] == '1'){
            	if(stripos($product_option_value['name'],'Radiant') !== false){
            		$shape = 'rad';
            	} elseif(stripos($product_option_value['name'],'Cushion') !== false){
            		$shape = 'cus';
            	} elseif(stripos($product_option_value['name'],'Marquise') !== false){
            		$shape = 'mqs';
            	} elseif(stripos($product_option_value['name'],'Heart') !== false){
            		$shape = 'hrt';
            	} elseif(stripos($product_option_value['name'],'Pear') !== false){
            		$shape = 'per';
            	} elseif(stripos($product_option_value['name'],'Oval') !== false){
            		$shape = 'ovl';
            	} elseif(stripos($product_option_value['name'],'Asscher') !== false){
            		$shape = 'asc';
            	} elseif(stripos($product_option_value['name'],'Emerald') !== false){
            		$shape = 'emr';
            	} elseif(stripos($product_option_value['name'],'Princess') !== false){
            		$shape = 'prn';
            	} elseif(stripos($product_option_value['name'],'Round') !== false){
            		$shape = 'rnd';
            	} else {
            		$shape = 'rnd';
            	}
            }
            //Carat
            if($product_option['option_id'] == '5' && $product_option_value['default'] == '1'){
            	if($product_option_value['name'] > 0 &&  $product_option_value['name'] <= 1){
            		$carat = '0.0000-1.0000';
            	} elseif($product_option_value['name'] > 1 &&  $product_option_value['name'] <= 2){
            		$carat = '1.0000-2.0000';
            	} elseif($product_option_value['name'] > 2 &&  $product_option_value['name'] <= 3){
            		$carat = '2.0000-3.0000';
            	} else {
            		$carat = '0.0000-1.0000';
            	}
            }

        }
    }

    $product_option_data['metal'] = $metal;
    $product_option_data['stonetype'] = $stonetype;
    $product_option_data['shape'] = $shape;
    $product_option_data['carat'] = $carat;

    return $product_option_data;
}


$carrat_arr = array('0.0000-1.0000','1.0000-2.0000','2.0000-3.0000','0.0000-3.0000');

$query_products = $db->query("SELECT * FROM " . DB_PREFIX . "product WHERE 1");
if($query_products->num_rows) {
	foreach($query_products->rows as $product){
		
		$product_options = getProductOptions($product['product_id']);

		$image_path = 'product/'.strtolower($product['sku']).'/front/'.$product_options['metal'].'/'.$product_options['stonetype'].'/'.$product_options['shape'].'/'.$product_options['carat'].'/0001.jpg';
	
		$new_path = DIR_IMAGE.$image_path;
		if(!file_exists($new_path)){
			foreach($carrat_arr as $carat_ar){
				$test_path = DIR_IMAGE.'product/'.strtolower($product['sku']).'/front/'.$product_options['metal'].'/'.$product_options['stonetype'].'/'.$product_options['shape'].'/'.$carat_ar.'/0001.jpg';
				if(file_exists($test_path)){
					$image_path = 'product/'.strtolower($product['sku']).'/front/'.$product_options['metal'].'/'.$product_options['stonetype'].'/'.$product_options['shape'].'/'.$carat_ar.'/0001.jpg';
					break;
				}
			}
		}
		
		$update_image = $db->query("UPDATE " . DB_PREFIX . "product SET `image` = '".$image_path."' WHERE product_id = '".$product['product_id']."' ");

	}
}

echo 'Completed';


?>