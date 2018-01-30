<?php
class ModelCatalogPriceCalculator extends Model {
	public function calculatePrice($data) {
	
        $option = array();
		$option['error'] = '';
        //----------metal price-------------------//

        $currency_str = (isset($data['currency']) ? $data['currency'] : 'USD_1');
		$currency_arr = explode("_",$data['currency']);
		$currency_code = $currency_arr[0];
		$currency = $currency_arr[1];
        $product_markup = (isset($data['markup']) ? $data['markup'] : '');
        $taxs = (isset($data['tax']) ? $data['tax'] : '');
		$product_type = (isset($data['product_type_id']) ? $data['product_type_id'] : '');
        $store_id = (isset($data['stores']) ? $data['stores'] : 0);
        $band_weight = (isset($data['band_weight']) ? $data['band_weight'] : 0);
        $head_weight = (isset($data['head_weight']) ? $data['head_weight'] : 0);
        $stone_price_date = (isset($data['stone_price_date']) ? $data['stone_price_date'] : date("Y-m-d"));
		
		if (empty($data['head_purity']) || (isset($data['metal_purity']) && ($data['head_purity'] == $data['metal_purity'])))
		{
			$band_weight = $band_weight + $head_weight;
			$head_weight = 0;
		}
		
		$table_price = "stone_price";
		
		$today_date = date("Y-m-d");
		if($stone_price_date != $today_date){
			
			$year = date("Y",strtotime($stone_price_date));
			$month = date("m",strtotime($stone_price_date));
			$day = date("d",strtotime($stone_price_date));
			$file_price_path = str_replace("\\","/",DIR_DOWNLOAD).'stone_price_backup/'.$year.'/'.$month.'/'.$day.'_price.csv';
			
			if(!file_exists($file_price_path)){
				$option['error'] = "Stone Price Backup Data not available for <b>".$stone_price_date."</b>";
				return $option;
			} else {
				$truncate = $this->db->query("TRUNCATE " . DB_PREFIX . "stone_price_temp");
				$insert_price = $this->db->query("LOAD DATA LOCAL INFILE '".$file_price_path."' INTO TABLE " . DB_PREFIX . "stone_price_temp FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n' IGNORE 1 LINES");
				
				if($insert_price) {
					$table_price = "stone_price_temp";
				} else {
					$option['error'] = "Error in adding old stone price backup data. Please check if the old stone price backup data is available or not.";
					return $option;
				}
			}
		}
		
		$default_gravity = 15.50;
		
        $actual_stone_price = $stone_price = 0;
        $band_price = $head_price = 0;
        $metalband_price = $metalhead_price = 0;
        $total_price = 0;
		$metal_price = $actual_metal_price = 0;
        $stnprice = $stnprice_disp = 0;
        $mprice = 0;
        $sprice = 0;
		$chain_price = 0;
		$option_search_id ='';

		$multistone_serialized = 'a:34:{i:0;s:2:"42";i:1;s:2:"25";i:2;s:2:"30";i:3;s:2:"26";i:4;s:2:"29";i:5;s:2:"17";i:6;s:2:"41";i:7;s:2:"39";i:8;s:2:"40";i:9;s:2:"38";i:10;s:2:"35";i:11;s:2:"31";i:12;s:2:"37";i:13;s:2:"33";i:14;s:2:"34";i:15;s:2:"32";i:16;s:1:"8";i:17;s:1:"3";i:18;s:2:"12";i:19;s:2:"23";i:20;s:2:"21";i:21;s:2:"36";i:22;s:2:"24";i:23;s:2:"27";i:24;s:2:"19";i:25;s:2:"46";i:26;s:2:"44";i:27;s:2:"43";i:28;s:1:"4";i:29;s:2:"13";i:30;s:1:"9";i:31;s:2:"10";i:32;s:2:"16";i:33;s:2:"28";}';
		$multistone_products = unserialize($multistone_serialized);
		//$multistone_products = $this->config->get('config_multistone_product_type_id');

		$mapping_array = $this->getOptionValueMapping();

		//echo"<pre>";print_r($mapping_array);echo"</pre>";//exit;
		
		if (isset($data['metal_purity']) && !empty($data['metal_purity']))
		{
			$metal_sql = "SELECT price, gravity, markup_rate, code FROM " . DB_PREFIX . "metal_price WHERE  code = '" . $this->db->escape($data['metal_purity']) . "'";
			if (isset($data['head_purity']) && !empty($data['head_purity']))
			{
				$metal_sql.= " OR code = '" . $this->db->escape($data['head_purity']) . "'";
			}
			$metal_query = $this->db->query($metal_sql);
			if ($metal_query->num_rows)
			{
				foreach($metal_query->rows AS $metal_result)
				{
					if ($data['metal_purity'] == $metal_result['code'])
					{
						//$band_weight = ($band_weight / $default_gravity) * $metal_result['gravity'];
						$metalband_price+= $metal_result['price'] * $band_weight;
						
						$actual_metal_price += $metalband_price;
						/* Code to apply markup on metalband price starts here */
						if ($metal_result['markup_rate'])
						{
							$band_markup_per = $band_markup_amount = 0;
							$store = (int)$this->config->get('config_store_id');
							$band_metal_markup = explode(',', $metal_result['markup_rate']);
							foreach($band_metal_markup as $band_markup)
							{
								$band_makup_values = explode(':', $band_markup);
								if ($band_makup_values[0] == $store)
								{
									$band_markup_per = $band_makup_values[1];
									$band_markup_amount = $band_makup_values[2];
									break;
								}
							}

							$metalband_price+= (($metalband_price * $band_markup_per) / 100) + $band_markup_amount;
						}
					}
					elseif ((isset($data['head_purity'])) && $data['head_purity'] == $metal_result['code'])
					{
						//$head_weight = ($head_weight / $default_gravity) * $metal_result['gravity'];
						$metalhead_price = $metalhead_price + $metal_result['price'] * $head_weight;
						
						$actual_metal_price += $metalhead_price;
						/* Code to apply markup on metalhead price starts here */
						if ($metal_result['markup_rate'])
						{
							$store = (int)$this->config->get('config_store_id');
							$head_metal_markup = explode(',', $metal_result['markup_rate']);
							$head_markup_per = $head_markup_amount = 0;
							foreach($head_metal_markup as $head_markup)
							{
								$head_makup_values = explode(':', $head_markup);
								if ($head_makup_values[0] == $store)
								{
									$head_markup_per = $head_makup_values[1];
									$head_markup_amount = $head_makup_values[2];
									break;
								}
							}

							$metalhead_price+= (($metalhead_price * $head_markup_per) / 100) + $head_markup_amount;
						}
					}
				}
			}

			$metal_price = $metalband_price + $metalhead_price;
		}
		
		/*
        if (!empty($data['metal_purity'])) {

            $query = $this->db->query("SELECT code,price,percent,markup_rate FROM " . DB_PREFIX . "metal_price WHERE code = '" . $this->db->escape($data['metal_purity']) . "'");

            $band_price = $query->row['price'] * $data['band_weight'];

            $band_markup_per = $band_markup_amount = 0;

            $band_metal_markup = explode(',', $query->row['markup_rate']);
            foreach ($band_metal_markup as $band_markup) {
                $band_makup_values = explode(':', $band_markup);
                if ($band_makup_values[0] == $store_id) {
                    $band_markup_per = $band_makup_values[1];
                    $band_markup_amount = $band_makup_values[2];
                    break;
                }
            }
			
            $metal_band_price += (($band_price * $band_markup_per) / 100) + $band_markup_amount;
        }


        if (!empty($data['head_purity'])) {
            $query1 = $this->db->query("SELECT m.code,m.price,m.percent,m.markup_rate,mps.store_id "
                    . "FROM " . DB_PREFIX . "metal_price m LEFT JOIN " . DB_PREFIX . "metal_price_to_store "
                    . "mps ON(m.metal_price_id=mps.metal_price_id) WHERE m.code = '" . $data['head_purity'] . "'");

            $head_price = $query1->row['price'] * $data['head_weight'];

            $head_markup_per = $head_markup_amount = 0;

            $band_metal_markup = explode(',', $query1->row['markup_rate']);
            foreach ($band_metal_markup as $head_markup) {
                $band_makup_values = explode(':', $head_markup);
                if ($band_makup_values[0] == $store_id) {
                    $head_markup_per = $band_makup_values[1];
                    $head_markup_amount = $band_makup_values[2];
                    break;
                }
            }

            $metal_head_price += (($head_price * $head_markup_per) / 100) + $head_markup_amount;
        }

			$actual_metal_price = $band_price + $head_price; 
			$metal_price = $metal_band_price + $metal_head_price; 		
		*/
			
			//$mtlprice = $currency * ($band_price + $head_price);


        //----------stone price calculate-------------------//
		$stoneprice_id = 0;
        if (isset($data['stone'])) {
			
			foreach ($data['stone'] as $stone) {
				$stone_sql = "SELECT  sp.stone_price_id, sp.sprice,sp.mprice,sp.carat_price " . "FROM " . DB_PREFIX . $table_price." sp WHERE 1=1 ";
				if (!empty($stone['type'])) {
					$stone_sql.= " AND sp.stone = '" . $this->db->escape($stone['type']) . "'";
				} else {
					$stone_sql.= " AND sp.stone = 'DI'";
				}

				if (!empty($stone['shape'])) {
					$stone_sql.= " AND sp.shape = '" . $this->db->escape($stone['shape']) . "'";
				} else {
					$option['noprice'] = 1;
				}

				if (!empty($stone['color'])) {
					if (isset($mapping_array[$stone['certificate']][$stone['shape']][$stone['color']])) {
						$stone_sql.= " AND sp.color in (" . $mapping_array[$stone['certificate']][$stone['shape']][$stone['color']] . ")";
					} else {
						$stone_sql.= " AND sp.color = '" . $this->db->escape($stone['color']) . "'";
					}
				} else {
					if ($stone['type'] == 'DI') {
						$option['noprice'] = 1;
					}
				}
				
				if ($stone['type'] == 'FY' && !empty($stone['intensity']))
				{
					if (isset($mapping_array[$stone['certificate']][$stone['shape']][$stone['intensity']]))
					{
						$stone_sql.= " AND sp.intensity in (" . $mapping_array[$stone['certificate']][$stone['shape']][$stone['intensity']] . ")";
					}
					else
					{
						$stone_sql.= " AND sp.intensity = '" . $this->db->escape($stone['intensity']) . "'";
					}
				}
				else
				{
					if ($stone['type'] == 'FY')
					{
						$option_price['noprice'] = 1;
						//$this->addMessageLog($data, 'INTENSITY', 'NOT FOUND', $stone_sql);
						$error_arr[] =  'INTENSITY NOT FOUND, '.$data.', '.$stone_sql;
						break;
					}
				}	

				if (!empty($stone['clarity'])) {
					if ($stone['type'] == 'DI') {
						if (isset($mapping_array[$stone['certificate']][$stone['shape']][$stone['clarity']])) {
							$stone_sql.= " AND sp.clarity in (" . $mapping_array[$stone['certificate']][$stone['shape']][$stone['clarity']] . ")";
						} else {
							$stone_sql.= " AND sp.clarity = '" . $this->db->escape($stone['clarity']) . "'";
						}
					}
				} else {
					if ($stone['type'] == 'DI') {
						$option['noprice'] = 1;
					}
				}

				// STONE CUT, SYMMETRY, POLISH ADDED

					if (!empty($stone['cut'])) {

						if (isset($mapping_array[$stone['certificate']][$stone['shape']][$stone['cut']])) {
							$stone_sql.= " AND sp.cut_grade in (" . $mapping_array[$stone['certificate']][$stone['shape']][$stone['cut']] . ")";
						} else {
							$stone_sql.= " AND sp.cut_grade = '" . $this->db->escape($stone['cut']) . "'";
						}
					} elseif ($stone['shape'] == 'RND') {
						$stone_sql.= " AND sp.cut_grade IN ('GD','EX','VG')";
					}

					if (!empty($stone['polish'])) {

						if (isset($mapping_array[$stone['certificate']][$stone['shape']][$stone['polish']])) {
							$stone_sql.= " AND sp.polish in (" . $mapping_array[$stone['certificate']][$stone['shape']][$stone['polish']] . ")";
						} else {
							$stone_sql.= " AND sp.polish = '" . $this->db->escape($stone['polish']) . "'";
						}
					}
					
					if (!empty($stone['symmetry'])) {

						if (isset($mapping_array[$stone['certificate']][$stone['shape']][$stone['symmetry']])) {
							$stone_sql.= " AND sp.symmetry in (" . $mapping_array[$stone['certificate']][$stone['shape']][$stone['symmetry']] . ")";
						} else {
							$stone_sql.= " AND sp.symmetry = '" . $this->db->escape($stone['symmetry']) . "'";
						}
					}
					
					if (!empty($stone['fluorescence'])) {

						if (isset($mapping_array[$stone['certificate']][$stone['shape']][$stone['fluorescence']])) {
							$stone_sql.= " AND sp.fluorescence_intensity in (" . $mapping_array[$stone['certificate']][$stone['shape']][$stone['fluorescence']] . ")";
						} else {
							$stone_sql.= " AND sp.fluorescence_intensity = '" . $this->db->escape($stone['fluorescence']) . "'";
						}						
					}

// END STONE CUT, SYMMETRY, POLISH ADDED

				if (!empty($stone['certificate'])) {
					if (isset($mapping_array[$stone['certificate']][$stone['shape']][$stone['certificate']]) && $stone['carat'] >= 0.20) {
						$stone_sql.= " AND sp.lab in (" . $mapping_array[$stone['certificate']][$stone['shape']][$stone['certificate']] . ")";
					} else {
						$stone_sql.= " AND sp.lab = '" . $this->db->escape($stone['certificate']) . "'";
					}
				} elseif ($stone['position'] == 'Side Stone') {
					$stone_sql.= " AND sp.lab = 'DF'";
				} else {
					$option['noprice'] = 1;
				}

				if (!empty($stone['carat']) && $stone['carat'] >= 0.20 && in_array($stone['type'],array('DI','FY')))
				{
					if ((($stone['quantity'] > 1) && !in_array($data['product_type_id'], array(6,7))) || in_array($data['product_type_id'], $multistone_products)) {
						$stone_sql.= " AND " . (float)$stone['carat'] . "  BETWEEN sp.crt_from AND sp.crt_to";
						$stone_sql.= " ORDER BY sp.mprice ASC";
					}
					else
					{
						$stone_sql.= " AND sp.weight  >= " . (float)$stone['carat'] . " ";
						$stone_sql.= " ORDER BY sp.sprice ASC";
					}

					if (!in_array($data['product_type_id'], $multistone_products))
					{
						if(isset($mapping_array[$stone['certificate']][$stone['shape']]['position'])){
							$position = $mapping_array[$stone['certificate']][$stone['shape']]['position'];
						}else{
							$position = 2;
						}
						
						$position = (isset($position))?($position-1):1;
						$stone_sql.= " limit $position,1";							
					}else{
						$stone_sql.= " limit 0,1";							
					}
				}
				else
				{
					if(in_array($stone['type'], array('DI','BD','FY'))) {
						$stone_sql.= " AND " . (float)$stone['carat'] . "  BETWEEN sp.crt_from AND sp.crt_to";
					}else{
						// PRICE FOR GEM STONE PRODUCTS
						//$stone_sql.= " AND stone_size = '" . $this->db->escape($stone['stone_size']) . "'";
						$stone_sql.= " AND sp.weight  >= " . (float)$stone['carat'] . " ";
					}

					if (in_array($data['product_type_id'], $multistone_products)) {
						$stone_sql.= " ORDER BY sp.mprice ASC";
					}
					else
					{
						$stone_sql.= " ORDER BY sp.sprice ASC";
					}
					$stone_sql.= " limit 0,1";	
				}

				//echo "\n".$stone_sql;
				
				$stonedata = $this->db->query($stone_sql);
				if ($stonedata->num_rows > 0) {
					$stoneprice_id = $stonedata->row['stone_price_id'];
					$stone_price_ids[] = $stonedata->row['stone_price_id'] . "-" . $stone['quantity'];
					if ($stone['quantity'] == 1 && $stone['carat'] >= 0.20 && !in_array($data['product_type_id'], $multistone_products)) {
						
						$stone_price+= $stonedata->row['sprice'];
						$actual_stone_price+= $stonedata->row['carat_price'];
					} elseif ($stone['position'] == 'Center Stone' && in_array($data['product_type_id'], array(6,7))) {
						$stone_price+= $stonedata->row['sprice'] * 2;
						$actual_stone_price+= $stonedata->row['carat_price'] * 2;
					} else {
						$stone_price+= $stonedata->row['mprice'] * ($stone['carat']) * $stone['quantity'];
						$actual_stone_price+= $stonedata->row['carat_price'] * ($stone['carat']) * $stone['quantity'];
					}
				} else {
					$option['noprice'] = 1;
				}
			}
			
			/*
            foreach ($data['stone'] as $data) {

				$sql = "SELECT  sp2.stone_price_id, sp2.sprice,sp2.mprice "
						 . "FROM " . DB_PREFIX . "stone_price sp2 WHERE 1=1 ";

			
				if (!empty($data['stone_type'])) {
                    $sql.= " AND sp2.stone = '" . $this->db->escape($data['stone_type']) . "'";
                }
				else
				{
					$sql.= " AND sp2.stone = 'DI'";
				}

                if (!empty($data['shape'])) {
                    $sql.= " AND sp2.shape = '" . $this->db->escape($data['shape']) . "'";
                }

				if (!empty($data['clarity'])) {
					if ($data['stone_type'] == 'DI') {

						if (isset($mapping_array[$data['certificate']][$data['shape']][$data['clarity']]))
						{
							$sql.= " AND sp2.clarity in (" . $mapping_array[$data['certificate']][$data['shape']][$data['clarity']] . ")";
						}
						else
						{
							$sql.= " AND sp2.clarity = '" . $this->db->escape($data['clarity']) . "'";
						}
						
					}
				}


				if (!empty($data['color'])) {

					if (isset($mapping_array[$data['certificate']][$data['shape']][$data['color']]))
					{
						$sql.= " AND sp2.color in (" . $mapping_array[$data['certificate']][$data['shape']][$data['color']] . ")";
					}
					else
					{
						$sql.= " AND sp2.color = '" . $this->db->escape($data['color']) . "'";
					}

				}

                if (!empty($data['certificate'])) {

					if (isset($mapping_array[$data['certificate']][$data['shape']][$data['certificate']]) && $data['carat'] >= 0.20)
					{
						$sql.= " AND sp2.lab in (" . $mapping_array[$data['certificate']][$data['shape']][$data['certificate']] . ")";
					}
					else
					{
						$sql.= " AND sp2.lab = '" . $this->db->escape($data['certificate']) . "'";
					}
					
                }

                if (!empty($data['shape']) && strtoupper($data['shape']) == 'RND') {
                    $sql.= " AND sp2.cut_grade IN ('GD','EX','VG') ";
                }

				if (!empty($data['carat']) && $data['carat'] >= 0.20 && $data['stone_type'] == 'DI' ) {

					$sql .= " AND sp2.weight  >= " . (float)$data['carat'] . " ";

					if (($data['stone_qnt'] > 1) && !in_array($product_type, array(6,7))) {
						$sql.= " ORDER BY sp2.mprice ASC";
					}
					else
					{
						$sql.= " ORDER BY sp2.sprice ASC";
					}

					if (!in_array($product_type, $multistone_products))
					{						
						$position = $mapping_array[$data['certificate']][$data['shape']]['position'];
						$position = (isset($position))?($position-1):1;
						$sql.= " limit $position,1";								
					}else{
						$sql.= " limit 0,1";	
					}
				} else {

					if ($data['carat'] >= 0.20 && ($product_type == 6 || $product_type == 7)) {

						$sql.= " AND ( '" . $this->db->escape($data['carat'] / 2) . "' BETWEEN sp2.crt_from AND sp2.crt_to )";
					}else{
						$sql .= " AND ( " . (float)$data['carat'] . "  BETWEEN sp2.crt_from AND sp2.crt_to )";
					}

					if (($data['stone_qnt'] > 1) && in_array($product_type, $multistone_products)) {
						$sql.= " ORDER BY sp2.mprice ASC";
					}
					else
					{
						$sql.= " ORDER BY sp2.sprice ASC";
					}
						$sql.= " limit 0,1";	
				}


                //echo "<br>".$sql;

                $sql_result = $this->db->query($sql);

				

                //-----------------------------------------------------//
			

				if ($sql_result->num_rows) {

					if ($data['stone_qnt'] == 1 && $data['carat'] >= 0.20) {

						$stone_price += $sql_result->row['sprice'];
					} elseif (in_array($product_type , array(6,7))) {

						$stone_price += $sql_result->row['sprice'] * 2;
					} else {
						
						$stone_price += $sql_result->row['mprice'] * ($data['carat']) * $data['stone_qnt'];
					}
						
						$stnprice_disp = $currency * ($stone_price);
				}
            }
			*/
			
        }
			//echo"stone==".$stone_price;
			//echo"metal==".$metal_price;
			
			/*
			$total_price = $stone_price + $metal_price;  

			if (!empty($product_markup)) {				
				$total_price = (int)$this->addProductMarkup($product_markup, $total_price);
			}
			if (!empty($taxs)) {
			   $total_price = $total_price + ($total_price * $taxs) / 100;
			}
			
			 $total_price = $currency * $total_price;


			$price = array(
				'metal_price' => round($mtlprice),
				'stone_price' => round($stnprice_disp),
				'total_price' => round($total_price)
			);
			*/
		
		// code for getting chain price --starts here
		if (isset($data['chain_purity']) && isset($data['chain_type']) && isset($data['chain_length'])) {
			
			$chainquery = $this->db->query("select * from " . DB_PREFIX . "chain_pnc where type='" . $data['chain_type'] . "' AND purity='" . $data['chain_purity'] . "' AND length=" . $data['chain_length']);
			if ($chainquery->num_rows) {
				$chain_price += $chainquery->row['price'];
				$option['chain_weight'] = $chainquery->row['weight'];
			}
		}
			
        //----------stone price-------------------//
		//$option['side_stone'] = json_encode($side_stone_arr);
		$option['metal_price'] = $this->getCurrencySymbol($metal_price * $currency,$currency_code,2);
		$option['actual_metal_price'] = $this->getCurrencySymbol($actual_metal_price * $currency,$currency_code,2);
		$option['stone_price'] = $this->getCurrencySymbol($stone_price * $currency,$currency_code,2);
		$option['actual_stone_price'] = $this->getCurrencySymbol($actual_stone_price * $currency,$currency_code,2);
		$price = $metal_price + $stone_price + $chain_price;
		$option['price'] = $this->getCurrencySymbol($price * $currency,$currency_code,2);
		$option['actual_price'] = $this->getCurrencySymbol(($actual_metal_price + $actual_stone_price + $chain_price) * $currency,$currency_code,2);
		
		$price_with_prod_markup = $this->addProductMarkup($data['markup'], $price);
		$option['price_with_prod_markup'] = $this->getCurrencySymbol($price_with_prod_markup * $currency,$currency_code,2);
		
		if($data['tax'] > 0){
			$option['price_with_tax'] = $this->getCurrencySymbol(($price_with_prod_markup + ($price_with_prod_markup * ($data['tax']/100))) * $currency,$currency_code,2);
			$option['final_price'] = $this->getCurrencySymbol(($price_with_prod_markup + ($price_with_prod_markup * ($data['tax']/100))) * $currency,$currency_code,0);
		} else {
			$option['price_with_tax'] = $this->getCurrencySymbol($price_with_prod_markup * $currency,$currency_code,2);
			$option['final_price'] = $this->getCurrencySymbol($price_with_prod_markup * $currency,$currency_code,0);
		}
		
		$option['total_weight'] = number_format($head_weight + $band_weight, 2);
		$option['metal_code'] = $data['metal_purity'];
		$option['stoneprice_id'] = $stoneprice_id;
		
        return $option;
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