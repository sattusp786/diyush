<?php
class ModelMastersStoneMapping extends Model {
	public function addStoneMapping($data) {


		$this->db->query("INSERT INTO `" . DB_PREFIX . "stone_mapping` SET name = '" . $this->db->escape($data['name']) . "', certificate = '" . $this->db->escape($data['certificate']) . "', position = '" . (int)$data['position'] . "', markup_percent = '" . (float)$data['markup_percent'] . "', markup_fixed = '" . (float)$data['markup_fixed'] . "'");
		
		$stone_mapping_id = $this->db->getLastId();
		if(isset($data['option_value'])) {
			foreach ($data['option_value'] as $language_id => $value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "stone_mapping_value SET stone_mapping_id='".(int)$stone_mapping_id."', option_value = '" . $value['option_value'] . "', option_value_mapping = '" . $value['option_value_mapping'] . "'");
			}
		}
	}
	
	public function editStoneMapping($stone_mapping_id, $data) {
		
		$this->db->query("UPDATE `" . DB_PREFIX . "stone_mapping` SET name = '" . $this->db->escape($data['name']) . "', certificate = '" . $this->db->escape($data['certificate']) . "', position = '" . (int)$data['position'] . "', markup_percent = '" . (float)$data['markup_percent'] . "', markup_fixed = '" . (float)$data['markup_fixed'] . "' WHERE stone_mapping_id = '" . (int)$stone_mapping_id . "'");

		
			$this->db->query("DELETE FROM " . DB_PREFIX . "stone_mapping_value WHERE stone_mapping_id = '" . (int)$stone_mapping_id . "'");

			if(isset($data['option_value'])) {
				foreach ($data['option_value'] as $language_id => $value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "stone_mapping_value SET stone_mapping_id='".(int)$stone_mapping_id."', option_value = '" . $value['option_value'] . "', option_value_mapping = '" . $value['option_value_mapping'] . "'");
			}
		}
	}
	
	

	public function deleteStoneMapping($stone_mapping_id) {
		
		$this->db->query("DELETE FROM `" . DB_PREFIX . "stone_mapping` WHERE stone_mapping_id = '" . (int)$stone_mapping_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "stone_mapping_value WHERE stone_mapping_id = '" . (int)$stone_mapping_id . "'");	
		
	}
	
	
		
	public function getStoneMappings($data = array()) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "stone_mapping`  ";
		
		if (isset($data['filter_name']) && !is_null($data['filter_name'])) {
			$sql .= " AND od.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sort_data = array(
			'name',
			'certificate',
			'position',
			'markup_percent',
			'markup_fixed'
		);	
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];	
		} else {
			$sql .= " ORDER BY name";	
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
	


	public function getTotalStoneMappings() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "stone_mapping`"); 
		
		return $query->row['total'];
	}	
	
	public function getStoneMappingValues($stone_mapping_id) {
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "stone_mapping WHERE stone_mapping_id='".(int)$stone_mapping_id."' ");

		
		return $query->row;
	}


	public function getStoneMappingValueDescriptions($stone_mapping_id) {
		$option_value_data = array();
		
		$option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "stone_mapping_value WHERE stone_mapping_id = '" . (int)$stone_mapping_id . "' ORDER BY option_value ASC ");
				
		foreach ($option_value_query->rows as $option_value) {
			$option_value_description_data = array();
			
			$qur = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "stone_mapping_value WHERE stone_mapping_id=".$option_value['stone_mapping_id']);
			
			$product_option_count=($qur->row['total']>0?$qur->row['total']:'');
			
			$option_value_data[] = array(
				'stone_mapping_value_id'    => $option_value['stone_mapping_value_id'],
				'stone_mapping_id'					=> $option_value['stone_mapping_id'],
				'option_value'					    => $option_value['option_value'],
				'option_value_mapping'				=> $option_value['option_value_mapping'],
				'stone_mapping_count'				=> $product_option_count
				
			);
		}
		
		return $option_value_data;
	}
	
	public function copyStoneMapping($stone_mapping_id) {
	

		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "stone_mapping os  WHERE os.stone_mapping_id = '" . (int)$stone_mapping_id . "' ");
		
		if ($query->num_rows) {
			$data = array();
			
			$data = $query->row;			
						
			$data = array_merge($data, array('option_value' => $this->getStoneMappingValueDescriptions($stone_mapping_id)));
			//echo"<pre>";print_r($data);echo"</pre>";exit;
			
			$this->addStoneMapping($data);
		}
	}
}