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

$mapping = getOptionValueMapping();
$multi_mapping = getOptionValueMapping();

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

function calculatePrice($data) {
		
	global $db, $mapping, $multi_mapping;
	
	$default_gravity = 15.50;
	//Code added by Paul to calculate price starts...
	$final_price = 0;
	
	//Calculate Metal Price..
	$metal_price = 0;
	$metal_weight = $data['metal_weight'];		
	
	if (isset($data['Metal']) && !empty($data['Metal']))
	{
		$metal_sql = "SELECT price, gravity, code FROM " . DB_PREFIX . "metal_price WHERE  code = '" . $db->escape($data['Metal']) . "'";

		$metal_query = $db->query($metal_sql);
		if ($metal_query->num_rows)
		{
			foreach($metal_query->rows AS $metal_result)
			{
				if ($data['Metal'] == $metal_result['code'])
				{
					$metal_weight = ($metal_weight / $default_gravity) * $metal_result['gravity'];
					$metal_price+= $metal_result['price'] * $metal_weight;
				}
			}
		}
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
		if(isset($mapping[$data['Certificate']][$data['Clarity']])){
			$stone_sql .= " AND clarity IN (" . $mapping[$data['Certificate']][$data['Clarity']] . ") ";
		} else {
			$stone_sql .= " AND clarity = '".$data['Clarity']."' ";
		}
	}
	
	if(isset($data['Colour']) && !empty($data['Colour'])){
		if(isset($mapping[$data['Certificate']][$data['Colour']])){
			$stone_sql .= " AND color IN (" . $mapping[$data['Certificate']][$data['Colour']] . ") ";
		} else {
			$stone_sql .= " AND color = '".$data['Colour']."' ";
		}
	}
	
	if(isset($data['Certificate']) && !empty($data['Certificate'])){
		if(isset($mapping[$data['Certificate']][$data['Certificate']])){
			$stone_sql .= " AND lab IN (" . $mapping[$data['Certificate']][$data['Certificate']] . ") ";
		} else {
			$stone_sql .= " AND lab = '".$data['Certificate']."' ";
		}
	}
	
	if(isset($data['Cut']) && !empty($data['Cut'])){
		if(isset($mapping[$data['Certificate']][$data['Cut']])){
			$stone_sql .= " AND cut IN (" . $mapping[$data['Certificate']][$data['Cut']] . ") ";
		} else {
			$stone_sql .= " AND cut = '".$data['Cut']."' ";
		}
	}
	
	if(isset($data['Polish']) && !empty($data['Polish'])){
		if(isset($mapping[$data['Certificate']][$data['Polish']])){
			$stone_sql .= " AND polish IN (" . $mapping[$data['Certificate']][$data['Polish']] . ") ";
		} else {
			$stone_sql .= " AND polish = '".$data['Polish']."' ";
		}
	}
	
	if(isset($data['Symmetry']) && !empty($data['Symmetry'])){
		if(isset($mapping[$data['Certificate']][$data['Symmetry']])){
			$stone_sql .= " AND symmetry IN (" . $mapping[$data['Certificate']][$data['Symmetry']] . ") ";
		} else {
			$stone_sql .= " AND symmetry = '".$data['Symmetry']."' ";
		}
	}
	
	if(isset($data['Fluo.']) && !empty($data['Fluo.'])){
		if(isset($mapping[$data['Certificate']][$data['Fluo.']])){
			$stone_sql .= " AND fluorescence IN (" . $mapping[$data['Certificate']][$data['Fluo.']] . ") ";
		} else {
			$stone_sql .= " AND fluorescence = '".$data['Fluo.']."' ";
		}
	}
	
	if(isset($data['Intensity']) && !empty($data['Intensity'])){
		if(isset($mapping[$data['Certificate']][$data['Intensity']])){
			$stone_sql .= " AND intensity IN (" . $mapping[$data['Certificate']][$data['Intensity']] . ") ";
		} else {
			$stone_sql .= " AND intensity = '".$data['Intensity']."' ";
		}
	}
	
	$stone_sql .= " ORDER BY sprice ASC";
		
	if(isset($mapping[$data['Certificate']]['position'])){
		$position = $mapping[$data['Certificate']]['position'];
	} else {
		$position = 2;
	}
	
	$position = (isset($position))?($position-1):1;
	$stone_sql .= " limit $position,1";
	//echo $stone_sql;
	
	$get_stone_price = $db->query($stone_sql);
	
	if($get_stone_price->num_rows){
		$sprice = $get_stone_price->row['sprice'];
		if (isset($mapping[$data['Certificate']]['markup'])) {
			$markup = explode('|',$mapping[$data['Certificate']]['markup']);
			if(isset($markup[0]) && $markup[0] > 0){
				$sprice = $sprice * $markup[0] + $markup[1];
			} elseif(isset($markup[1])) {
				$sprice = $sprice + $markup[1];
			}
		}
		$stone_price = $sprice;
	}
	
	//Calculate Side Stone Price..
	$sidestone_price = 0;
	if(isset($data['side_stone']) && !empty($data['side_stone'])){
		foreach($data['side_stone'] as $side){
			
			$sider_stone = (isset($side['stone']) && !empty($side['stone'])) ? $side['stone'] : $data['Stone Type'];
			$sider_shape = (isset($side['shape']) && !empty($side['shape'])) ? $side['shape'] : $data['Shape'];
			$sider_carat = (isset($side['carat']) && !empty($side['carat'])) ? $side['carat'] : $data['Carat'];
			$sider_color = (isset($side['color']) && !empty($side['color'])) ? $side['color'] : $data['Colour'];
			$sider_clarity = (isset($side['clarity']) && !empty($side['clarity'])) ? $side['clarity'] : $data['Clarity'];
			$sider_lab = (isset($side['lab']) && !empty($side['lab'])) ? $side['lab'] : $data['Certificate'];
			$sider_pieces = (isset($side['pieces']) && !empty($side['pieces'])) ? $side['pieces'] : 1;
			
			$sider_color = isset($multi_mapping[$sider_lab][$sider_color]) ? $multi_mapping[$sider_lab][$sider_color] : "'".$sider_color."'";
			$sider_clarity = isset($multi_mapping[$sider_lab][$sider_clarity]) ? $multi_mapping[$sider_lab][$sider_clarity] : "'".$sider_clarity."'";
			$sider_lab = isset($multi_mapping[$sider_lab][$sider_lab]) ? $multi_mapping[$sider_lab][$sider_lab] : "'".$sider_lab."'";
			
			$sidestone_sql = "SELECT * FROM ".DB_PREFIX."stone_price WHERE stone='".$sider_stone."' AND shape='".$sider_shape."' AND ".$sider_carat." between crt_from AND crt_to AND clarity IN (" . $sider_clarity . ") AND color IN (" . $sider_color . ") AND lab IN (" . $sider_lab . ") ORDER BY mprice ASC ";
			
			if(isset($multi_mapping[$sider_lab]['position'])){
				$side_position = $multi_mapping[$sider_lab]['position'];
			} else {
				$side_position = 2;
			}
			
			$side_position = (isset($side_position))?($side_position-1):1;
			$sidestone_sql .= " limit $side_position,1";
				
			$get_sidestone_price = $db->query($sidestone_sql);
			
			if($get_sidestone_price->num_rows){
				$sidestone_price += $get_sidestone_price->row['mprice'] * ($sider_carat) * $sider_pieces;
				if (isset($multi_mapping[$sider_lab]['markup'])) {
					$side_markup = explode('|',$multi_mapping[$sider_lab]['markup']);
					if(isset($side_markup[0]) && $side_markup[0] > 0){
						$sidestone_price += $sidestone_price * $side_markup[0] + $side_markup[1];
					} elseif(isset($side_markup[1])) {
						$sidestone_price += $sidestone_price + $side_markup[1];
					}
				}
			}
		}
	}
	
	//Calculate Multi Stone Price..
	$multistone_price = 0;
	if(isset($data['multi_stone']) && !empty($data['multi_stone'])){
		foreach($data['multi_stone'] as $multi){
			
			$multir_stone = (isset($multi['stone']) && !empty($multi['stone'])) ? $multi['stone'] : $data['Stone Type'];
			$multir_shape = (isset($multi['shape']) && !empty($multi['shape'])) ? $multi['shape'] : $data['Shape'];
			$multir_carat = (isset($multi['carat']) && !empty($multi['carat'])) ? $multi['carat'] : $data['Carat'];
			$multir_color = (isset($multi['color']) && !empty($multi['color'])) ? $multi['color'] : $data['Colour'];
			$multir_clarity = (isset($multi['clarity']) && !empty($multi['clarity'])) ? $multi['clarity'] : $data['Clarity'];
			$multir_lab = (isset($multi['lab']) && !empty($multi['lab'])) ? $multi['lab'] : $data['Certificate'];
			$multir_pieces = (isset($multi['pieces']) && !empty($multi['pieces'])) ? $multi['pieces'] : 1;
			
			$multir_color = isset($multi_mapping[$multir_lab][$multir_color]) ? $multi_mapping[$multir_lab][$multir_color] : "'".$multir_color."'";
			$multir_clarity = isset($multi_mapping[$multir_lab][$multir_clarity]) ? $multi_mapping[$multir_lab][$multir_clarity] : "'".$multir_clarity."'";
			$multir_lab = isset($multi_mapping[$multir_lab][$multir_lab]) ? $multi_mapping[$multir_lab][$multir_lab] : "'".$multir_lab."'";
			
			$multistone_sql = "SELECT * FROM ".DB_PREFIX."stone_price WHERE stone='".$multir_stone."' AND shape='".$multir_shape."' AND weight >= ".$multir_carat." AND clarity IN (" . $multir_clarity . ") AND color IN (" . $multir_color . ") AND lab IN (" . $multir_lab . ") ";
				
			if($multir_pieces == '1'){
				$multistone_sql .= " ORDER BY sprice ASC ";
			} else {
				$multistone_sql .= " ORDER BY mprice ASC ";
			}
			
			if(isset($multi_mapping[$multir_lab]['position'])){
				$multi_position = $multi_mapping[$multir_lab]['position'];
			} else {
				$multi_position = 2;
			}
			
			$multi_position = (isset($multi_position))?($multi_position-1):1;
			$multistone_sql .= " limit $multi_position,1";
			
			$get_multistone_price = $db->query($multistone_sql);
			
			if($get_multistone_price->num_rows){
				if($multir_pieces == '1'){
					$multistone_price += $get_multistone_price->row['sprice'];
				} else {
					$multistone_price += $get_multistone_price->row['mprice'] * ($multir_carat) * $multir_pieces;
				}
				
				if (isset($multi_mapping[$multir_lab]['markup'])) {
					$multi_markup = explode('|',$multi_mapping[$multir_lab]['markup']);
					if(isset($multi_markup[0]) && $multi_markup[0] > 0){
						$multistone_price += $multistone_price * $multi_markup[0] + $multi_markup[1];
					} elseif(isset($multi_markup[1])) {
						$multistone_price += $multistone_price + $multi_markup[1];
					}
				}
			}
		}
	}
	
	
	$no_price = '0';
	$all_stone_price = $stone_price + $sidestone_price + $multistone_price;
	if($metal_price == 0 || $all_stone_price == 0){
		$no_price = '1';
	}
	
	$final_price = $metal_price + $stone_price + $sidestone_price + $multistone_price;
	//Code added by Paul to calculate price ends...
	
	//Add Product Markup..
	if($data['product_markup'] != ''){
		$final_price = addProductMarkup($final_price, $data['product_markup']);
	}
	
	return $final_price;
}

