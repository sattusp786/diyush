<?php
/**
 * @version [2.3.x.x] [Supported opencart version 2.3.x.x]
 * @category Webkul
 * @package Opencart-Amazon Connector
 * @author [Webkul] <[<http://webkul.com/>]>
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
class ModelExtensionModuleWkAmazonConnector extends Model {

	/**
	 * [createTable to create the module tables]
	 * @return [type] [description]
	 */
	public function createTables(){
		/**
		 * create table : "amazon_accounts"
		 */
		$this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "amazon_accounts (
				                        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
				                        `wk_amazon_connector_store_name` varchar(500) NOT NULL,
																`wk_amazon_connector_attribute_group` int(50) NOT NULL,
				                        `wk_amazon_connector_country` varchar(50) NOT NULL,
				                        `wk_amazon_connector_seller_id` varchar(500) NOT NULL,
				                        `wk_amazon_connector_marketplace_id` varchar(1000) NOT NULL,
				                        `wk_amazon_connector_access_key_id` varchar(1000) NOT NULL,
				                        `wk_amazon_connector_secret_key` varchar(1000) NOT NULL,
				                        `wk_amazon_connector_currency_rate` varchar(100) NOT NULL,
																`wk_amazon_connector_currency_code` varchar(10) NOT NULL,
				                        `wk_amazon_connector_listing_report_id` varchar(500) NOT NULL,
				                        `wk_amazon_connector_inventory_report_id` varchar(500) NOT NULL,
				                        `wk_amazon_connector_date_added` datetime NOT NULL,
				                        PRIMARY KEY (`id`) ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1"
									);
			/**
			 * create table : "amazon_product_fields"
			 */
			$this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "amazon_product_fields (
																	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
																	`product_id` int(50) NOT NULL,
																	`main_product_type` varchar(20) NOT NULL,
																	`main_product_type_value` varchar(50) NOT NULL,
																	PRIMARY KEY (`id`) ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1"
										);

		/**
		 * create table : "amazon_product_map"
		 */
		$this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "amazon_product_map (
				                        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
				                        `oc_product_id` int(50) NOT NULL,
				                        `amazon_product_id` varchar(500) NOT NULL,
																`amazon_product_sku` varchar(500) NOT NULL,
																`amazon_image` varchar(500) NOT NULL,
				                        `oc_category_id` int(50) NOT NULL,
				                        `account_id` int(50) NOT NULL,
																`sync_source` varchar(100) NOT NULL,
				                        `added_date` datetime NOT NULL,
				                        PRIMARY KEY (`id`) ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1"
									);
				/**
				 * create table : "amazon_product_map"
				 */
				$this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "amazon_product_variation_map (
						                        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
						                        `product_id` int(50) NOT NULL,
																		`product_option_value_id` int(50) NOT NULL,
																		`option_value_id` int(50) NOT NULL,
						                        `id_type` varchar(50) NOT NULL,
																		`id_value` varchar(50) NOT NULL,
																		`sku` varchar(100) NOT NULL,
						                        `main_product_type` varchar(20) NOT NULL,
																		`main_product_type_value` varchar(50) NOT NULL,
						                        PRIMARY KEY (`id`) ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1"
											);
		/**
		 * create table : "amazon_attribute_map"
		 */
		$this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "amazon_attribute_map (
				                        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
				                        `oc_attribute_id` int(50) NOT NULL,
																`account_group_id` int(50) NOT NULL,
																`attr_code_map` varchar(250) NOT NULL,
				                        `account_id` int(50) NOT NULL,
				                        PRIMARY KEY (`id`) ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1"
									);

		/**
		 * create table : "amazon_variation_map"
		 */
		$this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "amazon_variation_map (
				                        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
				                        `variation_id` int(50) NOT NULL,
																`variation_value_id` int(50) NOT NULL,
				                        `value_name` varchar(250) NOT NULL,
																`label` varchar(500) NOT NULL,
				                        PRIMARY KEY (`id`) ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1"
									);

		/**
		 * create table : "amazon_order_map"
		 */
		$this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "amazon_order_map (
				                        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
				                        `oc_order_id` int(50) NOT NULL,
				                        `amazon_order_id` varchar(500) NOT NULL,
				                        `amazon_order_status` varchar(250) NOT NULL,
				                        `sync_date` datetime NOT NULL,
				                        `account_id` int(50) NOT NULL,
				                        PRIMARY KEY (`id`) ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1"
									);

				/**
				 * create table : "amazon_customer_map"
				 */
				$this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "amazon_customer_map (
						                        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
						                        `oc_customer_id` int(50) NOT NULL,
																		`customer_group_id` int(50) NOT NULL,
																		`name` varchar(100) NOT NULL,
																		`email` varchar(150) NOT NULL,
						                        `city` varchar(100) NOT NULL,
																		`country` varchar(150) NOT NULL,
																		`account_id` int(10) NOT NULL,
						                        PRIMARY KEY (`id`) ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1"
											);

			$this->__addVariationOption();
			$this->__addAmazonDefaultCategory();
	}

	public function __addVariationOption(){
			$this->removeVariationOption();

			$this->load->model('localisation/language');
			$languages = $this->model_localisation_language->getLanguages();

			$this->db->query("INSERT INTO `" . DB_PREFIX . "option` SET type = 'select', sort_order = '0'");

			$option_id = $this->db->getLastId();

			foreach ($languages as $key => $language) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "option_description SET option_id = '" . (int)$option_id . "', language_id = '" . (int)$language['language_id'] . "', name = 'Amazon Variations'");
			}
	}

	public function removeVariationOption(){
		$getOptionEntry = $this->db->query("SELECT * FROM ".DB_PREFIX."option o LEFT JOIN ".DB_PREFIX."option_description od ON(o.option_id = od.option_id) WHERE od.name = 'Amazon Variations' ")->row;
			if(!empty($getOptionEntry)){
				$this->db->query("DELETE FROM ".DB_PREFIX."option WHERE option_id = '".(int)$getOptionEntry['option_id']."' ");
				$this->db->query("DELETE FROM ".DB_PREFIX."option_description WHERE option_id = '".(int)$getOptionEntry['option_id']."' ");
				$this->db->query("DELETE FROM ".DB_PREFIX."product_option WHERE option_id = '".(int)$getOptionEntry['option_id']."' ");
				$this->db->query("DELETE FROM ".DB_PREFIX."product_option_value WHERE option_id = '".(int)$getOptionEntry['option_id']."' ");
			}
	}


		public function __addAmazonDefaultCategory(){
				$category_data = array(
					'path' 				=> '',
					'parent_id' 	=> 0,
					'filter' 			=> '',
					'keyword' 		=> 'Default-Amazon-Category',
					'image' 			=> '',
					'top' 				=> 1,
					'column' 			=> 1,
					'sort_order' 	=> 0,
					'status' 			=> 1,
					'category_layout' => array(),
					);
				$this->language->load('localisation/language');
				$getAllLanguage = $this->model_localisation_language->getLanguages();
				foreach ($getAllLanguage as $key => $language) {
					$category_data['category_description'][$language['language_id']] = array(
												'name' 					=> 'Default-Amazon-Category',
												'description' 	=> '<p>Default-Amazon-Category<br></p>',
												'meta_title' 		=> 'amazon category',
												'meta_description' => '',
												'meta_keyword' 	=> '',
							);
				}
				$category_data['category_store'] = array(0);
				$this->load->model('setting/store');
				$stores = $this->model_setting_store->getStores();
				foreach ($stores as $key => $store) {
					$category_data['category_store'] = array([0] => $store['store_id'] ? $store['store_id'] : 0);
				}

				$this->load->model('catalog/category');
				$stores = $this->model_catalog_category->addCategory($category_data);
		}

	public function removeTables(){
		$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."amazon_accounts`");
		$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."amazon_product_fields`");
		$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."amazon_product_map`");
		$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."amazon_product_variation_map`");
		$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."amazon_attribute_map`");
		$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."amazon_variation_map`");
		$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."amazon_order_map`");
		$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX ."amazon_customer_map`");
	}

	public function get_OpencartCategories($data = array()){

		$sql = "SELECT * FROM ".DB_PREFIX."category oc_cat LEFT JOIN ".DB_PREFIX."category_description oc_cat_desc ON(oc_cat.category_id = oc_cat_desc.category_id) WHERE oc_cat.status = '1' AND oc_cat_desc.language_id = '".(int)$this->config->get('config_language_id')."' ";

		if(!empty($data['filter_parent_id'])){
			$sql .= " AND oc_cat.parent_id = '".(int)$data['filter_parent_id']."' ";
		}else{
			$sql .= " AND oc_cat.parent_id = '0' ";
		}

		if(!empty($data['filter_category_id'])){
			$sql .= " AND oc_cat.category_id = '".(int)$data['filter_category_id']."' ";
		}

		if(empty($data)){
			$sql .= " ORDER BY oc_cat.category_id DESC ";
		}

		$results = $this->db->query($sql)->rows;

		return $results;
	}
}
