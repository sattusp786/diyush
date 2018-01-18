<?php
class ModelMastersMarkupProduct extends Model {
	public function addProductMarkup($data) {
		
		$this->db->query("INSERT INTO " . DB_PREFIX . "markup_product SET 
		title = '" . $this->db->escape($data['title']) . "', 
		code = '" . $this->db->escape($data['code']) . "', 
		markup = '" . $this->db->escape($data['markup']) . "', 
		status = '" . (int)$data['status'] . "'");

		$markup_product_id = $this->db->getLastId(); 
	}
	
	public function editProductMarkup($markup_product_id, $data) {
	    
		$this->db->query("UPDATE " . DB_PREFIX . "markup_product SET 
		title = '" . $this->db->escape($data['title']) . "', 
		code = '" . $this->db->escape($data['code']) . "', 
		markup = '" . $this->db->escape($data['markup']) . "', 
		status = '" . (int)$data['status'] . "' 		    
		WHERE markup_product_id = '" . (int)$markup_product_id . "'");
	}
	
	public function deleteProductMarkup($markup_product_id) {
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "markup_product WHERE markup_product_id = '" . (int)$markup_product_id . "'");
	}	

	public function getProductMarkup($markup_product_id) {
		 $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "markup_product WHERE markup_product_id = '" . (int)$markup_product_id . "' ");

		 return $query->row;
	}

	public function getProductMarkupsInfo($markup_product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "markup_product WHERE markup_product_id = '" . (int)$markup_product_id . "'");
		
		return $query->row;
		
	}

	
	public function getProductMarkups($data = array()) {
	    
			 $sql = "SELECT * FROM " . DB_PREFIX . "markup_product WHERE 1 ";
			
			if (!empty($data['filter_name'])) {
				$sql .= " AND title LIKE '" . $this->db->escape($data['filter_name']) . "%'";
			}

			$sort_data = array(
				'title',
				'code',
				'markup'
			);		
		
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];	
			} else {
				$sql .= " ORDER BY title";	
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
	
	public function getTotalProductMarkups() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "markup_product");
		
		return $query->row['total'];
	}	
}
