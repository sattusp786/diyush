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
	
	$default_gravity = 10.80;
	//$default_gravity = 15.50;
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
	
	$stone_price = 0;
	$sidestone_price = 0;
	$multistone_price = 0;
	
	if(stripos($data['multistone'],'C') !== false){
		
		//Calculate Stone Price..
		
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
		
		$stone_sql .= " ORDER BY carat_price ASC";
			
		if(isset($mapping[$data['Certificate']]['position'])){
			$position = $mapping[$data['Certificate']]['position'];
		} else {
			$position = 1;
		}
		
		$position = (isset($position))?($position-1):1;
		$stone_sql .= " limit $position,1";
		//echo $stone_sql;
		
		$get_stone_price = $db->query($stone_sql);
			
		if($get_stone_price->num_rows){
			if(isset($data['Carat']) && !empty($data['Carat'])) {
					$sprice = $get_stone_price->row['carat_price'] * ($data['Carat']/100);
				} else {
					$sprice = $get_stone_price->row['carat_price'];
				}
			if (isset($mapping[$data['Certificate']]['markup'])) {
				$markup = explode('|',$mapping[$data['Certificate']]['markup']);
				if(isset($markup[0]) && $markup[0] > 0){
					$sprice = $sprice * $markup[0] + $markup[1];
				} elseif(isset($markup[1])) {
					$sprice = $sprice + $markup[1];
				}
			}
			$stone_price = $sprice;
		} else {
			$no_price = '1';
		}
	}
	
	
	if(stripos($data['multistone'],'S') !== false){
		//Calculate Side Stone Price..
		$sprice = 0;
		if(isset($data['side_stone']) && !empty($data['side_stone'])){
			foreach($data['side_stone'] as $side){
				
				$sides_stone = (isset($side['stone']) && !empty($side['stone'])) ? $side['stone'] : $data['Stone Type'];
				$sides_shape = (isset($side['shape']) && !empty($side['shape'])) ? $side['shape'] : $data['Shape'];
				$sides_carat = (isset($side['carat']) && !empty($side['carat'])) ? $side['carat'] : $data['Carat'];
				$sides_color = (isset($side['color']) && !empty($side['color'])) ? $side['color'] : $data['Colour'];
				$sides_clarity = (isset($side['clarity']) && !empty($side['clarity'])) ? $side['clarity'] : $data['Clarity'];
				$sides_lab = (isset($side['lab']) && !empty($side['lab'])) ? $side['lab'] : $data['Certificate'];
				$sides_pieces = (isset($side['pieces']) && !empty($side['pieces'])) ? $side['pieces'] : 1;
				
				$sider_color = isset($multi_mapping[$sides_lab][$sides_color]) ? $multi_mapping[$sides_lab][$sides_color] : "'".$sides_color."'";
				$sider_clarity = isset($multi_mapping[$sides_lab][$sides_clarity]) ? $multi_mapping[$sides_lab][$sides_clarity] : "'".$sides_clarity."'";
				$sider_lab = isset($multi_mapping[$sides_lab][$sides_lab]) ? $multi_mapping[$sides_lab][$sides_lab] : "'".$sides_lab."'";
				
				$sidestone_sql = "SELECT * FROM ".DB_PREFIX."stone_price WHERE stone='".$sides_stone."' AND shape='".$sides_shape."' AND ".$sides_carat." between crt_from AND crt_to AND clarity IN (" . $sider_clarity . ") AND color IN (" . $sider_color . ") AND lab IN (" . $sider_lab . ") ";
				
				//$sidestone_sql = "SELECT * FROM ".DB_PREFIX."stone_price WHERE stone='".$sider_stone."' AND shape='".$sider_shape."' AND weight >= ".$sider_carat." AND clarity IN (" . $sider_clarity . ") AND color IN (" . $sider_color . ") AND lab IN (" . $sider_lab . ") ";
				
				if($sides_pieces == 1 && $sides_carat >= 0.20){
					$sidestone_sql .= " ORDER BY carat_price ASC";
				} else {
					$sidestone_sql .= " ORDER BY carat_price ASC";
				}
				
				if(isset($multi_mapping[$sides_lab]['position'])){
					$side_position = $multi_mapping[$sides_lab]['position'];
				} else {
					$side_position = 1;
				}
				
				$side_position = (isset($side_position))?($side_position-1):1;
				$sidestone_sql .= " limit $side_position,1";
		
				//echo $sidestone_sql;
				
				$get_sidestone_price = $db->query($sidestone_sql);
				
				if($get_sidestone_price->num_rows){
					
						$sprice = $get_sidestone_price->row['carat_price'] * $sides_carat;
						if (isset($multi_mapping[$sides_lab]['markup'])) {
							$side_markup = explode('|',$multi_mapping[$sides_lab]['markup']);
							if(isset($side_markup[0]) && $side_markup[0] > 0){
								$sprice = $sprice * $side_markup[0] + $side_markup[1];
							} elseif(isset($side_markup[1])) {
								$sprice = $sprice + $side_markup[1];
							}
						}
						$sidestone_price += $sprice * $sides_pieces;
					
				} else {
					$no_price = '1';
				}
			}
		}
		//echo "</br>";
		//echo $sidestone_sql;
	}
	
	if(stripos($data['multistone'],'M') !== false){
		//Calculate Multi Stone Price..
		$mprice = 0;
		if(isset($data['multi_stone']) && !empty($data['multi_stone'])){
			foreach($data['multi_stone'] as $multi){
				
				$multie_stone = (isset($multi['stone']) && !empty($multi['stone'])) ? $multi['stone'] : $data['Stone Type'];
				$multie_shape = (isset($multi['shape']) && !empty($multi['shape'])) ? $multi['shape'] : $data['Shape'];
				$multie_carat = (isset($multi['carat']) && !empty($multi['carat'])) ? $multi['carat'] : $data['Carat'];
				$multie_color = (isset($multi['color']) && !empty($multi['color'])) ? $multi['color'] : $data['Colour'];
				$multie_clarity = (isset($multi['clarity']) && !empty($multi['clarity'])) ? $multi['clarity'] : $data['Clarity'];
				$multie_lab = (isset($multi['lab']) && !empty($multi['lab'])) ? $multi['lab'] : $data['Certificate'];
				$multie_pieces = (isset($multi['pieces']) && !empty($multi['pieces'])) ? $multi['pieces'] : 1;
				
				$multir_color = isset($multi_mapping[$multie_lab][$multie_color]) ? $multi_mapping[$multie_lab][$multie_color] : "'".$multie_color."'";
				$multir_clarity = isset($multi_mapping[$multie_lab][$multie_clarity]) ? $multi_mapping[$multie_lab][$multie_clarity] : "'".$multie_clarity."'";
				$multir_lab = isset($multi_mapping[$multie_lab][$multie_lab]) ? $multi_mapping[$multie_lab][$multie_lab] : "'".$multie_lab."'";
				
				//$multistone_sql = "SELECT * FROM ".DB_PREFIX."stone_price WHERE stone='".$multir_stone."' AND shape='".$multir_shape."' AND ".$multir_carat." between crt_from AND crt_to AND clarity IN (" . $multir_clarity . ") AND color IN (" . $multir_color . ") AND lab IN (" . $multir_lab . ") ";
				
				$multistone_sql = "SELECT * FROM ".DB_PREFIX."stone_price WHERE stone='".$multie_stone."' AND shape='".$multie_shape."' AND clarity IN (" . $multir_clarity . ") AND color IN (" . $multir_color . ") AND lab IN (" . $multir_lab . ") AND ".$multie_carat." between crt_from AND crt_to ";
				
				/*
				if($multie_pieces == '1' || in_array($data['product_type_id'],array(5,7))){
					if(in_array($data['product_type_id'],array(5,7))) {
						//$multistone_sql .=" AND weight >= ".$multir_carat;
						$multistone_sql .=" AND ".$multir_carat." between crt_from AND crt_to ";
					} else {
						$multistone_sql .=" AND ".$multir_carat." between crt_from AND crt_to ";
					}
					if($multie_carat >= 0.20){
						$multistone_sql .= " ORDER BY sprice ASC ";
					} else {
						$multistone_sql .= " ORDER BY mprice ASC ";
					}
				} else {
					$multistone_sql .=" AND ".$multie_carat." between crt_from AND crt_to ";
					$multistone_sql .= " ORDER BY mprice ASC ";
				}
				*/
				
				if($multie_pieces == '1' && $multie_carat >= 0.20){
					$multistone_sql .= " ORDER BY carat_price ASC ";
				} else {
					$multistone_sql .= " ORDER BY carat_price ASC ";
				}
					
				if(isset($multi_mapping[$multie_lab]['position'])){
					$multi_position = $multi_mapping[$multie_lab]['position'];
				} else {
					$multi_position = 1;
				}
				
				$multi_position = (isset($multi_position))?($multi_position-1):1;
				$multistone_sql .= " limit $multi_position,1";
				
				$get_multistone_price = $db->query($multistone_sql);
				
				if($get_multistone_price->num_rows){
					
					
						$mprice = $get_multistone_price->row['carat_price'] * $multie_carat;
						
						if (isset($multi_mapping[$multie_lab]['markup'])) {
							$multi_markup = explode('|',$multi_mapping[$multie_lab]['markup']);
							if(isset($multi_markup[0]) && $multi_markup[0] > 0){
								$mprice = $mprice * $multi_markup[0] + $multi_markup[1];
							} elseif(isset($multi_markup[1])) {
								$mprice = $mprice + $multi_markup[1];
							}
						}
						$multistone_price += $mprice * $multie_pieces;
					
				} else {
					$no_price = '1';
				}
			}
		}
		//echo "</br>";
		//echo $multistone_sql;
	}
	
	$no_price = '0';
	$all_stone_price = $stone_price + $sidestone_price + $multistone_price;
	if($metal_price == 0 || $all_stone_price == 0){
		$no_price = '1';
	}
	
	/*
	echo "<br/>Metal :". $metal_price;
	echo "<br/>Stone :". $stone_price;
	echo "<br/>Sidestone :". $sidestone_price;
	echo "<br/>Multistone :". $multistone_price;
	*/
	
	if($no_price == '0'){
		$final_price = $metal_price + $stone_price + $sidestone_price + $multistone_price;
		
		//Add Product Markup..
		if($data['product_markup'] != ''){
			$final_price = addProductMarkup($final_price, $data['product_markup']);
		}
	}
	
	//echo "<br/>TOTAL: ".$final_price;
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
		
		echo "\n Processing Model : " . $i++ . " : ". $product['sku']."<br/>";
		
		$sidestones = array();
		$multistones = array();
		$option_weight = 0;
        $default_options = array();
		$default_options = getOptionValuesData($product['product_id']);
		foreach ($default_options as $option) {
			if($option['default'] == '1'){
				$default_options[$option['name']] = $option['code'];
				
				//Sidestones..
				$sidestone_query = $db->query("SELECT * FROM " . DB_PREFIX . "side_stone WHERE product_option_value_id = '" . (int)$option['product_option_value_id'] . "' ");
				if($sidestone_query->num_rows){
					$i = 0;
					foreach($sidestone_query->rows as $sidestone){
						$sidestones[$i]['stone'] = $sidestone['stone'];
						$sidestones[$i]['shape'] = $sidestone['shape'];
						$sidestones[$i]['carat'] = $sidestone['carat'];
						$sidestones[$i]['pieces'] = $sidestone['pieces'];
						$sidestones[$i]['color'] = $sidestone['color'];
						$sidestones[$i]['clarity'] = $sidestone['clarity'];
						$sidestones[$i]['lab'] = $sidestone['lab'];
						
						$i++;
					}
				}
				
				//Multistones..
				$multistone_query = $db->query("SELECT * FROM " . DB_PREFIX . "multi_stone WHERE product_option_value_id = '" . (int)$option['product_option_value_id'] . "' ");
				if($multistone_query->num_rows){
					$j = 0;
					foreach($multistone_query->rows as $multistone){
						$multistones[$j]['stone'] = $multistone['stone'];
						$multistones[$j]['shape'] = $multistone['shape'];
						$multistones[$j]['carat'] = $multistone['carat'];
						$multistones[$j]['pieces'] = $multistone['pieces'];
						$multistones[$j]['color'] = $multistone['color'];
						$multistones[$j]['clarity'] = $multistone['clarity'];
						$multistones[$j]['lab'] = $multistone['lab'];
						
						$j++;
					}
				}
		
				if ($option['weight_prefix'] == '+') {
					$option_weight += $option['weight'];
				} elseif ($option['weight_prefix'] == '-') {
					$option_weight -= $option['weight'];
				}
			}
		}
		
		$default_options['metal_weight'] = $product['weight'] + $option_weight;
		$default_options['product_markup'] = $product['product_markup'];
		$default_options['product_type_id'] = $product['product_type_id'];
		$default_options['multistone'] = $product['multistone'];
		
		$default_options['side_stone'] = $sidestones;
		$default_options['multi_stone'] = $multistones;
		
		$product_price = calculatePrice($default_options);
		
		$update = $db->query("UPDATE " . DB_PREFIX . "product SET price = '".$product_price."' WHERE product_id = '".$product['product_id']."' ");
	}

    echo 'Showcase Price Cron Completed Successfully! Thank You.';
}


?>