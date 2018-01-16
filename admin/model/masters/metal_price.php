<?php
class ModelMastersMetalPrice extends Model {
	public function addMetalPrice($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "metal_price SET 
			option_value_id = '" . (int)$data['option_value'] . "', 
		    metal_id = '" . (int)$data['metal_id'] . "', 
			code = '" . $this->db->escape($data['code']) . "', 
			purity = '" . $this->db->escape($data['purity']) . "', 
			gravity = '" . $this->db->escape($data['gravity']) . "', 
			percent = '" . (float)$data['percent'] . "', 
			price = '" . (float)$data['price'] . "', 
			markup_rate = '" .$data['markup_rate'] . "',
			sort_order = '" . (int)$data['sort_order'] . "', 
			status = '" . (int)$data['status'] . "'");

		$metal_price_id = $this->db->getLastId(); 
		
		foreach ($data['metal_price_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "metal_price_description SET metal_price_id = '" . (int)$metal_price_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "'");
		}
		
		if (isset($data['metal_price_store'])) {
			foreach ($data['metal_price_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "metal_price_to_store SET metal_price_id = '" . (int)$metal_price_id . "', store_id = '" . (int)$store_id . "'");
			}
		}
		
		$this->cache->delete('metal_price');
		
	}
	
	public function editMetalPrice($metal_price_id, $data) {
		
		$this->db->query("UPDATE " . DB_PREFIX . "metal_price SET 
				option_value_id = '" . (int)$data['option_value'] . "', 
				metal_id = '" . (int)$data['metal_id'] . "', 
				code = '" . $this->db->escape($data['code']) . "', 
				purity = '" . $this->db->escape($data['purity']) . "', 
				gravity = '" . $this->db->escape($data['gravity']) . "', 
				percent = '" . (float)$data['percent'] . "',
				price = '" . (float)$data['price'] . "',
				markup_rate = '" . $data['markup_rate'] . "',
				sort_order = '" . (int)$data['sort_order'] . "', 
				status = '" . (int)$data['status'] . "' 
				WHERE metal_price_id = '" . (int)$metal_price_id . "'");
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "metal_price_description WHERE metal_price_id = '" . (int)$metal_price_id . "'");
					
		foreach ($data['metal_price_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "metal_price_description SET metal_price_id = '" . (int)$metal_price_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "metal_price_to_store WHERE metal_price_id = '" . (int)$metal_price_id . "'");
		
		if (isset($data['metal_price_store'])) {
			foreach ($data['metal_price_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "metal_price_to_store SET metal_price_id = '" . (int)$metal_price_id . "', store_id = '" . (int)$store_id . "'");
			}
		}	
		$this->cache->delete('metal_price');
	}
	
	public function deleteMetalPrice($metal_price_id) {
		
		$data = $this->getMetalPricesInfo($metal_price_id);
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "metal_price WHERE metal_price_id = '" . (int)$metal_price_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "metal_price_description WHERE metal_price_id = '" . (int)$metal_price_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "metal_price_to_store WHERE metal_price_id = '" . (int)$metal_price_id . "'");

		$this->cache->delete('metal_price');
	}	

	public function getMetalPricesInfo($metal_price_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "metal_price WHERE metal_price_id = '" . (int)$metal_price_id . "'");
		
		return $query->row;
		
	}

	public function getMetalPrices($data = array()) {
		if ($data) {
		 $sql = "SELECT i.*,id.name,ovd.name as option_value FROM " . DB_PREFIX . "metal_price i LEFT JOIN " . DB_PREFIX . "metal_price_description id ON (i.metal_price_id = id.metal_price_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ovd.option_value_id = i.option_value_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		
			$sort_data = array(
				'id.name',
				'i.code',
				'i.purity',
				'i.gravity',
				'i.percent',
				'i.sort_order',
				'option_value'
			);		
		
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];	
			} else {
				$sql .= " ORDER BY id.name";	
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
		} else {
			$metal_price_data = $this->cache->get('metal_price.' . (int)$this->config->get('config_language_id'));
		
			if (!$metal_price_data) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "metal_price i LEFT JOIN " . DB_PREFIX . "metal_price_description id ON (i.metal_price_id = id.metal_price_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY i.sort_order");
				$metal_price_data = $query->rows;
				
				$this->cache->set('metal_price.' . (int)$this->config->get('config_language_id'), $metal_price_data);
			}
			return $metal_price_data;			
		}
	}
	
	public function getMetalPriceDescriptions($metal_price_id) {
		$metal_price_description_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "metal_price_description WHERE metal_price_id = '" . (int)$metal_price_id . "'");

		foreach ($query->rows as $result) {
			$metal_price_description_data[$result['language_id']] = array(
				'name'       => $result['name'],
				'description' => $result['description']
			);
		}
		
		return $metal_price_description_data;
	}
	
	public function getMetalPriceStores($metal_price_id) {
		$metal_price_store_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "metal_price_to_store WHERE metal_price_id = '" . (int)$metal_price_id . "'");

		foreach ($query->rows as $result) {
			$metal_price_store_data[] = $result['store_id'];
		}
		
		return $metal_price_store_data;
	}

	public function getTotalMetalPrices() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "metal_price");
		
		return $query->row['total'];
	}	
		
	public function getMetalConversion() {

		$metal_conversion_data = $this->cache->get('metal_conversion');
	
		if (!$metal_conversion_data) {
			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "metal_conversion` ORDER BY metal_conversion_id");
			$metal_conversion_data = $query->rows;
			
			$this->cache->set('metal_conversion', $metal_conversion_data);
		}
		return $metal_conversion_data;			
		
	}
	
	public function getOptionValue()
	{		
		$query = $this->db->query("select option_value_id,name from " . DB_PREFIX . "option_value_description where language_id = '" . (int)$this->config->get('config_language_id') . "' AND option_id='14' Order By name ASC");
					
		return $query->rows;
	}
}
