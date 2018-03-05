<?php
class ModelCatalogFilterMeta extends Model {
	public function addInformationFilter($data) {

		if (isset($data['category_filter']) && !empty($data['category_filter'])) {
			$data['keyword'] = implode(',', $data['category_filter']);
		} else {
			$data['keyword'] = '';
		}

			
		$this->db->query("INSERT INTO " . DB_PREFIX . "information_filter SET category_id = '" . (int)$data['category_id'] . "', `nofollow` = '" . (isset($data['nofollow']) ? (int)$data['nofollow'] : 0) . "',  `noindex` = '" . (isset($data['noindex']) ? (int)$data['noindex'] : 0) . "', keyword = '" . $this->db->escape($data['keyword']) . "', status = '" . (int)$data['status'] . "', sort_order = '" . (int)$data['sort_order'] . "', date_added = NOW()");
		
		$information_filter_id = $this->db->getLastId();
		
		foreach ($data['information_filter_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "information_filter_description SET information_filter_id = '" . (int)$information_filter_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', heading_title = '" . $this->db->escape($value['heading_title']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "',meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "' , description = '" . $this->db->escape($value['description']) . "'");
		}

		if (isset($data['information_store'])) {
			foreach ($data['information_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "information_filter_to_store SET information_filter_id = '" . (int)$information_filter_id . "', store_id = '" . (int)$store_id . "'");
			}
		}
		
		$this->cache->delete('information_filter');
	}
	
	public function editInformationFilter($information_filter_id, $data) {

		if (isset($data['category_filter']) && !empty($data['category_filter'])) {
			$data['keyword'] = implode(',', $data['category_filter']);
		} else {
			$data['keyword'] = '';
		}
		
		$this->db->query("UPDATE " . DB_PREFIX . "information_filter SET category_id = '" . (int)$data['category_id'] . "', `nofollow` = '" . (isset($data['nofollow']) ? (int)$data['nofollow'] : 0) . "',  `noindex` = '" . (isset($data['noindex']) ? (int)$data['noindex'] : 0) . "', keyword = '" . $this->db->escape($data['keyword']) . "',  status = '" . (int)$data['status'] . "', sort_order = '" . (int)$data['sort_order'] . "', date_modified = NOW() WHERE information_filter_id = '" . (int)$information_filter_id . "'");
				
		foreach ($data['information_filter_description'] as $language_id => $value) {

			$this->db->query("DELETE FROM " . DB_PREFIX . "information_filter_description WHERE information_filter_id = '" . (int)$information_filter_id . "' AND language_id = '" . (int)$language_id . "'");

			$this->db->query("INSERT INTO " . DB_PREFIX . "information_filter_description SET information_filter_id = '" . (int)$information_filter_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "',heading_title = '" . $this->db->escape($value['heading_title']) . "',meta_title = '" . $this->db->escape($value['meta_title']) . "',meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', description = '" . $this->db->escape($value['description']) . "'");
		}

		if (isset($data['information_store'])) {		
			foreach ($data['information_store'] as $store_id) {

				$this->db->query("DELETE FROM " . DB_PREFIX . "information_filter_to_store WHERE information_filter_id = '" . (int)$information_filter_id . "' AND store_id = '" . (int)$store_id . "'");

				$this->db->query("INSERT INTO " . DB_PREFIX . "information_filter_to_store SET information_filter_id = '" . (int)$information_filter_id . "', store_id = '" . (int)$store_id . "'");
			}
		}
		
		$this->cache->delete('information_filter');
	}
	
	public function deleteInformationFilter($information_filter_id) {
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "information_filter WHERE information_filter_id = '" . (int)$information_filter_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "information_filter_description WHERE information_filter_id = '" . (int)$information_filter_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "information_filter_to_store WHERE information_filter_id = '" . (int)$information_filter_id . "'");		
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'information_filter_id=" . (int)$information_filter_id. "'");
		
		$this->cache->delete('information_filter');
	}
	
	public function getInformationFilter($information_filter_id) {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT cd1.name FROM " . DB_PREFIX . "category_description cd1 
					WHERE cd1.category_id = ib.category_id AND cd1.language_id = '" . (int)$this->config->get('config_language_id') . "') AS path	 FROM " . DB_PREFIX . "information_filter ib LEFT JOIN " . DB_PREFIX . "information_filter_description ibd ON (ib.information_filter_id = ibd.information_filter_id) WHERE ib.information_filter_id = '" . (int)$information_filter_id . "' AND ibd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
				
		return $query->row;
	}
	
	public function getInformationFilters($data = array()) {
		if ($data) {
			$sql = "SELECT ib.*, ibd.title, (SELECT cd1.name FROM " . DB_PREFIX . "category_description cd1 
					WHERE cd1.category_id = ib.category_id AND cd1.language_id = '" . (int)$this->config->get('config_language_id') . "') AS category FROM " . DB_PREFIX . "information_filter ib LEFT JOIN " . DB_PREFIX . "information_filter_description ibd ON (ib.information_filter_id = ibd.information_filter_id) WHERE ibd.language_id = '" . (int)$this->config->get('config_language_id') . "'"; 
		
			if (isset($data['filter_title']) && !is_null($data['filter_title'])) {
				$sql .= " AND LCASE(ibd.title) LIKE '" . $this->db->escape(strtolower($data['filter_title'])) . "%'";
			}
			
			if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
				$sql .= " AND ib.status = '" . (int)$data['filter_status'] . "'";
			}

			$sort_data = array(
				'ibd.title',				
				'category',				
				'ib.status',
				'ib.sort_order'
			);	
			
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];	
			} else {
				$sql .= " ORDER BY ibd.title";	
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
			$qa_data = $this->cache->get('information_filter.' . $this->config->get('config_language_id'));
		
			if (!$qa_data) {
				$query = $this->db->query("SELECT ib.*, ibd.title FROM " . DB_PREFIX . "information_filter ib LEFT JOIN " . DB_PREFIX . "information_filter_description ibd ON (ib.information_filter_id = ibd.information_filter_id)  WHERE ibd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND ibd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY ibd.title ASC");
	
				$qa_data = $query->rows;
			
				$this->cache->set('information_filter.' . $this->config->get('config_language_id'), $qa_data);
			}	
	
			return $qa_data;
		}
	}
	
	public function getInformationFilterDescriptions($information_filter_id) {
		$information_filter_description_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "information_filter_description WHERE information_filter_id = '" . (int)$information_filter_id . "'");
		
		foreach ($query->rows as $result) {
			$information_filter_description_data[$result['language_id']] = array(
				'title'             => $result['title'],
				'heading_title'     => $result['heading_title'],
			    'meta_title'		=> $result['meta_title'],
				'meta_keyword'     => $result['meta_keyword'],
				'meta_description' => $result['meta_description'],
				'description'      => $result['description'],
			);
		}
		
		return $information_filter_description_data;
	}


	public function getInformationFilterStores($information_filter_id) {
		$information_filter_store_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "information_filter_to_store WHERE information_filter_id = '" . (int)$information_filter_id . "'");

		foreach ($query->rows as $result) {
			$information_filter_store_data[] = $result['store_id'];
		}
		
		return $information_filter_store_data;
	}
	
	public function geTotaltInformationFilters($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "information_filter ib LEFT JOIN " . DB_PREFIX . "information_filter_description ibd ON (ib.information_filter_id = ibd.information_filter_id) WHERE ibd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		
		if (isset($data['filter_title']) && !is_null($data['filter_title'])) {
			$sql .= " AND LCASE(ibd.title) LIKE '%" . $this->db->escape(strtolower($data['filter_title'])) . "%'";
		}
		
		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND ib.status = '" . (int)$data['filter_status'] . "'";
		}

		$query = $this->db->query($sql);
		
		return $query->row['total'];
	}
	
	public function importMannualUpload($import_file) {

		$files = $import_file['import_file']['name'];

		
		if (!file_exists(DIR_DOWNLOAD . "information_filter")) {
			mkdir(DIR_DOWNLOAD . "information_filter");
		}

		$files = "information_filter_" . date("Ymdhis") . ".csv";
		$uploads_dir = DIR_DOWNLOAD . "information_filter" . DIRECTORY_SEPARATOR . $files;
		move_uploaded_file($import_file['import_file']['tmp_name'], $uploads_dir);

		$empty = 0;
		$sucess = 0;
		$i = 1;
		$sql = '';
		$row = 0;
		$rc_file = $uploads_dir;
		$getfile = fopen($rc_file, 'rb');

		while (($data = fgetcsv($getfile, 0, ',', '"')) !== FALSE) {
			if ($row == 0) {
				$row++;
				continue;
			} else {
				if($data[0] != '')
				{	
					$data['keyword']     	= explode(',', $data[0]);
					$data['category_id'] 	= $data[1];
					$data['sort_order'] 	= $data[11];
					$data['status'] 		= $data[10];

					$languages = explode(",",$data[9]);
					foreach($languages as $language_id) {
						$data['information_filter_description'][$language_id] = array(
							'title' 			=> $data[2],
							'heading_title' 	=> $data[3],
							'meta_title' 		=> $data[4],
							'meta_description' 	=> $data[5],
							'meta_keyword' 		=> $data[6],
							'description' 		=> $data[7]
						);
					}
					
					$data['information_store'] = explode(",",$data[8]);

					$check_info = $this->db->query("SELECT * FROM " . DB_PREFIX . "information_filter WHERE keyword='".$this->db->escape($data[0])."' AND category_id = '". (int)$data[1] . "'");
					if($check_info->num_rows > 0)
					{
						$this->editInformationFilter($check_info->row['information_filter_id'], $data);	
					}
					else
					{
						$this->addInformationFilter($data);							
					}
					
					$sucess++;
				} else {
					$empty++;
				}
			}
			$row++;
		}

		fclose($getfile);
		return $empty . '-' . $sucess;
	}
	
	public function batchInsert($sql, $sqlSet = array(), $limit = 100) {

		$sqlSetCount = count($sqlSet);
		$startIndex = 0;
		$sqlStr = array();
		$loop = 0;

		if ($sqlSetCount > 0) {
			if ($sqlSetCount > 100) {
				$loop = (int) ceil($sqlSetCount / 100);
			} else {
				$loop = 1;
			}

			for ($l = 1; $l <= $loop; $l++) {
				$sqlStr = array_slice($sqlSet, $startIndex, $limit);
				$sqlStr = implode(",", $sqlStr);

				$this->db->query($sql . $sqlStr);

				$startIndex = $startIndex + 100;
				unset($sqlStr);
			}
		}
	}
}
?>