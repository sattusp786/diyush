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

$query_products = $db->query("SELECT product_id FROM " . DB_PREFIX . "product WHERE status = '1'");
if($query_products->num_rows) {
	foreach($query_products->rows as $product){
		
		$product_id = $product['product_id'];
        $filter_style = array();
        $filter_shape = array();
        $filter_setting = array();


        //Get Filter Style..
		$get_filter_style = $db->query("SELECT * FROM " . DB_PREFIX . "product_filter pf LEFT JOIN " . DB_PREFIX . "filter f ON pf.filter_id = f.filter_id LEFT JOIN " . DB_PREFIX . "product p ON pf.product_id = p.product_id WHERE pf.product_id='".$product_id."' AND f.filter_group_id='5' AND p.status = '1' ");

		if($get_filter_style->num_rows > 0){
			foreach($get_filter_style->rows as $filter_style_id){
				$filter_style[] = $filter_style_id['filter_id'];
			}
		}

		//Get Filter Shape..
		$get_filter_shape = $db->query("SELECT * FROM " . DB_PREFIX . "product_filter pf LEFT JOIN " . DB_PREFIX . "filter f ON pf.filter_id = f.filter_id LEFT JOIN " . DB_PREFIX . "product p ON pf.product_id = p.product_id WHERE pf.product_id='".$product_id."' AND f.filter_group_id='4' AND p.status = '1' ");

		if($get_filter_shape->num_rows > 0){
			foreach($get_filter_shape->rows as $filter_shape_id){
				$filter_shape[] = $filter_shape_id['filter_id'];
			}
		}


		//Get Filter Setting..
		$get_filter_setting = $db->query("SELECT * FROM " . DB_PREFIX . "product_filter pf LEFT JOIN " . DB_PREFIX . "filter f ON pf.filter_id = f.filter_id LEFT JOIN " . DB_PREFIX . "product p ON pf.product_id = p.product_id WHERE pf.product_id='".$product_id."' AND f.filter_group_id='6' AND p.status = '1' ");

		if($get_filter_setting->num_rows > 0){
			foreach($get_filter_setting->rows as $filter_setting_id){
				$filter_setting[] = $filter_setting_id['filter_id'];
			}
		}


		$truncate = $db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '".$product_id."' ");
		$count = 0;
		//Cond 1
		if(!empty($filter_style)) { 
			
			$get_styles = $db->query("SELECT * FROM " . DB_PREFIX . "product_filter pf LEFT JOIN " . DB_PREFIX . "product p ON pf.product_id = p.product_id WHERE pf.filter_id IN (". implode(",",$filter_style) .") AND pf.product_id != '".$product_id."' AND p.status='1' ");
			
			if($get_styles->num_rows){
				foreach($get_styles->rows as $style_product){

					if(!empty($filter_shape)){
						
						$get_shapes = $db->query("SELECT * FROM " . DB_PREFIX . "product_filter pf LEFT JOIN " . DB_PREFIX . "product p ON pf.product_id = p.product_id WHERE pf.filter_id IN (". implode(",",$filter_shape) .") AND pf.product_id = '".$style_product['product_id']."' AND p.status='1' ");

						if($get_shapes->num_rows){
						foreach($get_shapes->rows as $shape_product){

							if(!empty($filter_setting)){
								
								$get_setting = $db->query("SELECT * FROM " . DB_PREFIX . "product_filter pf LEFT JOIN " . DB_PREFIX . "product p ON pf.product_id = p.product_id WHERE pf.filter_id IN (". implode(",",$filter_setting) .") AND pf.product_id = '".$shape_product['product_id']."' AND p.status='1' ");

								if($get_setting->num_rows){
									foreach($get_setting->rows as $setting_product){
										if($count < 10){
											$insert = $db->query("INSERT IGNORE INTO " . DB_PREFIX . "product_related SET product_id = '".$product_id."', related_id = '".$setting_product['product_id']."' ");
											$count++;
										} else {
											break;
										}
									}
								}

							}	
						}
					}
					}
				}
			}
		}


		//Cond 2
		if(!empty($filter_style)) { 
			
			$get_styles = $db->query("SELECT * FROM " . DB_PREFIX . "product_filter pf LEFT JOIN " . DB_PREFIX . "product p ON pf.product_id = p.product_id WHERE pf.filter_id IN (". implode(",",$filter_style) .") AND pf.product_id != '".$product_id."' AND p.status='1' ");
			
			if($get_styles->num_rows){
				foreach($get_styles->rows as $style_product){

					if(!empty($filter_shape)){
						
						$get_shapes = $db->query("SELECT * FROM " . DB_PREFIX . "product_filter pf LEFT JOIN " . DB_PREFIX . "product p ON pf.product_id = p.product_id WHERE pf.filter_id IN (". implode(",",$filter_shape) .") AND pf.product_id = '".$style_product['product_id']."' AND p.status='1' ");

						if($get_shapes->num_rows){
						foreach($get_shapes->rows as $shape_product){

							if($count < 10){
								$insert = $db->query("INSERT IGNORE INTO " . DB_PREFIX . "product_related SET product_id = '".$product_id."', related_id = '".$shape_product['product_id']."' ");
								$count++;
							} else {
								break;
							}
							
						}
					}
					}
				}
			}
		}


		//Cond 3
		if(!empty($filter_style)) { 
			
			$get_styles = $db->query("SELECT * FROM " . DB_PREFIX . "product_filter pf LEFT JOIN " . DB_PREFIX . "product p ON pf.product_id = p.product_id WHERE pf.filter_id IN (". implode(",",$filter_style) .") AND pf.product_id != '".$product_id."' AND p.status='1' ");
			
			if($get_styles->num_rows){
				foreach($get_styles->rows as $style_product){

					if($count < 10){
						$insert = $db->query("INSERT IGNORE INTO " . DB_PREFIX . "product_related SET product_id = '".$product_id."', related_id = '".$style_product['product_id']."' ");
						$count++;
					} else {
						break;
					}
				}
			}
		}
	}

    echo 'Completed';
}


?>