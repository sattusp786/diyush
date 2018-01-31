<?php
class ModelCatalogPriceCalculator extends Model {
	public function calculatePrice($data) {
	
		$sider_stone = array();
			if($data['side_stone'] != ''){
				$side_stone_arr = explode("|",$data['side_stone']);
				if(!empty($side_stone_arr)){
					$i = 0;
					foreach($side_stone_arr as $stones){
					if(!empty($stones)){
						$stones_arr = explode(",",$stones);
						if(!empty($stones_arr)){
							foreach($stones_arr as $stoner){
								if(!empty($stoner)){
									list($key,$value) = explode(":",$stoner);
									if($key == 'cert'){
										$key = 'lab';
									} elseif($key == 'width'){
										$key = 'bandwidth';
									} elseif($key == 'ring_size'){
										$key = 'ringsize';
									}
									$sider_stone[$i][strtolower($key)] = $value;
								}
							}
						}
					}
					$i++;
				}
			}
		}
		
		$multir_stone = array();
			if($data['multi_stone'] != ''){
				$multi_stone_arr = explode("|",$data['multi_stone']);
				if(!empty($multi_stone_arr)){
					$j = 0;
					foreach($multi_stone_arr as $multi){
					if(!empty($multi)){
						$stones_arr = explode(",",$multi);
						if(!empty($stones_arr)){
							foreach($stones_arr as $stoner){
								if(!empty($stoner)){
									list($key,$value) = explode(":",$stoner);
									if($key == 'cert'){
										$key = 'lab';
									} elseif($key == 'width'){
										$key = 'bandwidth';
									} elseif($key == 'ring_size'){
										$key = 'ringsize';
									}
									$multir_stone[$j][strtolower($key)] = $value;
								}
							}
						}
					}
					$j++;
				}
			}
		}
		
		$final_option = array();
		$final_option = $data;
		$final_option['side_stone'] = $sider_stone;
		$final_option['multi_stone'] = $multir_stone;
		
		$optioner = $this->cart->calculatePrice($final_option);
		
		return $optioner;
			
    }

	public function getOptionValueMapping()
	{
		$mapping_array = array();
		$mapping_query = $this->db->query("SELECT * FROM  " . DB_PREFIX . "option_search os"
				. " LEFT JOIN " . DB_PREFIX . "option_search_mapping osm ON (os.option_search_id = osm.option_search_id)");
		
		//echo "<pre>";print_r($mapping_query->rows);		echo "</pre>"; die;
		
		foreach($mapping_query->rows as $map)
		{
			if (!empty($map['option_value']))
			{
				$shapes = explode(",",$map['shape']);
				
				for ($index = 0; $index < count($shapes); $index++) {
					$mapping_array[$map['certificate']][$shapes[$index]][$map['option_value']] = "'" . str_replace(",", "','", $map['option_value_mapping']) . "'";
					$mapping_array[$map['certificate']][$shapes[$index]]['position'] = $map['position'];
				}
			}
		}

		return $mapping_array;
	}

    public function getOptionValue() {

    	$options = array();

        $option_arr = array(
        					3 => 'Band Thickness',
        					4 => 'Band Width',
        					5 => 'Carat',
        					6 => 'Certificate',
        					10 => 'Clarity',
        					11 => 'Colour',
        					14 => 'Metal',
        					18 => 'Ring Size',
        					20 => 'Shape',
        					22 => 'Stone Type'
        				);
        $i = 0;
        foreach($option_arr as $option_id => $option_name ){

        	$option_values = array();

	        $sql = "SELECT ovd.name, ov.code FROM " . DB_PREFIX . "option_value ov
			LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id=ovd.option_value_id)
			WHERE ov.option_id = '" . (int) $option_id . "'  ORDER BY ovd.name ";

	        $query1 = $this->db->query($sql);
	        if($query1->num_rows){
	        	$j = 0;
	        	foreach($query1->rows as $option_value){
	        		$option_values[$j]['code'] = $option_value['code'];
	        		$option_values[$j]['name'] = $option_value['name'];

	        		$j++;
	        	}
	        }

	        $options[$i]['option_id'] = $option_id;
	        $options[$i]['option_name'] = $option_name;
	        $options[$i]['option_values'] = $option_values;

	        $i++;
    	}

        return $options;
    }

    public function getProductMarkup() {
        $sql = "SELECT * FROM " . DB_PREFIX . "markup_product  WHERE status = '1' ORDER BY code ASC ";

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getProductTypes() {
        $sql = "SELECT product_type_id,name FROM " . DB_PREFIX . "product_type  WHERE 1 ORDER BY name ASC ";

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getProductTax() {
        $sql = "SELECT name,rate FROM " . DB_PREFIX . "tax_rate  ORDER BY name ASC ";

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function addProductMarkup($code, $price)
	{
		$final_price = $price;
	
		if(!empty($code)) { 

			$markupuery = $this->db->query("SELECT pms.ranges FROM " . DB_PREFIX . "markup_product mp LEFT JOIN " . DB_PREFIX . "markup_product_to_store pms ON(mp.markup_product_id=pms.markup_product_id) WHERE  mp.code = '" . $this->db->escape($code) . "' AND mp.status = '1' AND pms.store_id='" .(int)$this->config->get('config_store_id'). "'");
			if ($markupuery->num_rows)
			{
				if ($markupuery->row['ranges'])
				{
					$product_markup = explode(',', $markupuery->row['ranges']);
					if ($product_markup)
					{
						$markup_per = $markup_amount = 0;
						foreach($product_markup as $markup)
						{
							$markup_values = explode(':', $markup);
							if ($markup_values[0] >= $price)
							{
								$markup_per = $markup_values[1];
								$markup_amount = isset($markup_values[2]) ? $markup_values[2] : 0;
								break;
							}
						}

						$final_price+= (($price * $markup_per) / 100) + $markup_amount;
					}
				}
			}
		}

		$store_markup = $this->config->get('config_store_markup');
		if(!empty($store_markup)) {
			$store_markup_data = explode(":",$store_markup);
			if(count($store_markup_data) > 1) {
				$final_price += (($final_price * $store_markup_data[0]) / 100) + $store_markup_data[1];
			}

		}
		

		return $final_price;
	}
	
	public function getProductVariant($variant_code){
		
		$prod_variant = array();
		$get_prod_variant = $this->db->query("SELECT product_variant_id, code FROM " . DB_PREFIX . "product_variant WHERE code LIKE '".$variant_code."%' ORDER BY code LIMIT 10");
		if($get_prod_variant->num_rows > 0){
			$prod_variant = $get_prod_variant->rows;
		}
		return $prod_variant;
	}
	
	public function getVariantImages($product_variant_id){
		
		$variant_images = array();
		$get_variant_images = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_variant_image WHERE product_variant_id='".$product_variant_id."' ");
		if($get_variant_images->num_rows > 0){
			$variant_images = $get_variant_images->rows;
		}
		return $variant_images;
	}
	
	public function getStones($variant_image_id){
		
		$stones = array();
		$get_stones = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_variant_image pvi LEFT JOIN " . DB_PREFIX . "product_variant_stone pvs ON (pvi.product_variant_image_id=pvs.product_variant_image_id AND pvi.product_variant_id=pvs.product_variant_id) WHERE pvi.product_variant_image_id='".$variant_image_id."' ");
		if($get_stones->num_rows > 0){
			$stones = $get_stones->rows;
		}
		return $stones;
		
	}
	
	public function getCurrencySymbol($price, $code, $round){
		
		return $this->currency->getSymbolLeft($code).round($price,$round).$this->currency->getSymbolRight($code);
	}
}