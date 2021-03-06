<?php
class ModelCatalogCategory extends Model {
	public function addCategory($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "category SET parent_id = '" . (int)$data['parent_id'] . "', `top` = '" . (isset($data['top']) ? (int)$data['top'] : 0) . "', `column` = '" . (int)$data['column'] . "', delivery_days = '" . (int)$data['delivery_days'] . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "', date_modified = NOW(), date_added = NOW()");

		$category_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "category SET image = '" . $this->db->escape($data['image']) . "' WHERE category_id = '" . (int)$category_id . "'");
		}
		if (isset($data['image2'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "category SET image2 = '" . $this->db->escape($data['image2']) . "' WHERE category_id = '" . (int)$category_id . "'");
		}
		if (isset($data['image3'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "category SET image3 = '" . $this->db->escape($data['image3']) . "' WHERE category_id = '" . (int)$category_id . "'");
		}

		foreach ($data['category_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "category_description SET category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		// MySQL Hierarchical Data Closure Table Pattern
		$level = 0;

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$data['parent_id'] . "' ORDER BY `level` ASC");

		foreach ($query->rows as $result) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$category_id . "', `path_id` = '" . (int)$result['path_id'] . "', `level` = '" . (int)$level . "'");

			$level++;
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$category_id . "', `path_id` = '" . (int)$category_id . "', `level` = '" . (int)$level . "'");

		if (isset($data['category_filter'])) {
			foreach ($data['category_filter'] as $filter_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "category_filter SET category_id = '" . (int)$category_id . "', filter_id = '" . (int)$filter_id . "'");
			}
		}
		
		if (isset($data['category_option'])) {
			foreach ($data['category_option'] as $option_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "category_option SET category_id = '" . (int)$category_id . "', option_id = '" . (int)$option_id . "'");
			}
		}

		if (isset($data['category_store'])) {
			foreach ($data['category_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "category_to_store SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "'");
			}
		}
		
		if (isset($data['category_seo_url'])) {
			foreach ($data['category_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'category_id=" . (int)$category_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}
		
		// Set which layout to use with this category
		if (isset($data['category_layout'])) {
			foreach ($data['category_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "category_to_layout SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}

		$this->cache->delete('category');

		return $category_id;
	}

	public function editCategory($category_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "category SET parent_id = '" . (int)$data['parent_id'] . "', `top` = '" . (isset($data['top']) ? (int)$data['top'] : 0) . "', `column` = '" . (int)$data['column'] . "', delivery_days = '" . (int)$data['delivery_days'] . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "', date_modified = NOW() WHERE category_id = '" . (int)$category_id . "'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "category SET image = '" . $this->db->escape($data['image']) . "' WHERE category_id = '" . (int)$category_id . "'");
		}
		if (isset($data['image2'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "category SET image2 = '" . $this->db->escape($data['image2']) . "' WHERE category_id = '" . (int)$category_id . "'");
		}
		if (isset($data['image3'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "category SET image3 = '" . $this->db->escape($data['image3']) . "' WHERE category_id = '" . (int)$category_id . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "category_description WHERE category_id = '" . (int)$category_id . "'");

		foreach ($data['category_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "category_description SET category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		// MySQL Hierarchical Data Closure Table Pattern
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE path_id = '" . (int)$category_id . "' ORDER BY level ASC");

		if ($query->rows) {
			foreach ($query->rows as $category_path) {
				// Delete the path below the current one
				$this->db->query("DELETE FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$category_path['category_id'] . "' AND level < '" . (int)$category_path['level'] . "'");

				$path = array();

				// Get the nodes new parents
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$data['parent_id'] . "' ORDER BY level ASC");

				foreach ($query->rows as $result) {
					$path[] = $result['path_id'];
				}

				// Get whats left of the nodes current path
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$category_path['category_id'] . "' ORDER BY level ASC");

				foreach ($query->rows as $result) {
					$path[] = $result['path_id'];
				}

				// Combine the paths with a new level
				$level = 0;

				foreach ($path as $path_id) {
					$this->db->query("REPLACE INTO `" . DB_PREFIX . "category_path` SET category_id = '" . (int)$category_path['category_id'] . "', `path_id` = '" . (int)$path_id . "', level = '" . (int)$level . "'");

					$level++;
				}
			}
		} else {
			// Delete the path below the current one
			$this->db->query("DELETE FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$category_id . "'");

			// Fix for records with no paths
			$level = 0;

			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$data['parent_id'] . "' ORDER BY level ASC");

			foreach ($query->rows as $result) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET category_id = '" . (int)$category_id . "', `path_id` = '" . (int)$result['path_id'] . "', level = '" . (int)$level . "'");

				$level++;
			}

			$this->db->query("REPLACE INTO `" . DB_PREFIX . "category_path` SET category_id = '" . (int)$category_id . "', `path_id` = '" . (int)$category_id . "', level = '" . (int)$level . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "category_filter WHERE category_id = '" . (int)$category_id . "'");

		if (isset($data['category_filter'])) {
			foreach ($data['category_filter'] as $filter_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "category_filter SET category_id = '" . (int)$category_id . "', filter_id = '" . (int)$filter_id . "'");
			}
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "category_option WHERE category_id = '" . (int)$category_id . "'");

		if (isset($data['category_option'])) {
			foreach ($data['category_option'] as $option_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "category_option SET category_id = '" . (int)$category_id . "', option_id = '" . (int)$option_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "category_to_store WHERE category_id = '" . (int)$category_id . "'");

		if (isset($data['category_store'])) {
			foreach ($data['category_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "category_to_store SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		// SEO URL
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE query = 'category_id=" . (int)$category_id . "'");

		if (isset($data['category_seo_url'])) {
			foreach ($data['category_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'category_id=" . (int)$category_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "category_to_layout WHERE category_id = '" . (int)$category_id . "'");

		if (isset($data['category_layout'])) {
			foreach ($data['category_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "category_to_layout SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}

		$this->cache->delete('category');
	}

	public function deleteCategory($category_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "category_path WHERE category_id = '" . (int)$category_id . "'");

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_path WHERE path_id = '" . (int)$category_id . "'");

		foreach ($query->rows as $result) {
			$this->deleteCategory($result['category_id']);
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "category WHERE category_id = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "category_description WHERE category_id = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "category_filter WHERE category_id = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "category_to_store WHERE category_id = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "category_to_layout WHERE category_id = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE category_id = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'category_id=" . (int)$category_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "coupon_category WHERE category_id = '" . (int)$category_id . "'");

		$this->cache->delete('category');
	}

	public function repairCategories($parent_id = 0) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category WHERE parent_id = '" . (int)$parent_id . "'");

		foreach ($query->rows as $category) {
			// Delete the path below the current one
			$this->db->query("DELETE FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$category['category_id'] . "'");

			// Fix for records with no paths
			$level = 0;

			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$parent_id . "' ORDER BY level ASC");

			foreach ($query->rows as $result) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET category_id = '" . (int)$category['category_id'] . "', `path_id` = '" . (int)$result['path_id'] . "', level = '" . (int)$level . "'");

				$level++;
			}

			$this->db->query("REPLACE INTO `" . DB_PREFIX . "category_path` SET category_id = '" . (int)$category['category_id'] . "', `path_id` = '" . (int)$category['category_id'] . "', level = '" . (int)$level . "'");

			$this->repairCategories($category['category_id']);
		}
	}

	public function getCategory($category_id) {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT GROUP_CONCAT(cd1.name ORDER BY level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (cp.path_id = cd1.category_id AND cp.category_id != cp.path_id) WHERE cp.category_id = c.category_id AND cd1.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY cp.category_id) AS path FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (c.category_id = cd2.category_id) WHERE c.category_id = '" . (int)$category_id . "' AND cd2.language_id = '" . (int)$this->config->get('config_language_id') . "'");
		
		return $query->row;
	}

	public function getCategories($data = array()) {
		$sql = "SELECT cp.category_id AS category_id, GROUP_CONCAT(cd1.name ORDER BY cp.level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') AS name, c1.parent_id, c1.sort_order, c1.delivery_days FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category c1 ON (cp.category_id = c1.category_id) LEFT JOIN " . DB_PREFIX . "category c2 ON (cp.path_id = c2.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (cp.path_id = cd1.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (cp.category_id = cd2.category_id) WHERE cd1.language_id = '" . (int)$this->config->get('config_language_id') . "' AND cd2.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND cd2.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sql .= " GROUP BY cp.category_id";

		$sort_data = array(
			'name',
			'sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY sort_order";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getCategoryDescriptions($category_id) {
		$category_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_description WHERE category_id = '" . (int)$category_id . "'");

		foreach ($query->rows as $result) {
			$category_description_data[$result['language_id']] = array(
				'name'             => $result['name'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword'],
				'description'      => $result['description']
			);
		}

		return $category_description_data;
	}
	
	public function getCategoryPath($category_id) {
		$query = $this->db->query("SELECT category_id, path_id, level FROM " . DB_PREFIX . "category_path WHERE category_id = '" . (int)$category_id . "'");

		return $query->rows;
	}
	
	public function getCategoryFilters($category_id) {
		$category_filter_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_filter WHERE category_id = '" . (int)$category_id . "'");

		foreach ($query->rows as $result) {
			$category_filter_data[] = $result['filter_id'];
		}

		return $category_filter_data;
	}
	
	public function getCategoryOptions($category_id) {
		$category_option_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_option WHERE category_id = '" . (int)$category_id . "'");

		foreach ($query->rows as $result) {
			$category_option_data[] = $result['option_id'];
		}

		return $category_option_data;
	}

	public function getCategoryStores($category_id) {
		$category_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_to_store WHERE category_id = '" . (int)$category_id . "'");

		foreach ($query->rows as $result) {
			$category_store_data[] = $result['store_id'];
		}

		return $category_store_data;
	}
	
	public function getCategorySeoUrls($category_id) {
		$category_seo_url_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'category_id=" . (int)$category_id . "'");

		foreach ($query->rows as $result) {
			$category_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $category_seo_url_data;
	}
	
	public function getCategoryLayouts($category_id) {
		$category_layout_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_to_layout WHERE category_id = '" . (int)$category_id . "'");

		foreach ($query->rows as $result) {
			$category_layout_data[$result['store_id']] = $result['layout_id'];
		}

		return $category_layout_data;
	}

	public function getTotalCategories() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "category");

		return $query->row['total'];
	}
	
	public function getTotalCategoriesByLayoutId($layout_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "category_to_layout WHERE layout_id = '" . (int)$layout_id . "'");

		return $query->row['total'];
	}	
	
	public function getOptionValuesData($product_id) {
		
		$query = $this->db->query("SELECT pov.product_option_value_id, pov.weight, pov.weight_prefix, od.name, ov.code, ov.sort_order, pov.product_option_id, po.required, o.option_id, pov.default FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN ".DB_PREFIX."product_option po ON pov.product_option_id = po.product_option_id LEFT JOIN " . DB_PREFIX . "option o ON ( pov.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON o.option_id=od.option_id LEFT JOIN " . DB_PREFIX . "option_value ov ON ( pov.option_value_id = ov.option_value_id) WHERE pov.product_id =" . $product_id . " AND (pov.`default`=1)");
		
		return $query->rows;
	}

	public function addProductTemp($category_id, $data) {
		
		//carat = 5, clarity = 10, colour = 11, shape = 20
		
		if (isset($data['category_option']) && !empty($data['category_option'])) {
			
			$option_str = implode(",",$this->request->post['category_option']);
			
			//echo "SELECT p.*,pd.name as description FROM " . DB_PREFIX . "product_to_category p2c LEFT JOIN " . DB_PREFIX . "product p ON p2c.product_id = p.product_id LEFT JOIN " . DB_PREFIX . "product_description pd ON p.product_id=pd.product_id WHERE p2c.category_id = '" . (int)$category_id . "' GROUP BY p.product_id";
			
			$get_products = $this->db->query("SELECT p.*,pd.name as description FROM " . DB_PREFIX . "product_to_category p2c LEFT JOIN " . DB_PREFIX . "product p ON p2c.product_id = p.product_id LEFT JOIN " . DB_PREFIX . "product_description pd ON p.product_id=pd.product_id WHERE p2c.category_id = '" . (int)$category_id . "' GROUP BY p.product_id");
			
			if($get_products->num_rows){
				foreach($get_products->rows as $product){
					
					$truncate = $this->db->query("DELETE FROM " . DB_PREFIX . "product_temp WHERE parent_id = '" . (int)$product['product_id'] . "'");
					
					//echo "SELECT * FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON pov.option_value_id=ovd.option_value_id WHERE pov.product_id = '" . (int)$product['product_id'] . "' AND pov.option_id IN (".$option_str.") ORDER BY pov.option_id GROUP BY pov.option_value_id";
					
					$get_options = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON pov.option_value_id = ov.option_value_id LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON pov.option_value_id=ovd.option_value_id WHERE pov.product_id = '" . (int)$product['product_id'] . "' AND pov.option_id IN (".$option_str.") GROUP BY pov.option_value_id ORDER BY pov.option_id ");
					
					$carat_arr = array();
					$clarity_arr = array();
					$color_arr = array();
					$shape_arr = array();
					if($get_options->num_rows){
						$i = 0;
						foreach($get_options->rows as $optioner){
							if($optioner['option_id'] == '5'){
								$carat_arr[$i]['option_value_id'] = $optioner['option_value_id'];
								$carat_arr[$i]['name'] = $optioner['name'];
								$carat_arr[$i]['code'] = $optioner['code'];
							} elseif($optioner['option_id'] == '10'){
								$clarity_arr[$i]['option_value_id'] = $optioner['option_value_id'];
								$clarity_arr[$i]['name'] = $optioner['name'];
								$clarity_arr[$i]['code'] = $optioner['code'];
							} elseif($optioner['option_id'] == '11'){
								$color_arr[$i]['option_value_id'] = $optioner['option_value_id'];
								$color_arr[$i]['name'] = $optioner['name'];
								$color_arr[$i]['code'] = $optioner['code'];
							} elseif($optioner['option_id'] == '20'){
								$shape_arr[$i]['option_value_id'] = $optioner['option_value_id'];
								$shape_arr[$i]['name'] = $optioner['name'];
								$shape_arr[$i]['code'] = $optioner['code'];
							}
							
							$i++;
						}
					}
					
					/*
					echo "<pre>";
					print_r($carat_arr);
					print_r($clarity_arr);
					print_r($color_arr);
					print_r($shape_arr);
					echo "</pre>";
					*/
					
					$option_weight = 0;
					$default_options = array();
					$default_options = $this->getOptionValuesData($product['product_id']);
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
					
					$option_value_ids = '';
					$option_values = '';
					
					if(!empty($carat_arr) && !empty($clarity_arr) && !empty($color_arr) && !empty($shape_arr)){
						if(!empty($carat_arr)){
							foreach($carat_arr as $carat){
								if(!empty($clarity_arr)){
									foreach($clarity_arr as $clarity){
										if(!empty($color_arr)){
											foreach($color_arr as $color){
												if(!empty($shape_arr)){
													foreach($shape_arr as $shape){
														
														$option_value_ids = $carat['option_value_id'].','.$clarity['option_value_id'].','.$color['option_value_id'].','.$shape['option_value_id'];
														$option_values = $carat['name'].','.$clarity['name'].','.$color['name'].','.$shape['name'];
														
														$insert = $this->db->query("INSERT INTO " . DB_PREFIX . "product_temp SET parent_id='".(int)$product['product_id']."', name='".$product['description']."', model='".$product['model']."', sku='".$product['sku']."', quantity='".$product['quantity']."', image='".$product['image']."', price='".$product['price']."', option_ids='".$option_str."', option_value_ids='".$option_value_ids."', option_values='".$option_values."', tax_class_id='".$product['tax_class_id']."', date_available='".$product['date_available']."', weight='".$product['weight']."', weight_class_id='".$product['weight_class_id']."', subtract='".$product['subtract']."', minimum='".$product['minimum']."', sort_order='".$product['sort_order']."', status='".$product['status']."', viewed='".$product['viewed']."', date_added=NOW(), date_modified=NOW() ");
														
														$product_temp_id = $this->db->getLastId();
														
														$default_options[$carat['name']] = $carat['code'];
														$default_options[$clarity['name']] = $clarity['code'];
														$default_options[$color['name']] = $color['code'];
														$default_options[$shape['name']] = $shape['code'];
														
														$product_price = $this->cart->calculatePrice($default_options);
														
														$update = $this->db->query("UPDATE " . DB_PREFIX . "product_temp SET price='".$product_price."' WHERE product_id = '".$product_temp_id."' ");
														
													}
												}
											}
										}
									}
								}
							}
						}
					} elseif(!empty($carat_arr) && !empty($clarity_arr) && !empty($color_arr)){
						if(!empty($carat_arr)){
							foreach($carat_arr as $carat){
								if(!empty($clarity_arr)){
									foreach($clarity_arr as $clarity){
										if(!empty($color_arr)){
											foreach($color_arr as $color){
														
												$option_value_ids = $carat['option_value_id'].','.$clarity['option_value_id'].','.$color['option_value_id'];
												$option_values = $carat['name'].','.$clarity['name'].','.$color['name'];
												
												$insert = $this->db->query("INSERT INTO " . DB_PREFIX . "product_temp SET parent_id='".(int)$product['product_id']."', name='".$product['description']."', model='".$product['model']."', sku='".$product['sku']."', quantity='".$product['quantity']."', image='".$product['image']."', price='".$product['price']."', option_ids='".$option_str."', option_value_ids='".$option_value_ids."', option_values='".$option_values."', tax_class_id='".$product['tax_class_id']."', date_available='".$product['date_available']."', weight='".$product['weight']."', weight_class_id='".$product['weight_class_id']."', subtract='".$product['subtract']."', minimum='".$product['minimum']."', sort_order='".$product['sort_order']."', status='".$product['status']."', viewed='".$product['viewed']."', date_added=NOW(), date_modified=NOW() ");
												
												$product_temp_id = $this->db->getLastId();
														
												$default_options[$carat['name']] = $carat['code'];
												$default_options[$clarity['name']] = $clarity['code'];
												$default_options[$color['name']] = $color['code'];
												
												$product_price = $this->cart->calculatePrice($default_options);
												
												$update = $this->db->query("UPDATE " . DB_PREFIX . "product_temp SET price='".$product_price."' WHERE product_id = '".$product_temp_id."' ");
											}
										}
									}
								}
							}
						}
					}  elseif(!empty($carat_arr) && !empty($clarity_arr) && !empty($shape_arr)){
						if(!empty($carat_arr)){
							foreach($carat_arr as $carat){
								if(!empty($clarity_arr)){
									foreach($clarity_arr as $clarity){
										if(!empty($shape_arr)){
											foreach($shape_arr as $shape){
												
												$option_value_ids = $carat['option_value_id'].','.$clarity['option_value_id'].','.$shape['option_value_id'];
												$option_values = $carat['name'].','.$clarity['name'].','.$shape['name'];
												
												$insert = $this->db->query("INSERT INTO " . DB_PREFIX . "product_temp SET parent_id='".(int)$product['product_id']."', name='".$product['description']."', model='".$product['model']."', sku='".$product['sku']."', quantity='".$product['quantity']."', image='".$product['image']."', price='".$product['price']."', option_ids='".$option_str."', option_value_ids='".$option_value_ids."', option_values='".$option_values."', tax_class_id='".$product['tax_class_id']."', date_available='".$product['date_available']."', weight='".$product['weight']."', weight_class_id='".$product['weight_class_id']."', subtract='".$product['subtract']."', minimum='".$product['minimum']."', sort_order='".$product['sort_order']."', status='".$product['status']."', viewed='".$product['viewed']."', date_added=NOW(), date_modified=NOW() ");
												
												$product_temp_id = $this->db->getLastId();
														
												$default_options[$carat['name']] = $carat['code'];
												$default_options[$clarity['name']] = $clarity['code'];
												$default_options[$shape['name']] = $shape['code'];
												
												$product_price = $this->cart->calculatePrice($default_options);
												
												$update = $this->db->query("UPDATE " . DB_PREFIX . "product_temp SET price='".$product_price."' WHERE product_id = '".$product_temp_id."' ");
											}
										}
									}
								}
							}
						}
					}  elseif(!empty($carat_arr) && !empty($color_arr) && !empty($shape_arr)){
						if(!empty($carat_arr)){
							foreach($carat_arr as $carat){
								
								if(!empty($color_arr)){
									foreach($color_arr as $color){
										if(!empty($shape_arr)){
											foreach($shape_arr as $shape){
												
												$option_value_ids = $carat['option_value_id'].','.$color['option_value_id'].','.$shape['option_value_id'];
												$option_values = $carat['name'].','.$color['name'].','.$shape['name'];
												
												$insert = $this->db->query("INSERT INTO " . DB_PREFIX . "product_temp SET parent_id='".(int)$product['product_id']."', name='".$product['description']."', model='".$product['model']."', sku='".$product['sku']."', quantity='".$product['quantity']."', image='".$product['image']."', price='".$product['price']."', option_ids='".$option_str."', option_value_ids='".$option_value_ids."', option_values='".$option_values."', tax_class_id='".$product['tax_class_id']."', date_available='".$product['date_available']."', weight='".$product['weight']."', weight_class_id='".$product['weight_class_id']."', subtract='".$product['subtract']."', minimum='".$product['minimum']."', sort_order='".$product['sort_order']."', status='".$product['status']."', viewed='".$product['viewed']."', date_added=NOW(), date_modified=NOW() ");
												
												$product_temp_id = $this->db->getLastId();
														
												$default_options[$carat['name']] = $carat['code'];
												$default_options[$color['name']] = $color['code'];
												$default_options[$shape['name']] = $shape['code'];
												
												$product_price = $this->cart->calculatePrice($default_options);
												
												$update = $this->db->query("UPDATE " . DB_PREFIX . "product_temp SET price='".$product_price."' WHERE product_id = '".$product_temp_id."' ");
														
											}
										}
									}
								}
								
							}
						}
					}  elseif(!empty($clarity_arr) && !empty($color_arr) && !empty($shape_arr)){
						
						if(!empty($clarity_arr)){
							foreach($clarity_arr as $clarity){
								if(!empty($color_arr)){
									foreach($color_arr as $color){
										if(!empty($shape_arr)){
											foreach($shape_arr as $shape){
												
												$option_value_ids = $clarity['option_value_id'].','.$color['option_value_id'].','.$shape['option_value_id'];
												$option_values = $clarity['name'].','.$color['name'].','.$shape['name'];
												
												$insert = $this->db->query("INSERT INTO " . DB_PREFIX . "product_temp SET parent_id='".(int)$product['product_id']."', name='".$product['description']."', model='".$product['model']."', sku='".$product['sku']."', quantity='".$product['quantity']."', image='".$product['image']."', price='".$product['price']."', option_ids='".$option_str."', option_value_ids='".$option_value_ids."', option_values='".$option_values."', tax_class_id='".$product['tax_class_id']."', date_available='".$product['date_available']."', weight='".$product['weight']."', weight_class_id='".$product['weight_class_id']."', subtract='".$product['subtract']."', minimum='".$product['minimum']."', sort_order='".$product['sort_order']."', status='".$product['status']."', viewed='".$product['viewed']."', date_added=NOW(), date_modified=NOW() ");
												
												$product_temp_id = $this->db->getLastId();
														
												$default_options[$clarity['name']] = $clarity['code'];
												$default_options[$color['name']] = $color['code'];
												$default_options[$shape['name']] = $shape['code'];
												
												$product_price = $this->cart->calculatePrice($default_options);
												
												$update = $this->db->query("UPDATE " . DB_PREFIX . "product_temp SET price='".$product_price."' WHERE product_id = '".$product_temp_id."' ");
														
											}
										}
									}
								}
							}
						}
						
					}   elseif(!empty($carat_arr) && !empty($clarity_arr)){
						if(!empty($carat_arr)){
							foreach($carat_arr as $carat){
								if(!empty($clarity_arr)){
									foreach($clarity_arr as $clarity){
														
										$option_value_ids = $carat['option_value_id'].','.$clarity['option_value_id'];
										$option_values = $carat['name'].','.$clarity['name'];
										
										$insert = $this->db->query("INSERT INTO " . DB_PREFIX . "product_temp SET parent_id='".(int)$product['product_id']."', name='".$product['description']."', model='".$product['model']."', sku='".$product['sku']."', quantity='".$product['quantity']."', image='".$product['image']."', price='".$product['price']."', option_ids='".$option_str."', option_value_ids='".$option_value_ids."', option_values='".$option_values."', tax_class_id='".$product['tax_class_id']."', date_available='".$product['date_available']."', weight='".$product['weight']."', weight_class_id='".$product['weight_class_id']."', subtract='".$product['subtract']."', minimum='".$product['minimum']."', sort_order='".$product['sort_order']."', status='".$product['status']."', viewed='".$product['viewed']."', date_added=NOW(), date_modified=NOW() ");
										
										$product_temp_id = $this->db->getLastId();
														
										$default_options[$carat['name']] = $carat['code'];
										$default_options[$clarity['name']] = $clarity['code'];
										
										$product_price = $this->cart->calculatePrice($default_options);
										
										$update = $this->db->query("UPDATE " . DB_PREFIX . "product_temp SET price='".$product_price."' WHERE product_id = '".$product_temp_id."' ");
									}
								}
							}
						}
					} elseif(!empty($carat_arr) && !empty($color_arr)){
						if(!empty($carat_arr)){
							foreach($carat_arr as $carat){
								
								if(!empty($color_arr)){
									foreach($color_arr as $color){
												
											$option_value_ids = $carat['option_value_id'].','.$color['option_value_id'];
											$option_values = $carat['name'].','.$color['name'];
											
											$insert = $this->db->query("INSERT INTO " . DB_PREFIX . "product_temp SET parent_id='".(int)$product['product_id']."', name='".$product['description']."', model='".$product['model']."', sku='".$product['sku']."', quantity='".$product['quantity']."', image='".$product['image']."', price='".$product['price']."', option_ids='".$option_str."', option_value_ids='".$option_value_ids."', option_values='".$option_values."', tax_class_id='".$product['tax_class_id']."', date_available='".$product['date_available']."', weight='".$product['weight']."', weight_class_id='".$product['weight_class_id']."', subtract='".$product['subtract']."', minimum='".$product['minimum']."', sort_order='".$product['sort_order']."', status='".$product['status']."', viewed='".$product['viewed']."', date_added=NOW(), date_modified=NOW() ");
											
											$product_temp_id = $this->db->getLastId();
														
											$default_options[$carat['name']] = $carat['code'];
											$default_options[$color['name']] = $color['code'];
											
											$product_price = $this->cart->calculatePrice($default_options);
											
											$update = $this->db->query("UPDATE " . DB_PREFIX . "product_temp SET price='".$product_price."' WHERE product_id = '".$product_temp_id."' ");
											
									}
								}
								
							}
						}
					} elseif(!empty($carat_arr) && !empty($shape_arr)){
						if(!empty($carat_arr)){
							foreach($carat_arr as $carat){
								if(!empty($shape_arr)){
									foreach($shape_arr as $shape){
										
										$option_value_ids = $carat['option_value_id'].','.$shape['option_value_id'];
										$option_values = $carat['name'].','.$shape['name'];
										
										$insert = $this->db->query("INSERT INTO " . DB_PREFIX . "product_temp SET parent_id='".(int)$product['product_id']."', name='".$product['description']."', model='".$product['model']."', sku='".$product['sku']."', quantity='".$product['quantity']."', image='".$product['image']."', price='".$product['price']."', option_ids='".$option_str."', option_value_ids='".$option_value_ids."', option_values='".$option_values."', tax_class_id='".$product['tax_class_id']."', date_available='".$product['date_available']."', weight='".$product['weight']."', weight_class_id='".$product['weight_class_id']."', subtract='".$product['subtract']."', minimum='".$product['minimum']."', sort_order='".$product['sort_order']."', status='".$product['status']."', viewed='".$product['viewed']."', date_added=NOW(), date_modified=NOW() ");
										
										$product_temp_id = $this->db->getLastId();
														
										$default_options[$carat['name']] = $carat['code'];
										$default_options[$shape['name']] = $shape['code'];
										
										$product_price = $this->cart->calculatePrice($default_options);
										
										$update = $this->db->query("UPDATE " . DB_PREFIX . "product_temp SET price='".$product_price."' WHERE product_id = '".$product_temp_id."' ");
									}
								}
							}
						}
					} elseif(!empty($clarity_arr) && !empty($color_arr)){
						
						if(!empty($clarity_arr)){
							foreach($clarity_arr as $clarity){
								if(!empty($color_arr)){
									foreach($color_arr as $color){
										
												
											$option_value_ids = $clarity['option_value_id'].','.$color['option_value_id'];
											$option_values = $clarity['name'].','.$color['name'];
											
											$insert = $this->db->query("INSERT INTO " . DB_PREFIX . "product_temp SET parent_id='".(int)$product['product_id']."', name='".$product['description']."', model='".$product['model']."', sku='".$product['sku']."', quantity='".$product['quantity']."', image='".$product['image']."', price='".$product['price']."', option_ids='".$option_str."', option_value_ids='".$option_value_ids."', option_values='".$option_values."', tax_class_id='".$product['tax_class_id']."', date_available='".$product['date_available']."', weight='".$product['weight']."', weight_class_id='".$product['weight_class_id']."', subtract='".$product['subtract']."', minimum='".$product['minimum']."', sort_order='".$product['sort_order']."', status='".$product['status']."', viewed='".$product['viewed']."', date_added=NOW(), date_modified=NOW() ");
											
											$product_temp_id = $this->db->getLastId();
														
											$default_options[$clarity['name']] = $clarity['code'];
											$default_options[$color['name']] = $color['code'];
											
											$product_price = $this->cart->calculatePrice($default_options);
											
											$update = $this->db->query("UPDATE " . DB_PREFIX . "product_temp SET price='".$product_price."' WHERE product_id = '".$product_temp_id."' ");
											
									}
								}
							}
						}
							
					} elseif(!empty($clarity_arr) && !empty($shape_arr)){
						if(!empty($clarity_arr)){
							foreach($clarity_arr as $clarity){
								if(!empty($shape_arr)){
									foreach($shape_arr as $shape){
										
										$option_value_ids = $clarity['option_value_id'].','.$shape['option_value_id'];
										$option_values = $clarity['name'].','.$shape['name'];
										
										$insert = $this->db->query("INSERT INTO " . DB_PREFIX . "product_temp SET parent_id='".(int)$product['product_id']."', name='".$product['description']."', model='".$product['model']."', sku='".$product['sku']."', quantity='".$product['quantity']."', image='".$product['image']."', price='".$product['price']."', option_ids='".$option_str."', option_value_ids='".$option_value_ids."', option_values='".$option_values."', tax_class_id='".$product['tax_class_id']."', date_available='".$product['date_available']."', weight='".$product['weight']."', weight_class_id='".$product['weight_class_id']."', subtract='".$product['subtract']."', minimum='".$product['minimum']."', sort_order='".$product['sort_order']."', status='".$product['status']."', viewed='".$product['viewed']."', date_added=NOW(), date_modified=NOW() ");
										
										$product_temp_id = $this->db->getLastId();
														
										$default_options[$clarity['name']] = $clarity['code'];
										$default_options[$shape['name']] = $shape['code'];
										
										$product_price = $this->cart->calculatePrice($default_options);
										
										$update = $this->db->query("UPDATE " . DB_PREFIX . "product_temp SET price='".$product_price."' WHERE product_id = '".$product_temp_id."' ");
									}
								}	
							}
						}
							
					} elseif(!empty($color_arr) && !empty($shape_arr)){
						
						if(!empty($color_arr)){
							foreach($color_arr as $color){
								if(!empty($shape_arr)){
									foreach($shape_arr as $shape){
										
										$option_value_ids = $color['option_value_id'].','.$shape['option_value_id'];
										$option_values = $color['name'].','.$shape['name'];
										
										$insert = $this->db->query("INSERT INTO " . DB_PREFIX . "product_temp SET parent_id='".(int)$product['product_id']."', name='".$product['description']."', model='".$product['model']."', sku='".$product['sku']."', quantity='".$product['quantity']."', image='".$product['image']."', price='".$product['price']."', option_ids='".$option_str."', option_value_ids='".$option_value_ids."', option_values='".$option_values."', tax_class_id='".$product['tax_class_id']."', date_available='".$product['date_available']."', weight='".$product['weight']."', weight_class_id='".$product['weight_class_id']."', subtract='".$product['subtract']."', minimum='".$product['minimum']."', sort_order='".$product['sort_order']."', status='".$product['status']."', viewed='".$product['viewed']."', date_added=NOW(), date_modified=NOW() ");
										
										$product_temp_id = $this->db->getLastId();
														
										$default_options[$color['name']] = $color['code'];
										$default_options[$shape['name']] = $shape['code'];
										
										$product_price = $this->cart->calculatePrice($default_options);
										
										$update = $this->db->query("UPDATE " . DB_PREFIX . "product_temp SET price='".$product_price."' WHERE product_id = '".$product_temp_id."' ");
									}
								}
							}
						}
					} elseif(!empty($carat_arr)){
						foreach($carat_arr as $carat){
									
							$option_value_ids = $carat['option_value_id'];
							$option_values = $carat['name'];
							
							$insert = $this->db->query("INSERT INTO " . DB_PREFIX . "product_temp SET parent_id='".(int)$product['product_id']."', name='".$product['description']."', model='".$product['model']."', sku='".$product['sku']."', quantity='".$product['quantity']."', image='".$product['image']."', price='".$product['price']."', option_ids='".$option_str."', option_value_ids='".$option_value_ids."', option_values='".$option_values."', tax_class_id='".$product['tax_class_id']."', date_available='".$product['date_available']."', weight='".$product['weight']."', weight_class_id='".$product['weight_class_id']."', subtract='".$product['subtract']."', minimum='".$product['minimum']."', sort_order='".$product['sort_order']."', status='".$product['status']."', viewed='".$product['viewed']."', date_added=NOW(), date_modified=NOW() ");
							
							$product_temp_id = $this->db->getLastId();
														
							$default_options[$carat['name']] = $carat['code'];
							
							$product_price = $this->cart->calculatePrice($default_options);
							
							$update = $this->db->query("UPDATE " . DB_PREFIX . "product_temp SET price='".$product_price."' WHERE product_id = '".$product_temp_id."' ");
						}
					} elseif(!empty($clarity_arr)){
						foreach($clarity_arr as $clarity){
									
							$option_value_ids = $clarity['option_value_id'];
							$option_values = $clarity['name'];
							
							$insert = $this->db->query("INSERT INTO " . DB_PREFIX . "product_temp SET parent_id='".(int)$product['product_id']."', name='".$product['description']."', model='".$product['model']."', sku='".$product['sku']."', quantity='".$product['quantity']."', image='".$product['image']."', price='".$product['price']."', option_ids='".$option_str."', option_value_ids='".$option_value_ids."', option_values='".$option_values."', tax_class_id='".$product['tax_class_id']."', date_available='".$product['date_available']."', weight='".$product['weight']."', weight_class_id='".$product['weight_class_id']."', subtract='".$product['subtract']."', minimum='".$product['minimum']."', sort_order='".$product['sort_order']."', status='".$product['status']."', viewed='".$product['viewed']."', date_added=NOW(), date_modified=NOW() ");
							
							$product_temp_id = $this->db->getLastId();
														
							$default_options[$clarity['name']] = $clarity['code'];
							
							$product_price = $this->cart->calculatePrice($default_options);
							
							$update = $this->db->query("UPDATE " . DB_PREFIX . "product_temp SET price='".$product_price."' WHERE product_id = '".$product_temp_id."' ");
						}
					} elseif(!empty($color_arr)){
						foreach($color_arr as $color){
									
							$option_value_ids = $color['option_value_id'];
							$option_values = $color['name'];
							
							$insert = $this->db->query("INSERT INTO " . DB_PREFIX . "product_temp SET parent_id='".(int)$product['product_id']."', name='".$product['description']."', model='".$product['model']."', sku='".$product['sku']."', quantity='".$product['quantity']."', image='".$product['image']."', price='".$product['price']."', option_ids='".$option_str."', option_value_ids='".$option_value_ids."', option_values='".$option_values."', tax_class_id='".$product['tax_class_id']."', date_available='".$product['date_available']."', weight='".$product['weight']."', weight_class_id='".$product['weight_class_id']."', subtract='".$product['subtract']."', minimum='".$product['minimum']."', sort_order='".$product['sort_order']."', status='".$product['status']."', viewed='".$product['viewed']."', date_added=NOW(), date_modified=NOW() ");
							
							$product_temp_id = $this->db->getLastId();
														
							$default_options[$color['name']] = $color['code'];
							
							$product_price = $this->cart->calculatePrice($default_options);
							
							$update = $this->db->query("UPDATE " . DB_PREFIX . "product_temp SET price='".$product_price."' WHERE product_id = '".$product_temp_id."' ");
						}
					} elseif(!empty($shape_arr)){
						foreach($shape_arr as $shape){
									
							$option_value_ids = $shape['option_value_id'];
							$option_values = $shape['name'];
							
							$insert = $this->db->query("INSERT INTO " . DB_PREFIX . "product_temp SET parent_id='".(int)$product['product_id']."', name='".$product['description']."', model='".$product['model']."', sku='".$product['sku']."', quantity='".$product['quantity']."', image='".$product['image']."', price='".$product['price']."', option_ids='".$option_str."', option_value_ids='".$option_value_ids."', option_values='".$option_values."', tax_class_id='".$product['tax_class_id']."', date_available='".$product['date_available']."', weight='".$product['weight']."', weight_class_id='".$product['weight_class_id']."', subtract='".$product['subtract']."', minimum='".$product['minimum']."', sort_order='".$product['sort_order']."', status='".$product['status']."', viewed='".$product['viewed']."', date_added=NOW(), date_modified=NOW() ");
							
							$product_temp_id = $this->db->getLastId();
														
							$default_options[$shape['name']] = $shape['code'];
							
							$product_price = $this->cart->calculatePrice($default_options);
							
							$update = $this->db->query("UPDATE " . DB_PREFIX . "product_temp SET price='".$product_price."' WHERE product_id = '".$product_temp_id."' ");
						}
					}
					
					
					/*
					$product_option = array();
					if(!empty($option_arr)){
						foreach($option_arr as $option_id){
							$a = 0;
							foreach($option_arr[$option_id] as $optioner){
								$b = 0;
								foreach()
								$product_option[$a][$b] = 
							}
						}
					}
					*/
					
					
				}
			}
			
		}
		
		return '1';
	}	
}