function addProductMarkup($price, $code){
	
	global $db;
	
	if(!empty($code)) {

		$markupuery = $db->query("SELECT * FROM " . DB_PREFIX . "markup_product WHERE code = '" . $db->escape($code) . "' AND status = '1' ");
		
		if ($markupuery->num_rows)
		{
			if ($markupuery->row['markup'])
			{
				$markup_arr = explode("|",$markupuery->row['markup']);
				$markup_per = 0;
				$markup_fix = 0;
				if(isset($markup_arr[0]) && !empty($markup_arr[0])){
					$markup_per = $markup_arr[0];
				}
				if(isset($markup_arr[1]) && !empty($markup_arr[1])){
					$markup_fix = $markup_arr[1];
				}
				if($markup_per > 0){
					$price += $price * ($markup_per/100) + $markup_fix;
				} elseif($markup_fix > 0) {
					$price += $markup_fix;
				}
			}
		}
	}
	
	return $price;
}

function getOptionValueMapping()
{
	global $db;
	
	$mapping_array = array();
	$mapping_query = $db->query("SELECT * FROM  " . DB_PREFIX . "stone_mapping sm"
			. " LEFT JOIN " . DB_PREFIX . "stone_mapping_value smv ON (sm.stone_mapping_id = smv.stone_mapping_id)");
	
	foreach($mapping_query->rows as $map)
	{
		if (!empty($map['option_value']))
		{	
			$mapping_array[$map['certificate']][$map['option_value']] = "'" . str_replace(",", "','", $map['option_value_mapping']) . "'";
			$mapping_array[$map['certificate']]['position'] = $map['position'];
			$mapping_array[$map['certificate']]['markup'] = $map['markup_percent'] . '|'.$map['markup_fixed'];
		}
	}

	return $mapping_array;
}

