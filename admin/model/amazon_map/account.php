<?php
/**
 * @version [2.3.x.x] [Supported opencart version 2.3.x.x]
 * @category Webkul
 * @package Opencart-Amazon Connector
 * @author [Webkul] <[<http://webkul.com/>]>
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
class ModelAmazonMapAccount extends Model {

	/**
	 * [getAmazonAccount to get Amazon Account list or particular account details]
	 * @param  array  $data [filter data array]
	 * @return [type]       [list of amazon accounts]
	 */
	public function getAmazonAccount($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "amazon_accounts WHERE 1 ";

		if (!empty($data['filter_account_id'])) {
			$sql .= " AND id = '" . (int)$data['filter_account_id'] . "'";
		}

		if (!empty($data['filter_store_name'])) {
			$sql .= " AND wk_amazon_connector_store_name LIKE '%" . $this->db->escape($data['filter_store_name']) . "%'";
		}

		if (!empty($data['filter_seller_id'])) {
			$sql .= " AND wk_amazon_connector_seller_id LIKE '%" . $this->db->escape($data['filter_seller_id']) . "%'";
		}

		if (!empty($data['filter_marketplace_id'])) {
			$sql .= " AND wk_amazon_connector_marketplace_id LIKE '%" . $this->db->escape($data['filter_marketplace_id']) . "%'";
		}

		if (!empty($data['filter_added_date'])) {
			$sql .= " AND wk_amazon_connector_date_added LIKE '%" . $this->db->escape($data['filter_added_date']) . "%'";
		}

		$sort_data = array(
			'id',
			'wk_amazon_connector_store_name',
			'wk_amazon_connector_seller_id',
			'wk_amazon_connector_marketplace_id',
			'wk_amazon_connector_date_added',
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY id";
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

	/**
	 * [getTotalAmazonAccount to get the total number of amazon account]
	 * @param  array  $data [filter data array]
	 * @return [type]       [total number of amazon account records]
	 */
	public function getTotalAmazonAccount($data = array()) {
		$sql = "SELECT COUNT(DISTINCT id) AS total FROM " . DB_PREFIX . "amazon_accounts WHERE 1 ";

		if (!empty($data['filter_account_id'])) {
			$sql .= " AND id = '" . (int)$data['filter_account_id'] . "'";
		}

		if (!empty($data['filter_store_name'])) {
			$sql .= " AND wk_amazon_connector_store_name LIKE '%" . $this->db->escape($data['filter_store_name']) . "%'";
		}

		if (!empty($data['filter_seller_id'])) {
			$sql .= " AND wk_amazon_connector_seller_id LIKE '%" . $this->db->escape($data['filter_seller_id']) . "%'";
		}

		if (!empty($data['filter_marketplace_id'])) {
			$sql .= " AND wk_amazon_connector_marketplace_id LIKE '%" . $this->db->escape($data['filter_marketplace_id']) . "%'";
		}

		if (!empty($data['filter_added_date'])) {
			$sql .= " AND wk_amazon_connector_date_added LIKE '%" . $this->db->escape($data['filter_added_date']) . "%'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	/**
	 * [__addAmazonAccount to add/update the amazon account details]
	 * @param  array  $data [details of amazon account]
	 * @return [type]       [description]
	 */
	public function __addAmazonAccount($data = array()){
		if(isset($data['account_id']) && $data['account_id']){
			$this->db->query("UPDATE ".DB_PREFIX."amazon_accounts SET `wk_amazon_connector_store_name` = '".$this->db->escape($data['wk_amazon_connector_store_name'])."', `wk_amazon_connector_country` = '".$this->db->escape($data['wk_amazon_connector_country'])."', `wk_amazon_connector_seller_id` = '".$this->db->escape($data['wk_amazon_connector_seller_id'])."', `wk_amazon_connector_marketplace_id` = '".$this->db->escape($data['wk_amazon_connector_marketplace_id'])."', `wk_amazon_connector_access_key_id` = '".$this->db->escape($data['wk_amazon_connector_access_key_id'])."', `wk_amazon_connector_secret_key` = '".$this->db->escape($data['wk_amazon_connector_secret_key'])."', `wk_amazon_connector_currency_rate` = '".$this->db->escape($data['wk_amazon_connector_currency_rate'])."', `wk_amazon_connector_currency_code` = '".$this->db->escape($data['wk_amazon_connector_currency_code'])."', `wk_amazon_connector_attribute_group` = '".(int)$data['wk_amazon_connector_attribute_group']."' WHERE `id` = '".(int)$data['account_id']."' ");
		}else{
			$this->db->query("INSERT INTO ".DB_PREFIX."amazon_accounts SET `wk_amazon_connector_store_name` = '".$this->db->escape($data['wk_amazon_connector_store_name'])."', `wk_amazon_connector_country` = '".$this->db->escape($data['wk_amazon_connector_country'])."', `wk_amazon_connector_seller_id` = '".$this->db->escape($data['wk_amazon_connector_seller_id'])."', `wk_amazon_connector_marketplace_id` = '".$this->db->escape($data['wk_amazon_connector_marketplace_id'])."', `wk_amazon_connector_access_key_id` = '".$this->db->escape($data['wk_amazon_connector_access_key_id'])."', `wk_amazon_connector_secret_key` = '".$this->db->escape($data['wk_amazon_connector_secret_key'])."', `wk_amazon_connector_currency_rate` = '".$this->db->escape($data['wk_amazon_connector_currency_rate'])."', `wk_amazon_connector_currency_code` = '".$this->db->escape($data['wk_amazon_connector_currency_code'])."', `wk_amazon_connector_attribute_group` = '".(int)$data['wk_amazon_connector_attribute_group']."', wk_amazon_connector_date_added = NOW() ");
		}
	}



	/**
	 * [deleteAccount to delete the amazon account]
	 * @param  boolean $account_id [amazon account id]
	 * @return [type]              [description]
	 */
	public function deleteAccount($account_id = false){
		if($account_id){
			$this->db->query("DELETE FROM ".DB_PREFIX."amazon_accounts WHERE id = '".(int)$account_id."' ");
		}
	}

}
