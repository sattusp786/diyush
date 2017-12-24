<?php
class ModelMastersStonePrice extends Model {
	public function addStonePrice($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "stone_price SET diamond_type = '" . $this->db->escape($data['diamond_type']) . "', shape = '" . $this->db->escape($data['shape']) . "', carat_from = '" . $this->db->escape($data['carat_from']) . "', carat_to = '" . $this->db->escape($data['carat_to']) . "', clarity = '" . $this->db->escape($data['clarity']) . "', color = '" . $this->db->escape($data['color']) . "', lab = '" . $this->db->escape($data['lab']) . "', cut = '" . $this->db->escape($data['cut']) . "', price = '" . (float)$data['price'] . "', status = '" . (int)$data['status'] . "', date_added = NOW()");

		$stone_price_id = $this->db->getLastId();

		return $stone_price_id;
	}

	public function editStonePrice($stone_price_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "stone_price SET diamond_type = '" . $this->db->escape($data['diamond_type']) . "', shape = '" . $this->db->escape($data['shape']) . "', carat_from = '" . $this->db->escape($data['carat_from']) . "', carat_to = '" . $this->db->escape($data['carat_to']) . "', clarity = '" . $this->db->escape($data['clarity']) . "', color = '" . $this->db->escape($data['color']) . "', lab = '" . $this->db->escape($data['lab']) . "', cut = '" . $this->db->escape($data['cut']) . "', price = '" . (float)$data['price'] . "', status = '" . (int)$data['status'] . "' WHERE stone_price_id = '" . (int)$stone_price_id . "'");

	}

	public function deleteStonePrice($stone_price_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "stone_price WHERE stone_price_id = '" . (int)$stone_price_id . "'");
	}

	public function getStonePrice($stone_price_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "stone_price WHERE stone_price_id = '" . (int)$stone_price_id . "'");

		return $query->row;
	}

	public function getStonePriceByCode($code) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "stone_price WHERE code = '" . $this->db->escape($code) . "'");

		return $query->row;
	}

	public function getStonePrices($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "stone_price WHERE 1 ";

		$sort_data = array(
			'stone',
			'shape',
			'crt_from',
			'crt_to',
			'clarity',
			'color',
			'lab',
			'cut',
			'carat_price',
			'total_price',
			'sprice',
			'mprice'
		);
		
		if (!empty($data['filter_stone'])) {
			$sql .= " AND stone LIKE '" . $this->db->escape($data['filter_stone']) . "%'";
		}
		
		if (!empty($data['filter_shape'])) {
			$sql .= " AND shape LIKE '" . $this->db->escape($data['filter_shape']) . "%'";
		}
		
		if (!empty($data['filter_crt_from'])) {
			$sql .= " AND crt_from LIKE '" . $this->db->escape($data['filter_crt_from']) . "%'";
		}
		
		if (!empty($data['filter_crt_to'])) {
			$sql .= " AND crt_to LIKE '" . $this->db->escape($data['filter_crt_to']) . "%'";
		}
		
		if (!empty($data['filter_weight'])) {
			$sql .= " AND weight LIKE '" . $this->db->escape($data['filter_weight']) . "%'";
		}
		
		if (!empty($data['filter_clarity'])) {
			$sql .= " AND clarity LIKE '" . $this->db->escape($data['filter_clarity']) . "%'";
		}
		
		if (!empty($data['filter_color'])) {
			$sql .= " AND color LIKE '" . $this->db->escape($data['filter_color']) . "%'";
		}
		
		if (!empty($data['filter_lab'])) {
			$sql .= " AND lab LIKE '" . $this->db->escape($data['filter_lab']) . "%'";
		}
		
		if (!empty($data['filter_cut'])) {
			$sql .= " AND cut LIKE '" . $this->db->escape($data['filter_cut']) . "%'";
		}

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY stone";
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

	public function getTotalStonePrices() {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "stone_price WHERE 1 ";
		
		if (!empty($data['filter_stone'])) {
			$sql .= " AND stone LIKE '" . $this->db->escape($data['filter_stone']) . "%'";
		}
		
		if (!empty($data['filter_shape'])) {
			$sql .= " AND shape LIKE '" . $this->db->escape($data['filter_shape']) . "%'";
		}
		
		if (!empty($data['filter_crt_from'])) {
			$sql .= " AND crt_from LIKE '" . $this->db->escape($data['filter_crt_from']) . "%'";
		}
		
		if (!empty($data['filter_crt_to'])) {
			$sql .= " AND crt_to LIKE '" . $this->db->escape($data['filter_crt_to']) . "%'";
		}
		
		if (!empty($data['filter_weight'])) {
			$sql .= " AND weight LIKE '" . $this->db->escape($data['filter_weight']) . "%'";
		}
		
		if (!empty($data['filter_clarity'])) {
			$sql .= " AND clarity LIKE '" . $this->db->escape($data['filter_clarity']) . "%'";
		}
		
		if (!empty($data['filter_color'])) {
			$sql .= " AND color LIKE '" . $this->db->escape($data['filter_color']) . "%'";
		}
		
		if (!empty($data['filter_lab'])) {
			$sql .= " AND lab LIKE '" . $this->db->escape($data['filter_lab']) . "%'";
		}
		
		if (!empty($data['filter_cut'])) {
			$sql .= " AND cut LIKE '" . $this->db->escape($data['filter_cut']) . "%'";
		}
		
		$query = $this->db->query($sql);
		
		return $query->row['total'];
	}
	
	public function importStonePrice($import_file) {
	
		$truncate = $this->db->query("TRUNCATE TABLE " . DB_PREFIX . "stone_price ");

		$row = 0;
		if (($handle = fopen($import_file['import_file']['tmp_name'], "r")) !== FALSE) {

			while (($data = fgetcsv($handle, 0, ',', '"')) !== FALSE) {

				$fields = array();
				if ($row == 0 || empty($data[0])) {
					$row++;
					continue;
				}
				
				$data['stone'] 				= $data[1];
				$data['shape'] 				= $data[2];
				$data['crt_from'] 			= $data[3];
				$data['crt_to'] 			= $data[4];
				$data['weight'] 			= $data[5];
				$data['clarity'] 			= $data[6];
				$data['color'] 				= $data[7];
				$data['lab'] 				= $data[8];
				$data['cut'] 				= $data[9];
				$data['polish'] 			= $data[10];
				$data['symmetry'] 			= $data[11];
				$data['fluorescence'] 		= $data[12];
				$data['intensity'] 			= $data[13];
				$data['carat_price'] 		= $data[14];
				$data['total_price'] 		= $data[15];
				$data['sprice'] 			= $data[16];
				$data['mprice'] 			= $data[17];
		
				$this->addStonePrice($data);
				
				$row++;
			}
		}

		fclose($handle);
	}
	
	public function getStonePriceExport($data = array(),$mode=null) {
		$sql = "SELECT * FROM " . DB_PREFIX . "stone_price WHERE 1 ";

		$sort_data = array(
			'stone_price_id',
			'stone',
			'shape',
			'crt_from',
			'crt_to',
			'clarity',
			'color',
			'lab',
			'cut',
			'sprice'
		);
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY stone";
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

			$sql .= " LIMIT " . (int) $data['start'] . "," . (int) $data['limit'];
		}
		$query = $this->db->query($sql);

		return $query->rows;
	}
}