function getMultiOptionValueMapping()
{
	global $db;
	
	$multi_mapping_array = array();
	$multi_mapping_query = $db->query("SELECT * FROM  " . DB_PREFIX . "multistone_mapping sm"
			. " LEFT JOIN " . DB_PREFIX . "multistone_mapping_value smv ON (sm.multistone_mapping_id = smv.multistone_mapping_id)");
	
	
	foreach($multi_mapping_query->rows as $map)
	{
		if (!empty($map['option_value']))
		{	
			$multi_mapping_array[$map['certificate']][$map['option_value']] = "'" . str_replace(",", "','", $map['option_value_mapping']) . "'";
			$multi_mapping_array[$map['certificate']]['position'] = $map['position'];
			$multi_mapping_array[$map['certificate']]['markup'] = $map['markup_percent'] . '|'.$map['markup_fixed'];
		}
	}

	return $multi_mapping_array;
}

echo 'STEP 1: Cron Started.<br/><br/>';

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
		$default_options['product_markup'] = $product['product_markup'];
		
		$product_price = calculatePrice($default_options);
		
		$update = $db->query("UPDATE " . DB_PREFIX . "product SET price = '".$product_price."' WHERE product_id = '".$product['product_id']."' ");
	}

    echo 'Showcase Price Cron Completed Successfully! Thank You.';
}


?>