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
	
?>