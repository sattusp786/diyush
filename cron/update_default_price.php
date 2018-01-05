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

function getOptionValuesData($product_id) {
	
	global $db;
	
    $query = $db->query("SELECT pov.product_option_value_id, pov.weight, pov.weight_prefix, od.name, ov.code, ov.sort_order, pov.product_option_id, po.required, o.option_id, pov.default FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN ".DB_PREFIX."product_option po ON pov.product_option_id = po.product_option_id LEFT JOIN " . DB_PREFIX . "option o ON ( pov.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON o.option_id=od.option_id LEFT JOIN " . DB_PREFIX . "option_value ov ON ( pov.option_value_id = ov.option_value_id) WHERE pov.product_id =" . $product_id . " AND (pov.`default`=1)");
    return $query->rows;
}

function calculatePrice($data){
	
	global $db;
	
	//Code added by Paul to calculate price starts...
	$final_price = 0;
	
	//Calculate Metal Price..
	$metal_price = 0;
	$metal_weight = $data['metal_weight'];
	
	$metal_price_per_gram = 0;
	$get_metal_price = $db->query("SELECT * FROM " . DB_PREFIX . "metal_price WHERE code = '".$data['Metal']."' ");
	if($get_metal_price->num_rows){
		$metal_price_per_gram = $get_metal_price->row['price'];
	}
	
	if($metal_price_per_gram > 0){
		$metal_price = $metal_price_per_gram * $metal_weight;
	}
	
	//Calculate Stone Price..
	$stone_price = 0;
	
	$stone_sql = "SELECT * FROM " . DB_PREFIX . "stone_price WHERE 1 ";
	
	if(isset($data['Stone Type']) && !empty($data['Stone Type'])){
		$stone_sql .= " AND stone = '".$data['Stone Type']."' ";
	}
	
	if(isset($data['Shape']) && !empty($data['Shape'])){
		$stone_sql .= " AND shape = '".$data['Shape']."' ";
	}
	
	if(isset($data['Carat']) && !empty($data['Carat'])){
		$stone_sql .= " AND '".($data['Carat']/100)."' between crt_from AND crt_to ";
	}
	
	if(isset($data['Clarity']) && !empty($data['Clarity'])){
		$stone_sql .= " AND clarity = '".$data['Clarity']."' ";
	}
	
	if(isset($data['Colour']) && !empty($data['Colour'])){
		$stone_sql .= " AND color = '".$data['Colour']."' ";
	}
	
	if(isset($data['Certificate']) && !empty($data['Certificate'])){
		$stone_sql .= " AND lab = '".$data['Certificate']."' ";
	}
	
	if(isset($data['Cut']) && !empty($data['Cut'])){
		$stone_sql .= " AND cut = '".$data['Cut']."' ";
	}
	
	if(isset($data['Polish']) && !empty($data['Polish'])){
		$stone_sql .= " AND polish = '".$data['Polish']."' ";
	}
	
	if(isset($data['Symmetry']) && !empty($data['Symmetry'])){
		$stone_sql .= " AND symmetry = '".$data['Symmetry']."' ";
	}
	
	if(isset($data['Fluo.']) && !empty($data['Fluo.'])){
		$stone_sql .= " AND fluorescence = '".$data['Fluo.']."' ";
	}
	
	if(isset($data['Intensity']) && !empty($data['Intensity'])){
		$stone_sql .= " AND intensity = '".$data['Intensity']."' ";
	}
	
	$stone_sql .= " ORDER BY stone_price_id DESC LIMIT 1 ";
	
	//echo $stone_sql;
	
	$get_stone_price = $db->query($stone_sql);
	
	if($get_stone_price->num_rows){
		$stone_price = $get_stone_price->row['sprice'];
	}
	
	$final_price = $metal_price + $stone_price;
	//Code added by Paul to calculate price ends...
	
	return $final_price;
}

$query_products = $db->query("SELECT * FROM " . DB_PREFIX . "product WHERE 1 ");
if($query_products->num_rows) {
	$i = 1;
	foreach($query_products->rows as $product){
		
		echo "\n Processing Model : " . $i++ . " : ". $product['model']."<br/>";
		
		$option_weight = 0;
        $default_options = array();
		$default_options = getOptionValuesData($product['product_id']);
		foreach ($default_options as $option) {
			if($option['default'] == '1' && $option['required'] == '1'){
				$default_options[$option['name']] = $option['code'];
				
				if ($option['weight_prefix'] == '+') {
					$option_weight += $option['weight'];
				} elseif ($option['weight_prefix'] == '-') {
					$option_weight -= $option['weight'];
				}
			}
		}
		
		$default_options['metal_weight'] = $product['weight'] + $option_weight;
		
		$product_price = calculatePrice($default_options);
		
		$update = $db->query("UPDATE " . DB_PREFIX . "product SET price = '".$product_price."' WHERE product_id = '".$product['product_id']."' ");
		$update = $db->query("UPDATE " . DB_PREFIX . "product SET price = '".$product_price."' WHERE product_id = '".$product['product_id']."' ");
	}

    echo 'Completed';
}


?>