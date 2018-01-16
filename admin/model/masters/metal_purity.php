<?php
class ModelMastersMetalPurity extends Model {
	public function addMetalPurity($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "metal_purity SET 
			option_value_id = '" . (int)$data['option_value'] . "', 
		    metal_id = '" . (int)$data['metal_id'] . "', 
			code = '" . $this->db->escape($data['code']) . "', 
			purity = '" . $this->db->escape($data['purity']) . "', 
			gravity = '" . $this->db->escape($data['gravity']) . "', 
			percent = '" . (float)$data['percent'] . "', 
			markup_rate = '" .$data['markup_rate'] . "',
			sort_order = '" . (int)$data['sort_order'] . "', 
			status = '" . (int)$data['status'] . "'");

		$metal_purity_id = $this->db->getLastId(); 
		
		foreach ($data['metal_purity_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "metal_purity_description SET metal_purity_id = '" . (int)$metal_purity_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "'");
		}
		
		if (isset($data['metal_purity_store'])) {
			foreach ($data['metal_purity_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "metal_purity_to_store SET metal_purity_id = '" . (int)$metal_purity_id . "', store_id = '" . (int)$store_id . "'");
			}
		}
		
		$this->cache->delete('metal_purity');
		
	}
	
	public function editMetalPurity($metal_purity_id, $data) {
		
		$this->db->query("UPDATE " . DB_PREFIX . "metal_purity SET 
				option_value_id = '" . (int)$data['option_value'] . "', 
				metal_id = '" . (int)$data['metal_id'] . "', 
				code = '" . $this->db->escape($data['code']) . "', 
				purity = '" . $this->db->escape($data['purity']) . "', 
				gravity = '" . $this->db->escape($data['gravity']) . "', 
				percent = '" . (float)$data['percent'] . "',
				markup_rate = '" . $data['markup_rate'] . "',
				sort_order = '" . (int)$data['sort_order'] . "', 
				status = '" . (int)$data['status'] . "' 
				WHERE metal_purity_id = '" . (int)$metal_purity_id . "'");
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "metal_purity_description WHERE metal_purity_id = '" . (int)$metal_purity_id . "'");
					
		foreach ($data['metal_purity_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "metal_purity_description SET metal_purity_id = '" . (int)$metal_purity_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "metal_purity_to_store WHERE metal_purity_id = '" . (int)$metal_purity_id . "'");
		
		if (isset($data['metal_purity_store'])) {
			foreach ($data['metal_purity_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "metal_purity_to_store SET metal_purity_id = '" . (int)$metal_purity_id . "', store_id = '" . (int)$store_id . "'");
			}
		}	
		$this->cache->delete('metal_purity');
	}
	
	public function deleteMetalPurity($metal_purity_id) {
		
		$data = $this->getMetalPuritysInfo($metal_purity_id);
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "metal_purity WHERE metal_purity_id = '" . (int)$metal_purity_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "metal_purity_description WHERE metal_purity_id = '" . (int)$metal_purity_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "metal_purity_to_store WHERE metal_purity_id = '" . (int)$metal_purity_id . "'");

		$this->cache->delete('metal_purity');
	}	

	public function getMetalPuritysInfo($metal_purity_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "metal_purity WHERE metal_purity_id = '" . (int)$metal_purity_id . "'");
		
		return $query->row;
		
	}

	public function getMetalPuritys($data = array()) {
		if ($data) {
		 $sql = "SELECT i.*,id.name,ovd.name as option_value FROM " . DB_PREFIX . "metal_purity i LEFT JOIN " . DB_PREFIX . "metal_purity_description id ON (i.metal_purity_id = id.metal_purity_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ovd.option_value_id = i.option_value_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		
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
			$metal_purity_data = $this->cache->get('metal_purity.' . (int)$this->config->get('config_language_id'));
		
			if (!$metal_purity_data) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "metal_purity i LEFT JOIN " . DB_PREFIX . "metal_purity_description id ON (i.metal_purity_id = id.metal_purity_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY i.sort_order");
				$metal_purity_data = $query->rows;
				
				$this->cache->set('metal_purity.' . (int)$this->config->get('config_language_id'), $metal_purity_data);
			}
			return $metal_purity_data;			
		}
	}
	
	public function getMetalPurityDescriptions($metal_purity_id) {
		$metal_purity_description_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "metal_purity_description WHERE metal_purity_id = '" . (int)$metal_purity_id . "'");

		foreach ($query->rows as $result) {
			$metal_purity_description_data[$result['language_id']] = array(
				'name'       => $result['name'],
				'description' => $result['description']
			);
		}
		
		return $metal_purity_description_data;
	}
	
	public function getMetalPurityStores($metal_purity_id) {
		$metal_purity_store_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "metal_purity_to_store WHERE metal_purity_id = '" . (int)$metal_purity_id . "'");

		foreach ($query->rows as $result) {
			$metal_purity_store_data[] = $result['store_id'];
		}
		
		return $metal_purity_store_data;
	}

	public function getTotalMetalPuritys() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "metal_purity");
		
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
