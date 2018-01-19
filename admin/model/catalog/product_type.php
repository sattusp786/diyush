<?php
class ModelCatalogProductType extends Model {
	public function addProductType($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "product_type SET name = '" . $this->db->escape($data['name']) . "', product_group = '" . $this->db->escape($data['product_group']) . "' ");
	}

	public function editProductType($product_type_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "product_type SET name = '" . $this->db->escape($data['name']) . "', product_group = '" . $this->db->escape($data['product_group']) . "' WHERE product_type_id = '" . (int)$product_type_id . "'");
				
	}

	public function deleteProductType($product_type_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_type WHERE product_type_id = '" . (int)$product_type_id . "'");
	}

	public function getProductType($product_type_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_type e WHERE e.product_type_id = '" . (int)$product_type_id . "'");

		return $query->row;
	}

	public function getProductTypes($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "product_type WHERE 1 ";

		if (!empty($data['filter_product_group'])) {
			$sql .= " AND product_group LIKE '" . $this->db->escape($data['filter_product_group']) . "%'";
		}

		if (!empty($data['filter_name'])) {
			$sql .= " AND name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sort_data = array(
			'name',
			'product_group'
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

	public function getTotalProductTypes($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_type WHERE 1 ";

		if (!empty($data['filter_product_group'])) {
			$sql .= " AND product_group LIKE '" . $this->db->escape($data['filter_product_group']) . "%'";
		}

		if (!empty($data['filter_name'])) {
			$sql .= " AND name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}
}