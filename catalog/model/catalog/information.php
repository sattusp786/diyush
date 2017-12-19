<?php
class ModelCatalogInformation extends Model {
	public function getInformation($information_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "information i LEFT JOIN " . DB_PREFIX . "information_description id ON (i.information_id = id.information_id) LEFT JOIN " . DB_PREFIX . "information_to_store i2s ON (i.information_id = i2s.information_id) WHERE i.information_id = '" . (int)$information_id . "' AND id.language_id = '" . (int)$this->config->get('config_language_id') . "' AND i2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND i.status = '1'");

		return $query->row;
	}

	public function getInformations() {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "information i LEFT JOIN " . DB_PREFIX . "information_description id ON (i.information_id = id.information_id) LEFT JOIN " . DB_PREFIX . "information_to_store i2s ON (i.information_id = i2s.information_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' AND i2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND i.status = '1' ORDER BY i.sort_order, LCASE(id.title) ASC");

		return $query->rows;
	}

	public function getInformationLayoutId($information_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "information_to_layout WHERE information_id = '" . (int)$information_id . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

		if ($query->num_rows) {
			return (int)$query->row['layout_id'];
		} else {
			return 0;
		}
	}
	
	public function getBlog($blog_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "easy_blog_article_description WHERE article_id='".$blog_id."' ");

		return $query->row;
	}
	
	public function getTotalBlogs() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "easy_blog_article");

		return $query->row['total'];
	}
	
	public function getBlogs($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "easy_blog_article b LEFT JOIN " . DB_PREFIX . "easy_blog_article_description bd ON (b.article_id = bd.article_id) WHERE bd.language_id = '" . (int)$this->config->get('config_language_id') . "' ";

		$sql .= " GROUP BY b.article_id";

		$sort_data = array(
			'bd.name',
			'b.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY b.sort_order";
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
	
	public function getTestimonials($business_id) {
		 
		$feed = $this->cache->get('testimonial.'.$business_id.'.' . (int) $this->config->get('config_store_id') . '.' . (int)$this->config->get('config_language_id'));

		$api_key = 'nHyxvSGxmDEg7DAuJWgRGZrGTLFm1RwK';
		
		if (!$feed) {

		$reviews_url = 'https://api.trustpilot.com/v1/business-units/'. $business_id . '/reviews?stars=5,4&apikey='.$api_key;
		
		$feed = $this->is_curl($reviews_url, 1);
		if(empty($feed))
			$feed = $this->is_curl($reviews_url, 0);
			
		$feed = json_decode($feed,true);
		
		$this->cache->set('testimonial.'.$business_id.'.' . (int) $this->config->get('config_store_id') . '.' .  (int)$this->config->get('config_language_id'), $feed, 24*3600);
		}
		
		return $feed;
	}
	
	public function getTestimonialCount($business_id) {
		
		$feed = $this->cache->get('testimonial.'.$business_id.'.count.' . (int) $this->config->get('config_store_id') . '.' . (int)$this->config->get('config_language_id'));

		$api_key = 'nHyxvSGxmDEg7DAuJWgRGZrGTLFm1RwK';
		
		if (!$feed) {
			
		$count_url = 'https://api.trustpilot.com/v1/business-units/' . $business_id . '?apikey='.$api_key;
		
		
		$feed = $this->is_curl($count_url, 1);
		if(empty($feed))
			$feed = $this->is_curl($count_url, 0);
			
		$feed = json_decode($feed,true);
		
		$this->cache->set('testimonial.'.$business_id.'.count.' . (int) $this->config->get('config_store_id') . '.' .  (int)$this->config->get('config_language_id'), $feed, 24*3600);
		}
		
		return $feed;
	}
	
	private function is_curl($url, $is_curl) {

		if ($is_curl == 1) {
			$ch = curl_init();									// Initiate a CURL object
			curl_setopt($ch, CURLOPT_URL, $url);				// Set the URL
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);		// Set to return a string
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);		// Set the timeout
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);		// Follow URL Redirection
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_AUTOREFERER, true );
			$output = curl_exec($ch);							// Execute the API call
			curl_close($ch);									// Close the CURL object
		} else {
			$output = file_get_contents($url);
		}

		return $output;
	}
	
	public function addEnquiry($data) {
		
		$clientuseripadd = $_SERVER['REMOTE_ADDR'];
		
		if(!isset($data['enquiry_type_id'])){
			$data['enquiry_type_id'] = '0';
		}
		if(!isset($data['name'])){
			$data['name'] = '';
		}
		if(!isset($data['lname'])){
			$data['lname'] = '';
		}
		if(!isset($data['subject'])){
			$data['subject'] = '';
		}
		if(!isset($data['text'])){
			$data['text'] = '';
		}
		if(!isset($data['email'])){
			$data['email'] = '';
		}
		if(!isset($data['phone'])){
			$data['phone'] = '';
		}
		if(!isset($data['address'])){
			$data['address'] = '';
		}
		
		$query = $this->db->query("INSERT INTO " . DB_PREFIX . "enquiry SET enquiry_type_id='".$data['enquiry_type_id']."', name='".$data['name']."', lname='".$data['lname']."', subject='".$data['subject']."', text='".$data['text']."', email='".$data['email']."', phone='".$data['phone']."', address='".$data['address']."', enquiry_status='1', ip='".$clientuseripadd."', date_added=NOW(), date_modified=NOW(), status='1' ");

		return '1';
	}
}