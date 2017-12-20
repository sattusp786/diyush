<?php
class ModelCatalogEnquiry extends Model {
	public function addEnquiry($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "enquiry SET name = '" . $this->db->escape($data['name']) . "', lname = '" . $this->db->escape($data['lname']) . "', subject = '" . $this->db->escape($data['subject']) . "', email = '" . $this->db->escape($data['email']) . "', text = '" . $this->db->escape($data['text']) . "', phone = '" . $this->db->escape($data['phone']) . "', address = '" . $this->db->escape($data['address']) . "', ip = '" . $this->db->escape($data['ip']) . "' ");

		$enquiry_id = $this->db->getLastId();

		return $enquiry_id;
	}

	public function editEnquiry($enquiry_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "enquiry SET name = '" . $this->db->escape($data['name']) . "', lname = '" . $this->db->escape($data['lname']) . "', subject = '" . $this->db->escape($data['subject']) . "', email = '" . $this->db->escape($data['email']) . "', text = '" . $this->db->escape($data['text']) . "', phone = '" . $this->db->escape($data['phone']) . "', address = '" . $this->db->escape($data['address']) . "', ip = '" . $this->db->escape($data['ip']) . "' WHERE enquiry_id = '" . (int)$enquiry_id . "'");
	}

	public function deleteEnquiry($enquiry_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "enquiry WHERE enquiry_id = '" . (int)$enquiry_id . "'");
	}

	public function getEnquiry($enquiry_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "enquiry e WHERE e.enquiry_id = '" . (int)$enquiry_id . "'");

		return $query->row;
	}

	public function getEnquirys($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "enquiry e WHERE 1 ";

		if (!empty($data['filter_name'])) {
			$sql .= " AND e.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_lname'])) {
			$sql .= " AND e.lname LIKE '" . $this->db->escape($data['filter_lname']) . "%'";
		}
		
		if (!empty($data['filter_subject'])) {
			$sql .= " AND e.subject LIKE '" . $this->db->escape($data['filter_subject']) . "%'";
		}
		
		if (!empty($data['filter_text'])) {
			$sql .= " AND e.text LIKE '" . $this->db->escape($data['filter_text']) . "%'";
		}
		
		if (!empty($data['filter_email'])) {
			$sql .= " AND e.email LIKE '" . $this->db->escape($data['filter_email']) . "%'";
		}
		
		if (!empty($data['filter_phone'])) {
			$sql .= " AND e.phone LIKE '" . $this->db->escape($data['filter_phone']) . "%'";
		}
		
		if (!empty($data['filter_address'])) {
			$sql .= " AND e.address LIKE '" . $this->db->escape($data['filter_address']) . "%'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND e.status = '" . (int)$data['filter_status'] . "'";
		}

		$sort_data = array(
			'e.name',
			'e.lname',
			'e.subject',
			'e.text',
			'e.email',
			'e.phone',
			'e.address',
			'e.status',
			'e.date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY e.date_added";
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

	public function getTotalEnquirys($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "enquiry e WHERE 1 ";

		if (!empty($data['filter_name'])) {
			$sql .= " AND e.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_lname'])) {
			$sql .= " AND e.lname LIKE '" . $this->db->escape($data['filter_lname']) . "%'";
		}
		
		if (!empty($data['filter_subject'])) {
			$sql .= " AND e.subject LIKE '" . $this->db->escape($data['filter_subject']) . "%'";
		}
		
		if (!empty($data['filter_text'])) {
			$sql .= " AND e.text LIKE '" . $this->db->escape($data['filter_text']) . "%'";
		}
		
		if (!empty($data['filter_email'])) {
			$sql .= " AND e.email LIKE '" . $this->db->escape($data['filter_email']) . "%'";
		}
		
		if (!empty($data['filter_phone'])) {
			$sql .= " AND e.phone LIKE '" . $this->db->escape($data['filter_phone']) . "%'";
		}
		
		if (!empty($data['filter_address'])) {
			$sql .= " AND e.address LIKE '" . $this->db->escape($data['filter_address']) . "%'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND e.status = '" . (int)$data['filter_status'] . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}
}