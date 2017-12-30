<?php
class ModelCatalogBespoke extends Model {
	public function addBespoke($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "bespoke SET title = '" . $this->db->escape($data['title']) . "', description = '" . $this->db->escape($data['description']) . "', sort_order = '" . $this->db->escape($data['sort_order']) . "', status = '" . $this->db->escape($data['status']) . "' ");

		$bespoke_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "bespoke SET image = '" . $this->db->escape($data['image']) . "' WHERE bespoke_id = '" . (int)$bespoke_id . "'");
		}
		
		return $bespoke_id;
	}

	public function editBespoke($bespoke_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "bespoke SET title = '" . $this->db->escape($data['title']) . "', description = '" . $this->db->escape($data['description']) . "', sort_order = '" . $this->db->escape($data['sort_order']) . "', status = '" . $this->db->escape($data['status']) . "' WHERE bespoke_id = '" . (int)$bespoke_id . "'");
		
		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "bespoke SET image = '" . $this->db->escape($data['image']) . "' WHERE bespoke_id = '" . (int)$bespoke_id . "'");
		}
	}

	public function deleteBespoke($bespoke_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "bespoke WHERE bespoke_id = '" . (int)$bespoke_id . "'");
	}

	public function getBespoke($bespoke_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "bespoke e WHERE e.bespoke_id = '" . (int)$bespoke_id . "'");

		return $query->row;
	}

	public function getBespokes($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "bespoke WHERE 1 ";

		if (!empty($data['filter_title'])) {
			$sql .= " AND title LIKE '" . $this->db->escape($data['filter_title']) . "%'";
		}

		if (!empty($data['filter_description'])) {
			$sql .= " AND description LIKE '" . $this->db->escape($data['filter_description']) . "%'";
		}
		
		if (!empty($data['filter_sort_order'])) {
			$sql .= " AND sort_order = '" . $this->db->escape($data['filter_sort_order']) . "'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND status = '" . (int)$data['filter_status'] . "'";
		}

		$sort_data = array(
			'title',
			'description',
			'sort_order',
			'status',
			'date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY date_added";
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

	public function getTotalBespokes($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "bespoke WHERE 1 ";

		if (!empty($data['filter_title'])) {
			$sql .= " AND title LIKE '" . $this->db->escape($data['filter_title']) . "%'";
		}

		if (!empty($data['filter_description'])) {
			$sql .= " AND description LIKE '" . $this->db->escape($data['filter_description']) . "%'";
		}
		
		if (!empty($data['filter_sort_order'])) {
			$sql .= " AND sort_order = '" . $this->db->escape($data['filter_sort_order']) . "'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND status = '" . (int)$data['filter_status'] . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}
}