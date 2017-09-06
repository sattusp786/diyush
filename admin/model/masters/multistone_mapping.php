<?php
class ModelMastersMultiStoneMapping extends Model {
	public function addMultiStoneMapping($data) {


		$this->db->query("INSERT INTO `" . DB_PREFIX . "multistone_mapping` SET name = '" . $this->db->escape($data['name']) . "', certificate = '" . $this->db->escape($data['certificate']) . "', total = '" . (int)$data['total'] . "', markup_percent = '" . (float)$data['markup_percent'] . "', markup_fixed = '" . (float)$data['markup_fixed'] . "'");
		
		$multistone_mapping_id = $this->db->getLastId();
		if(isset($data['option_value'])) {
			foreach ($data['option_value'] as $language_id => $value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "multistone_mapping_value SET multistone_mapping_id='".(int)$multistone_mapping_id."', option_value = '" . $value['option_value'] . "', option_value_mapping = '" . $value['option_value_mapping'] . "'");
			}
		}
	}
	
	public function editMultiStoneMapping($multistone_mapping_id, $data) {
		
		$this->db->query("UPDATE `" . DB_PREFIX . "multistone_mapping` SET name = '" . $this->db->escape($data['name']) . "', certificate = '" . $this->db->escape($data['certificate']) . "', total = '" . (int)$data['total'] . "', markup_percent = '" . (float)$data['markup_percent'] . "', markup_fixed = '" . (float)$data['markup_fixed'] . "' WHERE multistone_mapping_id = '" . (int)$multistone_mapping_id . "'");

		
			$this->db->query("DELETE FROM " . DB_PREFIX . "multistone_mapping_value WHERE multistone_mapping_id = '" . (int)$multistone_mapping_id . "'");

			if(isset($data['option_value'])) {
				foreach ($data['option_value'] as $language_id => $value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "multistone_mapping_value SET multistone_mapping_id='".(int)$multistone_mapping_id."', option_value = '" . $value['option_value'] . "', option_value_mapping = '" . $value['option_value_mapping'] . "'");
			}
		}
	}
	
	

	public function deleteMultiStoneMapping($multistone_mapping_id) {
		
		$this->db->query("DELETE FROM `" . DB_PREFIX . "multistone_mapping` WHERE multistone_mapping_id = '" . (int)$multistone_mapping_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "multistone_mapping_value WHERE multistone_mapping_id = '" . (int)$multistone_mapping_id . "'");	
		
	}
	
	
		
	public function getMultiStoneMappings($data = array()) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "multistone_mapping`  ";
		
		if (isset($data['filter_name']) && !is_null($data['filter_name'])) {
			$sql .= " AND od.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sort_data = array(
			'name',
			'certificate',
			'total',
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
	


	public function getTotalMultiStoneMappings() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "multistone_mapping`"); 
		
		return $query->row['total'];
	}	
	
	public function getMultiStoneMappingValues($multistone_mapping_id) {
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "multistone_mapping WHERE multistone_mapping_id='".(int)$multistone_mapping_id."' ");

		
		return $query->row;
	}


	public function getMultiStoneMappingValueDescriptions($multistone_mapping_id) {
		$option_value_data = array();
		
		$option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "multistone_mapping_value WHERE multistone_mapping_id = '" . (int)$multistone_mapping_id . "' ORDER BY option_value ASC ");
				
		foreach ($option_value_query->rows as $option_value) {
			$option_value_description_data = array();
			
			$qur = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "multistone_mapping_value WHERE multistone_mapping_id=".$option_value['multistone_mapping_id']);
			
			$product_option_count=($qur->row['total']>0?$qur->row['total']:'');
			
			$option_value_data[] = array(
				'multistone_mapping_value_id'    => $option_value['multistone_mapping_value_id'],
				'multistone_mapping_id'					=> $option_value['multistone_mapping_id'],
				'option_value'					    => $option_value['option_value'],
				'option_value_mapping'				=> $option_value['option_value_mapping'],
				'multistone_mapping_count'				=> $product_option_count
				
			);
		}
		
		return $option_value_data;
	}
	
	public function copyMultiStoneMapping($multistone_mapping_id) {
	

		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "multistone_mapping os  WHERE os.multistone_mapping_id = '" . (int)$multistone_mapping_id . "' ");
		
		if ($query->num_rows) {
			$data = array();
			
			$data = $query->row;			
						
			$data = array_merge($data, array('option_value' => $this->getMultiStoneMappingValueDescriptions($multistone_mapping_id)));
			//echo"<pre>";print_r($data);echo"</pre>";exit;
			
			$this->addMultiStoneMapping($data);
		}
	}
}