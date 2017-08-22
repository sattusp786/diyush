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
		$sql = "SELECT * FROM " . DB_PREFIX . "stone_price";

		$sort_data = array(
			'diamond_type',
			'shape',
			'carat_from',
			'carat_to',
			'clarity',
			'color',
			'lab',
			'cut',
			'price'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY diamond_type";
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
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "stone_price");

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
				
				$data['diamond_type'] 		= $data[1];
				$data['shape'] 				= $data[2];
				$data['carat_from'] 		= $data[3];
				$data['carat_to'] 			= $data[4];
				$data['clarity'] 			= $data[5];
				$data['color'] 				= $data[6];
				$data['lab'] 				= $data[7];
				$data['cut'] 				= $data[8];
				$data['price'] 				= $data[9];
				$data['status'] 			= $data[10];
		
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
			'diamond_type',
			'shape',
			'carat_from',
			'carat_to',
			'clarity',
			'color',
			'lab',
			'cut',
			'price'
		);
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY diamond_type";
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