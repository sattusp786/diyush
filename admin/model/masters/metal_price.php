<?php
class ModelMastersMetalPrice extends Model {
	public function addMetalPrice($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "metal_price SET name = '" . $this->db->escape($data['name']) . "', code = '" . $this->db->escape($data['code']) . "', price = '" . (float)$data['price'] . "', status = '" . (int)$data['status'] . "', date_added = NOW()");

		$metal_price_id = $this->db->getLastId();

		return $metal_price_id;
	}

	public function editMetalPrice($metal_price_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "metal_price SET name = '" . $this->db->escape($data['name']) . "', code = '" . $this->db->escape($data['code']) . "', price = '" . (float)$data['price'] . "', status = '" . (int)$data['status'] . "' WHERE metal_price_id = '" . (int)$metal_price_id . "'");

	}

	public function deleteMetalPrice($metal_price_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "metal_price WHERE metal_price_id = '" . (int)$metal_price_id . "'");
	}

	public function getMetalPrice($metal_price_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "metal_price WHERE metal_price_id = '" . (int)$metal_price_id . "'");

		return $query->row;
	}

	public function getMetalPriceByCode($code) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "metal_price WHERE code = '" . $this->db->escape($code) . "'");

		return $query->row;
	}

	public function getMetalPrices($data = array()) {
		$sql = "SELECT metal_price_id, name, code, price, status FROM " . DB_PREFIX . "metal_price";

		$sort_data = array(
			'name',
			'code',
			'price',
			'status'
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

	public function getTotalMetalPrices() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "metal_price");

		return $query->row['total'];
	}
	
	public function importMetalPrice($import_file) {
	
		$truncate = $this->db->query("TRUNCATE TABLE " . DB_PREFIX . "metal_price ");

		$row = 0;
		if (($handle = fopen($import_file['import_file']['tmp_name'], "r")) !== FALSE) {

			while (($data = fgetcsv($handle, 0, ',', '"')) !== FALSE) {

				$fields = array();
				if ($row == 0 || empty($data[0])) {
					$row++;
					continue;
				}
				
				$data['name'] 		= $data[1];
				$data['code'] 		= $data[2];
				$data['price'] 		= $data[3];
				$data['status'] 	= $data[4];
		
				$this->addMetalPrice($data);
				
				$row++;
			}
		}

		fclose($handle);
	}
	
	public function getMetalPriceExport($data = array(),$mode=null) {
		$sql = "SELECT * FROM " . DB_PREFIX . "metal_price WHERE 1 ";

		$sort_data = array(
			'metal_price_id',
			'name',
			'code',
			'price'
		);
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY code";
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