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
			$coder = $product_option_value['code'];
        	//Metal
            if($product_option['option_id'] == '14' && $product_option_value['default'] == '1'){
				$metal_code = strtolower(substr($coder, -1));
				$metal = $metal_code.$metal_code;
            }
            //Stonetype
            if($product_option['option_id'] == '22' && $product_option_value['default'] == '1'){
            	$stonetype = strtolower($coder);
            }
            //Shape
            if($product_option['option_id'] == '20' && $product_option_value['default'] == '1'){
            	$shape = strtolower($coder);
            }
            //Carat
            if($product_option['option_id'] == '5' && $product_option_value['default'] == '1'){
            	if($coder > 0 &&  $coder <= 100){
            		$carat = '0.0000-1.0000';
            	} elseif($coder > 100 &&  $coder <= 200){
            		$carat = '1.0000-2.0000';
            	} elseif($coder > 200 &&  $coder <= 300){
            		$carat = '2.0000-3.0000';
            	} elseif($coder > 0 &&  $coder <= 200){
            		$carat = '0.0000-2.0000';
            	} elseif($coder > 100 &&  $coder <= 300){
            		$carat = '1.0000-3.0000';
            	} elseif($coder > 0 &&  $coder <= 300){
            		$carat = '0.0000-3.0000';
            	} else {
            		$carat = '0.0000-3.0000';
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

	# Copy Files, if dimension is given then also resize.
	function copyFile($source_path, $dest_path, $dest_dimensions, $type, $img_quality) {
		if (!file_exists($dest_path)) {
		
			//echo 'copysource_path -> ' . $source_path . "\n";
			//echo 'copydest_path -> ' . $dest_path . "\n\n";
			
			# Create dir if it does not exist
			if (!file_exists(dirname($dest_path))) {
				mkdir(dirname($dest_path), 0777, true);
			}
			
			# If image dimension is empty then Copy Image else Resize Image and then copy
			if (empty($dest_dimensions)) {
				copy($source_path, $dest_path);
			} else {
				list ($s_img_width, $s_img_height) = getimagesize($source_path);
				list ($d_img_width, $d_img_height) = explode('x', $dest_dimensions);
						
				//$img_quality = 90;
				$image = new Image($source_path);
				$image->resize($d_img_width, $d_img_height, $type);
				$image->save($dest_path, $img_quality);
			}
			chmod($dest_path, 0777);
			
			//Compress images to quality 80 from [1 to 100]
			exec('jpegoptim -m80 '.$dest_path);
		}
	}
	
	function cropFile($source_path, $dest_path, $startx, $starty, $width, $height, $img_quality) {
		if (!file_exists($dest_path)) {
			//echo 'cropsource_path -> ' . $source_path . "\n";
			//echo 'cropdest_path -> ' . $dest_path . "\n\n";
			
			# Create dir if it does not exist
			if (!file_exists(dirname($dest_path))) {
				mkdir(dirname($dest_path), 0777, true);
			}
			
			# If image dimension is empty then Copy Image else Resize Image and then copy
			if (empty($width)) {
				copy($source_path, $dest_path);
			} 
			else 
			{
				$image = new Image($source_path);
				$image->crop($startx, $starty, $width, $height);
				$image->save($dest_path, $img_quality);
			}
			chmod($dest_path, 0777);
		}
	}
	
	echo 'STEP 1: Converting Images to respective directories.<br/><br/>';

	$dest_root = DIR_IMAGE."upload_images";
	$type = 'h';
	$img_quality = 100;
	$first_iters = array();
	# Recursively Scan over all Directories
	$first_iters = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dest_root, RecursiveDirectoryIterator::SKIP_DOTS)
	);

	foreach ($first_iters as $path){
		$pathname = $path->getPathname();
		$basefilename = $path->getBasename();
		
		$path_arr = array();
		$carat_arr = array();
		if($basefilename != ''){
			$path_arr = explode("_",$basefilename);
			
			$design_no = strtolower($path_arr[0]);
			$view_type = strtolower($path_arr[1]);
			$metal_type = strtolower($path_arr[2]);
			$stone_type = strtolower($path_arr[3]);
			$shape = strtolower($path_arr[4]);
			$carat = $path_arr[5];
			$carat_arr = explode("-",$carat);
			$carat_from = $carat[0];
			$carat_to = $carat[1];
			$filename = strtolower(end($path_arr));
			
			$full_path = DIR_IMAGE.'product/'.$design_no.'/'.$view_type.'/'.$metal_type.'/'.$stone_type.'/'.$shape.'/'.$carat.'/'.$filename;
			if(!file_exists($full_path)){
				copyFile($pathname, $full_path, '', $type, $img_quality); 
			}
		}
	}
	
	echo 'STEP 2: Completed Copying Images to respective image directories.<br/><br/>';
	
	echo 'STEP 3: Updating Default Images path.<br/><br/>';
	
	$carrat_arr = array('0.0000-1.0000','1.0000-2.0000','2.0000-3.0000','0.0000-2.0000','1.0000-3.0000','0.0000-3.0000');

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

	echo 'Image Cron Completed Successfully! Thank You.';
	
?>