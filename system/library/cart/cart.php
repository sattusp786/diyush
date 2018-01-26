<?php
namespace Cart;
class Cart {
	private $data = array();

	public function __construct($registry) {
		$this->config = $registry->get('config');
		$this->customer = $registry->get('customer');
		$this->session = $registry->get('session');
		$this->db = $registry->get('db');
		$this->tax = $registry->get('tax');
		$this->weight = $registry->get('weight');
		$this->cache = $registry->get('cache');

		// Remove all the expired carts with no customer ID
		$this->db->query("DELETE FROM " . DB_PREFIX . "cart WHERE (api_id > '0' OR customer_id = '0') AND date_added < DATE_SUB(NOW(), INTERVAL 1 HOUR)");

		if ($this->customer->getId()) {
			// We want to change the session ID on all the old items in the customers cart
			$this->db->query("UPDATE " . DB_PREFIX . "cart SET session_id = '" . $this->db->escape($this->session->getId()) . "' WHERE api_id = '0' AND customer_id = '" . (int)$this->customer->getId() . "'");

			// Once the customer is logged in we want to update the customers cart
			$cart_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "cart WHERE api_id = '0' AND customer_id = '0' AND session_id = '" . $this->db->escape($this->session->getId()) . "'");

			foreach ($cart_query->rows as $cart) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "cart WHERE cart_id = '" . (int)$cart['cart_id'] . "'");

