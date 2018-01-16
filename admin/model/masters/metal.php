<?php
class ModelMastersMetal extends Model {
	public function addMetal($data) {
		
		$this->db->query("INSERT INTO " . DB_PREFIX . "metal SET 
		code = '" . $this->db->escape($data['code']) . "', 
		price = '" . $this->db->escape($data['price']) . "', 
		sort_order = '" . (int)$data['sort_order'] . "', 
		percent = '" . (int)$data['percent'] . "', 
		status = '" . (int)$data['status'] . "'");

		$metal_id = $this->db->getLastId(); 
		
		foreach ($data['metal_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "metal_description SET metal_id = '" . (int)$metal_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "'");
		}
		
		if (isset($data['metal_store'])) {
			foreach ($data['metal_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "metal_to_store SET metal_id = '" . (int)$metal_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		$this->UpdateMetalPrice($metal_id,$data['price'],$data['percent']);
		
		$this->cache->delete('metal');
		
	}
	
	public function editMetal($metal_id, $data) {
	    
		$this->db->query("UPDATE " . DB_PREFIX . "metal SET 
		code = '" . $this->db->escape($data['code']) . "', 
		price = '" . $this->db->escape($data['price']) . "', 
		sort_order = '" . (int)$data['sort_order'] . "', 
		percent = '" . $data['percent'] . "', 
		status = '" . (int)$data['status'] . "' 		    
		WHERE metal_id = '" . (int)$metal_id . "'");
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "metal_description WHERE metal_id = '" . (int)$metal_id . "'");
					
		
		foreach ($data['metal_description'] as $language_id => $value) {

			$this->db->query("INSERT INTO " . DB_PREFIX . "metal_description SET metal_id = '" . (int)$metal_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "metal_to_store WHERE metal_id = '" . (int)$metal_id . "'");
		
		if (isset($data['metal_store'])) {
			foreach ($data['metal_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "metal_to_store SET metal_id = '" . (int)$metal_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		$this->UpdateMetalPrice($metal_id,$data['price'],$data['percent']);
		
		$this->cache->delete('metal');
	}
	
	public function deleteMetal($metal_id) {
		
		$data = $this->getMetal($metal_id);
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "metal WHERE metal_id = '" . (int)$metal_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "metal_description WHERE metal_id = '" . (int)$metal_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "metal_to_store WHERE metal_id = '" . (int)$metal_id . "'");

		$this->cache->delete('metal');
	}	

	public function getMetal($metal_id) {
		 $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "metal i LEFT JOIN " . DB_PREFIX . "metal_description id ON (i.metal_id = id.metal_id) WHERE i.metal_id = '" . (int)$metal_id . "' AND id.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		 return $query->row;
	
	}

	public function getMetalsInfo($metal_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "metal WHERE metal_id = '" . (int)$metal_id . "'");
		
		return $query->row;
		
	}

	
	public function getMetals($data = array()) {
	    
		if ($data) {
			 $sql = "SELECT * FROM " . DB_PREFIX . "metal i LEFT JOIN " . DB_PREFIX . "metal_description id ON (i.metal_id = id.metal_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "'";
			
			if (!empty($data['filter_name'])) {
				$sql .= " AND id.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
			}

			$sort_data = array(
				'id.name',
				'i.code',
				'i.price',
				'i.percent',
				'i.price',
				'i.sort_order'
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
			$metal_data = $this->cache->get('metal.' . (int)$this->config->get('config_language_id'));
		
			if (!$metal_data) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "metal i LEFT JOIN " . DB_PREFIX . "metal_description id ON (i.metal_id = id.metal_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY id.name");
	
				$metal_data = $query->rows;
			
				$this->cache->set('metal.' . (int)$this->config->get('config_language_id'), $metal_data);
			}	
	
			return $metal_data;			
		}
	}
	
	public function getMetalDescriptions($metal_id) {
		$metal_description_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "metal_description WHERE metal_id = '" . (int)$metal_id . "'");

		foreach ($query->rows as $result) {
			$metal_description_data[$result['language_id']] = array(
				'name'       => $result['name'],
				'description' => $result['description']
			);
		}
		
		return $metal_description_data;
	}
	
	public function getMetalStores($metal_id) {
		$metal_store_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "metal_to_store WHERE metal_id = '" . (int)$metal_id . "'");

		foreach ($query->rows as $result) {
			$metal_store_data[] = $result['store_id'];
		}
		
		return $metal_store_data;
	}

	public function getTotalMetals() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "metal");
		
		return $query->row['total'];
	}	

	public function UpdateMetalPrice($metal_id,$price,$percent) {		
		
		$this->db->query("UPDATE " . DB_PREFIX . "metal_purity SET 
		price = (percent * " . $price / $percent . ")
		WHERE metal_id = '" . (int) $metal_id . "'");

		$this->db->query("UPDATE " . DB_PREFIX . "metal_price SET 
		price = (percent * " . $price / $percent . ")
		WHERE metal_id = '" . (int) $metal_id . "'");
      	
	}	
}