				// The advantage of using $this->add is that it will check if the products already exist and increaser the quantity if necessary.
				$this->add($cart['product_id'], $cart['quantity'], json_decode($cart['option']), $cart['recurring_id']);
			}
		}
	}

	public function getProducts() {
		$product_data = array();

		$cart_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "cart WHERE api_id = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "' AND customer_id = '" . (int)$this->customer->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "'");

		foreach ($cart_query->rows as $cart) {
			$stock = true;

			$product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_store p2s LEFT JOIN " . DB_PREFIX . "product p ON (p2s.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND p2s.product_id = '" . (int)$cart['product_id'] . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.date_available <= NOW() AND p.status = '1'");

			if ($product_query->num_rows && ($cart['quantity'] > 0)) {
				$option_price = 0;
				$option_points = 0;
				$option_weight = 0;

				$option_data = array();

				foreach (json_decode($cart['option']) as $product_option_id => $value) {
					$option_query = $this->db->query("SELECT po.product_option_id, po.option_id, od.name, o.type FROM " . DB_PREFIX . "product_option po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE po.product_option_id = '" . (int)$product_option_id . "' AND po.product_id = '" . (int)$cart['product_id'] . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "'");

					if ($option_query->num_rows) {
						if ($option_query->row['type'] == 'select' || $option_query->row['type'] == 'radio') {
							$option_value_query = $this->db->query("SELECT pov.option_value_id, ov.code, ovd.name, pov.quantity, pov.subtract, pov.price, pov.price_prefix, pov.points, pov.points_prefix, pov.weight, pov.weight_prefix, pov.stone_type FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_option_value_id = '" . (int)$value . "' AND pov.product_option_id = '" . (int)$product_option_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

							if ($option_value_query->num_rows) {
								if ($option_value_query->row['price_prefix'] == '+') {
									$option_price += $option_value_query->row['price'];
								} elseif ($option_value_query->row['price_prefix'] == '-') {
									$option_price -= $option_value_query->row['price'];
								}

								if ($option_value_query->row['points_prefix'] == '+') {
									$option_points += $option_value_query->row['points'];
								} elseif ($option_value_query->row['points_prefix'] == '-') {
									$option_points -= $option_value_query->row['points'];
								}

								if ($option_value_query->row['weight_prefix'] == '+') {
									$option_weight += $option_value_query->row['weight'];
								} elseif ($option_value_query->row['weight_prefix'] == '-') {
									$option_weight -= $option_value_query->row['weight'];
								}

								if ($option_value_query->row['subtract'] && (!$option_value_query->row['quantity'] || ($option_value_query->row['quantity'] < $cart['quantity']))) {
									$stock = false;
								}
								
								//Sidestones..
								$sidestones = array();
								$sidestone_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "side_stone WHERE product_option_value_id = '" . (int)$value . "' ");
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
								$multistones = array();
								$multistone_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "multi_stone WHERE product_option_value_id = '" . (int)$value . "' ");
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
								
								
								$option_data[] = array(
									'product_option_id'       => $product_option_id,
									'product_option_value_id' => $value,
									'option_id'               => $option_query->row['option_id'],
									'option_value_id'         => $option_value_query->row['option_value_id'],
									'name'                    => $option_query->row['name'],
									'code'                    => $option_value_query->row['code'],
									'value'                   => $option_value_query->row['name'],
									'type'                    => $option_query->row['type'],
									'quantity'                => $option_value_query->row['quantity'],
									'subtract'                => $option_value_query->row['subtract'],
									'price'                   => $option_value_query->row['price'],
									'price_prefix'            => $option_value_query->row['price_prefix'],
									'points'                  => $option_value_query->row['points'],
									'points_prefix'           => $option_value_query->row['points_prefix'],
									'weight'                  => $option_value_query->row['weight'],
									'weight_prefix'           => $option_value_query->row['weight_prefix'],
									'stone_type'           	  => $option_value_query->row['stone_type'],
									'side_stones'             => $sidestones,
									'multi_stones'            => $multistones
								);
							}
						} elseif ($option_query->row['type'] == 'checkbox' && is_array($value)) {
							foreach ($value as $product_option_value_id) {
								$option_value_query = $this->db->query("SELECT pov.option_value_id, ov.code, pov.quantity, pov.subtract, pov.price, pov.price_prefix, pov.points, pov.points_prefix, pov.weight, pov.weight_prefix, ovd.name, pov.stone_type FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (pov.option_value_id = ovd.option_value_id) WHERE pov.product_option_value_id = '" . (int)$product_option_value_id . "' AND pov.product_option_id = '" . (int)$product_option_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

								if ($option_value_query->num_rows) {
									if ($option_value_query->row['price_prefix'] == '+') {
										$option_price += $option_value_query->row['price'];
									} elseif ($option_value_query->row['price_prefix'] == '-') {
										$option_price -= $option_value_query->row['price'];
									}

									if ($option_value_query->row['points_prefix'] == '+') {
										$option_points += $option_value_query->row['points'];
									} elseif ($option_value_query->row['points_prefix'] == '-') {
										$option_points -= $option_value_query->row['points'];
									}

									if ($option_value_query->row['weight_prefix'] == '+') {
										$option_weight += $option_value_query->row['weight'];
									} elseif ($option_value_query->row['weight_prefix'] == '-') {
										$option_weight -= $option_value_query->row['weight'];
									}

									if ($option_value_query->row['subtract'] && (!$option_value_query->row['quantity'] || ($option_value_query->row['quantity'] < $cart['quantity']))) {
										$stock = false;
									}
									
									//Sidestones..
									$sidestones = array();
									$sidestone_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "side_stone WHERE product_option_value_id = '" . (int)$product_option_value_id . "' ");
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
									$multistones = array();
									$multistone_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "multi_stone WHERE product_option_value_id = '" . (int)$product_option_value_id . "' ");
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

									$option_data[] = array(
										'product_option_id'       => $product_option_id,
										'product_option_value_id' => $product_option_value_id,
										'option_id'               => $option_query->row['option_id'],
										'option_value_id'         => $option_value_query->row['option_value_id'],
										'name'                    => $option_query->row['name'],
										'code'                    => $option_value_query->row['code'],
										'value'                   => $option_value_query->row['name'],
										'type'                    => $option_query->row['type'],
										'quantity'                => $option_value_query->row['quantity'],
										'subtract'                => $option_value_query->row['subtract'],
										'price'                   => $option_value_query->row['price'],
										'price_prefix'            => $option_value_query->row['price_prefix'],
										'points'                  => $option_value_query->row['points'],
										'points_prefix'           => $option_value_query->row['points_prefix'],
										'weight'                  => $option_value_query->row['weight'],
										'weight_prefix'           => $option_value_query->row['weight_prefix'],
										'stone_type'           	  => $option_value_query->row['stone_type'],
										'side_stones'             => $sidestones,
										'multi_stones'            => $multistones
									);
								}
							}
						} elseif ($option_query->row['type'] == 'text' || $option_query->row['type'] == 'textarea' || $option_query->row['type'] == 'file' || $option_query->row['type'] == 'date' || $option_query->row['type'] == 'datetime' || $option_query->row['type'] == 'time') {
							$option_data[] = array(
								'product_option_id'       => $product_option_id,
								'product_option_value_id' => '',
								'option_id'               => $option_query->row['option_id'],
								'option_value_id'         => '',
								'name'                    => $option_query->row['name'],
								'value'                   => $value,
								'type'                    => $option_query->row['type'],
								'quantity'                => '',
								'subtract'                => '',
								'price'                   => '',
								'price_prefix'            => '',
								'points'                  => '',
								'points_prefix'           => '',
								'weight'                  => '',
								'weight_prefix'           => ''
							);
						}
					}
				}

				$price = $product_query->row['price'];

				// Product Discounts
				$discount_quantity = 0;

				foreach ($cart_query->rows as $cart_2) {
					if ($cart_2['product_id'] == $cart['product_id']) {
						$discount_quantity += $cart_2['quantity'];
					}
				}

				$product_discount_query = $this->db->query("SELECT price FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$cart['product_id'] . "' AND customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND quantity <= '" . (int)$discount_quantity . "' AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) ORDER BY quantity DESC, priority ASC, price ASC LIMIT 1");

				if ($product_discount_query->num_rows) {
					$price = $product_discount_query->row['price'];
				}

				// Product Specials
				$product_special_query = $this->db->query("SELECT price FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$cart['product_id'] . "' AND customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) ORDER BY priority ASC, price ASC LIMIT 1");

				if ($product_special_query->num_rows) {
					$price = $product_special_query->row['price'];
				}

				// Reward Points
				$product_reward_query = $this->db->query("SELECT points FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int)$cart['product_id'] . "' AND customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "'");

				if ($product_reward_query->num_rows) {
					$reward = $product_reward_query->row['points'];
				} else {
					$reward = 0;
				}

				// Downloads
				$download_data = array();

				$download_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_download p2d LEFT JOIN " . DB_PREFIX . "download d ON (p2d.download_id = d.download_id) LEFT JOIN " . DB_PREFIX . "download_description dd ON (d.download_id = dd.download_id) WHERE p2d.product_id = '" . (int)$cart['product_id'] . "' AND dd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

				foreach ($download_query->rows as $download) {
					$download_data[] = array(
						'download_id' => $download['download_id'],
						'name'        => $download['name'],
						'filename'    => $download['filename'],
						'mask'        => $download['mask']
					);
				}

				// Stock
				if (!$product_query->row['quantity'] || ($product_query->row['quantity'] < $cart['quantity'])) {
					$stock = false;
				}

				$recurring_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "recurring r LEFT JOIN " . DB_PREFIX . "product_recurring pr ON (r.recurring_id = pr.recurring_id) LEFT JOIN " . DB_PREFIX . "recurring_description rd ON (r.recurring_id = rd.recurring_id) WHERE r.recurring_id = '" . (int)$cart['recurring_id'] . "' AND pr.product_id = '" . (int)$cart['product_id'] . "' AND rd.language_id = " . (int)$this->config->get('config_language_id') . " AND r.status = 1 AND pr.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "'");

				if ($recurring_query->num_rows) {
					$recurring = array(
						'recurring_id'    => $cart['recurring_id'],
						'name'            => $recurring_query->row['name'],
						'frequency'       => $recurring_query->row['frequency'],
						'price'           => $recurring_query->row['price'],
						'cycle'           => $recurring_query->row['cycle'],
						'duration'        => $recurring_query->row['duration'],
						'trial'           => $recurring_query->row['trial_status'],
						'trial_frequency' => $recurring_query->row['trial_frequency'],
						'trial_price'     => $recurring_query->row['trial_price'],
						'trial_cycle'     => $recurring_query->row['trial_cycle'],
						'trial_duration'  => $recurring_query->row['trial_duration']
					);
				} else {
					$recurring = false;
				}

				//Code added by Paul to calculate price starts...
				
				$final_option = array();
				$side_stone = array();
				$multi_stone = array();
				if(!empty($option_data)){
					foreach($option_data as $option){
						$final_option[$option['name']] = $option['code'];
						if(isset($option['side_stones']) && !empty($option['side_stones'])){
							$side_stone = $option['side_stones'];
						}
						if(isset($option['multi_stones']) && !empty($option['multi_stones'])){
							$multi_stone = $option['multi_stones'];
						}
					}
				}
				$final_option['metal_weight'] = $product_query->row['weight'] + $option_weight;
				$final_option['side_stone'] = $side_stone;
				$final_option['multi_stone'] = $multi_stone;
				$final_option['product_markup'] = $product_query->row['product_markup'];
				$final_option['product_type_id'] = $product_query->row['product_type_id'];
				$final_option['multistone'] = $product_query->row['multistone'];
				
				$optioner = $this->calculatePrice($final_option);
				
				$final_price = $optioner['final_price'];
				$metal_weight = $optioner['metal_weight'];
				$no_price = $optioner['no_price'];
				//Code added by Paul to calculate price ends...
				
				
				$product_data[] = array(
					'cart_id'         => $cart['cart_id'],
					'product_id'      => $product_query->row['product_id'],
					'name'            => $product_query->row['name'],
					'model'           => $product_query->row['model'],
'sku'           => $product_query->row['sku'],
					'shipping'        => $product_query->row['shipping'],
					'image'           => $product_query->row['image'],
					'option'          => $option_data,
					'download'        => $download_data,
					'quantity'        => $cart['quantity'],
					'minimum'         => $product_query->row['minimum'],
					'subtract'        => $product_query->row['subtract'],
					'stock'           => $stock,
					'price'           => ($final_price + $option_price),
					'total'           => ($final_price + $option_price) * $cart['quantity'],
					'reward'          => $reward * $cart['quantity'],
					'points'          => ($product_query->row['points'] ? ($product_query->row['points'] + $option_points) * $cart['quantity'] : 0),
					'tax_class_id'    => $product_query->row['tax_class_id'],
					'weight'          => ($product_query->row['weight'] + $option_weight) * $cart['quantity'],
					'metal_weight'    => $metal_weight,
					'weight_class_id' => $product_query->row['weight_class_id'],
					'length'          => $product_query->row['length'],
					'width'           => $product_query->row['width'],
					'height'          => $product_query->row['height'],
					'length_class_id' => $product_query->row['length_class_id'],
					'rrp' 			  => $product_query->row['rrp'],
					'multistone' 	  => $product_query->row['multistone'],
					'recurring'       => $recurring,
					'no_price'        => $no_price
				);
			} else {
				$this->remove($cart['cart_id']);
			}
		}

		return $product_data;
	}
	
	public function calculatePrice($data) {
		
		$mapping = $this->getOptionValueMapping();
		$multi_mapping = $this->getOptionValueMapping();
		
		$default_gravity = 10.80;
		//$default_gravity = 15.50;
		//Code added by Paul to calculate price starts...
		$final_price = 0;
		$stone_price = 0;
		$sidestone_price = 0;
		$multistone_price = 0;
		$no_price = '0';
		//Calculate Metal Price..
		$metal_price = 0;
		$metal_weight = $data['metal_weight'];		
		
		if (isset($data['Metal']) && !empty($data['Metal']))
		{
			$metal_sql = "SELECT price, gravity, code FROM " . DB_PREFIX . "metal_price WHERE  code = '" . $this->db->escape($data['Metal']) . "'";

			$metal_query = $this->db->query($metal_sql);
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
			
			$stone_sql .= " ORDER BY sprice ASC";
			
			if(isset($mapping[$data['Certificate']]['position'])){
				$position = $mapping[$data['Certificate']]['position'];
			} else {
				$position = 1;
			}
			
			$position = (isset($position))?($position-1):1;
			$stone_sql .= " limit $position,1";
			//echo $stone_sql;
			
			$get_stone_price = $this->db->query($stone_sql);
			
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
						$sidestone_sql .= " ORDER BY sprice ASC";
					} else {
						$sidestone_sql .= " ORDER BY mprice ASC";
					}
					
					if(isset($multi_mapping[$sides_lab]['position'])){
						$side_position = $multi_mapping[$sides_lab]['position'];
					} else {
						$side_position = 1;
					}
					
					$side_position = (isset($side_position))?($side_position-1):1;
					$sidestone_sql .= " limit $side_position,1";
			
					//echo $sidestone_sql;
					
					$get_sidestone_price = $this->db->query($sidestone_sql);
					
					if($get_sidestone_price->num_rows){
						if($sides_pieces == 1 && $sides_carat >= 0.20){
							$sprice += $get_sidestone_price->row['sprice'];
							if (isset($multi_mapping[$sides_lab]['markup'])) {
								$side_markup = explode('|',$multi_mapping[$sides_lab]['markup']);
								if(isset($side_markup[0]) && $side_markup[0] > 0){
									$sprice += $sprice * $side_markup[0] + $side_markup[1];
								} elseif(isset($side_markup[1])) {
									$sprice += $sprice + $side_markup[1];
								}
							}
							$sidestone_price += $sprice;
						} else {
							$sidestone_price += $get_sidestone_price->row['mprice'] * ($sides_carat) * $sides_pieces;
						}
					} else {
						$no_price = '1';
					}
				}
			}
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
						$multistone_sql .= " ORDER BY sprice ASC ";
					} else {
						$multistone_sql .= " ORDER BY mprice ASC ";
					}
						
					if(isset($multi_mapping[$multie_lab]['position'])){
						$multi_position = $multi_mapping[$multie_lab]['position'];
					} else {
						$multi_position = 1;
					}
					
					$multi_position = (isset($multi_position))?($multi_position-1):1;
					$multistone_sql .= " limit $multi_position,1";
					
					$get_multistone_price = $this->db->query($multistone_sql);
					
					if($get_multistone_price->num_rows){
						
						if($multie_pieces == '1' && $multie_carat >= 0.20) {
							$mprice += $get_multistone_price->row['sprice'];
							
							if (isset($multi_mapping[$multie_lab]['markup'])) {
								$multi_markup = explode('|',$multi_mapping[$multie_lab]['markup']);
								if(isset($multi_markup[0]) && $multi_markup[0] > 0){
									$mprice += $mprice * $multi_markup[0] + $multi_markup[1];
								} elseif(isset($multi_markup[1])) {
									$mprice += $mprice + $multi_markup[1];
								}
							}
							$multistone_price += $mprice * $multie_pieces;
						} else {
							$multistone_price += $get_multistone_price->row['mprice'] * ($multie_carat) * $multie_pieces;
						}	
					} else {
						$no_price = '1';
					}
				}
			}
		}
		
		$all_stone_price = $stone_price + $sidestone_price + $multistone_price;
		if($metal_price == 0 || $all_stone_price == 0){
			$no_price = '1';
		}
		
		if($no_price == '0'){
			$final_price = $metal_price + $stone_price + $sidestone_price + $multistone_price;
			
			//Add Product Markup..
			if($data['product_markup'] != ''){
				$final_price = $this->addProductMarkup($final_price, $data['product_markup']);
			}
		}
		
		$option = array();
		
		$option['final_price'] 	= $final_price;
		$option['metal_weight'] = $metal_weight;
		$option['no_price'] 	= $no_price;
		
		return $option;
	}

	public function addProductMarkup($price, $code){
		
		if(!empty($code)) {

			$markupuery = $this->db->query("SELECT * FROM " . DB_PREFIX . "markup_product WHERE code = '" . $this->db->escape($code) . "' AND status = '1' ");
			
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
	
	public function add($product_id, $quantity = 1, $option = array(), $recurring_id = 0) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "cart WHERE api_id = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "' AND customer_id = '" . (int)$this->customer->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "' AND product_id = '" . (int)$product_id . "' AND recurring_id = '" . (int)$recurring_id . "' AND `option` = '" . $this->db->escape(json_encode($option)) . "'");

		if (!$query->row['total']) {
			$this->db->query("INSERT " . DB_PREFIX . "cart SET api_id = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "', customer_id = '" . (int)$this->customer->getId() . "', session_id = '" . $this->db->escape($this->session->getId()) . "', product_id = '" . (int)$product_id . "', recurring_id = '" . (int)$recurring_id . "', `option` = '" . $this->db->escape(json_encode($option)) . "', quantity = '" . (int)$quantity . "', date_added = NOW()");
		} else {
			$this->db->query("UPDATE " . DB_PREFIX . "cart SET quantity = (quantity + " . (int)$quantity . ") WHERE api_id = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "' AND customer_id = '" . (int)$this->customer->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "' AND product_id = '" . (int)$product_id . "' AND recurring_id = '" . (int)$recurring_id . "' AND `option` = '" . $this->db->escape(json_encode($option)) . "'");
		}
	}

	public function update($cart_id, $quantity) {
		$this->db->query("UPDATE " . DB_PREFIX . "cart SET quantity = '" . (int)$quantity . "' WHERE cart_id = '" . (int)$cart_id . "' AND api_id = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "' AND customer_id = '" . (int)$this->customer->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "'");
	}

	public function remove($cart_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "cart WHERE cart_id = '" . (int)$cart_id . "' AND api_id = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "' AND customer_id = '" . (int)$this->customer->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "'");
	}

	public function clear() {
		$this->db->query("DELETE FROM " . DB_PREFIX . "cart WHERE api_id = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "' AND customer_id = '" . (int)$this->customer->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "'");
	}

	public function getRecurringProducts() {
		$product_data = array();

		foreach ($this->getProducts() as $value) {
			if ($value['recurring']) {
				$product_data[] = $value;
			}
		}

		return $product_data;
	}

	public function getWeight() {
		$weight = 0;

		foreach ($this->getProducts() as $product) {
			if ($product['shipping']) {
				$weight += $this->weight->convert($product['weight'], $product['weight_class_id'], $this->config->get('config_weight_class_id'));
			}
		}

		return $weight;
	}

	public function getSubTotal() {
		$total = 0;

		foreach ($this->getProducts() as $product) {
			$total += $product['total'];
		}

		return $total;
	}

	public function getTaxes() {
		$tax_data = array();

		foreach ($this->getProducts() as $product) {
			if ($product['tax_class_id']) {
				$tax_rates = $this->tax->getRates($product['price'], $product['tax_class_id']);

				foreach ($tax_rates as $tax_rate) {
					if (!isset($tax_data[$tax_rate['tax_rate_id']])) {
						$tax_data[$tax_rate['tax_rate_id']] = ($tax_rate['amount'] * $product['quantity']);
					} else {
						$tax_data[$tax_rate['tax_rate_id']] += ($tax_rate['amount'] * $product['quantity']);
					}
				}
			}
		}

		return $tax_data;
	}

	public function getTotal() {
		$total = 0;

		foreach ($this->getProducts() as $product) {
			$total += $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity'];
		}

		return $total;
	}

	public function countProducts() {
		$product_total = 0;

		$products = $this->getProducts();

		foreach ($products as $product) {
			$product_total += $product['quantity'];
		}

		return $product_total;
	}

	public function hasProducts() {
		return count($this->getProducts());
	}

	public function hasRecurringProducts() {
		return count($this->getRecurringProducts());
	}

	public function hasStock() {
		foreach ($this->getProducts() as $product) {
			if (!$product['stock']) {
				return false;
			}
		}

		return true;
	}

	public function hasShipping() {
		foreach ($this->getProducts() as $product) {
			if ($product['shipping']) {
				return true;
			}
		}

		return false;
	}

	public function hasDownload() {
		foreach ($this->getProducts() as $product) {
			if ($product['download']) {
				return true;
			}
		}

		return false;
	}
	
	public function getOptionValueMapping()
	{
		$mapping_array = $this->cache->get('option.stone_mapping.' . (int)$this->config->get('config_language_id'));
		if (!$mapping_array)
		{
			$mapping_array = array();
			$mapping_query = $this->db->query("SELECT * FROM  " . DB_PREFIX . "stone_mapping sm"
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

			$this->cache->set('option.stone_mapping.' . (int)$this->config->get('config_language_id') , $mapping_array);
		}

		return $mapping_array;
	}
	
	public function getMultiOptionValueMapping()
	{
		$multi_mapping_array = $this->cache->get('option.multi_stone_mapping.' . (int)$this->config->get('config_language_id'));
		if (!$multi_mapping_array)
		{
			$multi_mapping_array = array();
			$multi_mapping_query = $this->db->query("SELECT * FROM  " . DB_PREFIX . "multistone_mapping sm"
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

			$this->cache->set('option.multi_stone_mapping.' . (int)$this->config->get('config_language_id') , $multi_mapping_array);
		}

		return $multi_mapping_array;
	}
}
