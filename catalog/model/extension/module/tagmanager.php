<?php
/******************************************************
 * @package Google Tag Manager for OC3.0x
 * @version 3.0x
 * @author https://aits.pk
 * @copyright Copyright (C)2017 aits.pk All rights reserved.
 * @email:info@aits.pk. 
 * $date: 01 OCT 2017
*******************************************************/

class ModelExtensionModuleTagmanager extends Model {
	
	public function getTagmanger() {
		
		$tagmanager = array();
		
		$tagmanager = array (
			'code' 	           => $this->config->get('module_tagmanager_code'),
			'status'           => $this->config->get('module_tagmanager_status'),
			'admin'            => $this->config->get('module_tagmanager_admin'),
			'eu_cookie'        => $this->config->get('module_tagmanager_eu_cookie'),
			'cookie_text'      => $this->config->get('module_tagmanager_cookie_text'),
			'cookie_link'      => $this->config->get('module_tagmanager_cookie_link'),
			'cookie_button1'   => $this->config->get('module_tagmanager_cookie_button1'),
			'cookie_button2'   => $this->config->get('module_tagmanager_cookie_button2'),
			'adword'           => $this->config->get('module_tagmanager_adword'),
			'conversion_id'    => $this->config->get('module_tagmanager_conversion_id'),
			'conversion_label' => $this->config->get('module_tagmanager_conversion_label'),
			'remarketing'      => $this->config->get('module_tagmanager_remarketing'),
			'pixel'			   => $this->config->get('module_tagmanager_pixel'),
			'pixelcode'		   => $this->config->get('module_tagmanager_pixelcode'),
			'pmap'			   => $this->config->get('module_tagmanager_product'),
			'ptitle'					 => $this->config->get('tagmanager_ptitle'),
			'currency'         => $this->session->data['currency']
			);
		return $tagmanager;
		
	}
	
	 public function getOrderProduct($order_id,$order_info) {
				$order_product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");
		
				$products = array();
	
				foreach ($order_product_query->rows as $product) {
					$product_id = $product['product_id'];
					$option_data = array();
	
					/*
					$order_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$product['order_product_id'] . "'");
	
					foreach ($order_option_query->rows as $option) {
						if (isset($option['product_option_value_id']) && $option['product_option_value_id'] > 0) { 
								$option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value WHERE product_option_value_id = '" . $option['product_option_value_id'] . "'");
								$option_price = (isset($option_value_query->row['price']) && $option_value_query['price'] ? $option_value_query['price'] : 0.00);
								$option_prefix = (isset($option_value_query->row['price_prefix']) && $option_value_query['price_prefix'] ? $option_value_query['price_prefix'] : '+');		
								
								
								if ($option_prefix != '+') {
										$product['price'] = $product['price'] - $option_price;
								} else {
									$option_data[] = array(
										'name'  => $option['name'] . (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value),
										'price' => number_format((float)$option_price, 2, '.', '')
									);
								}
						} 
					}
					*/
					
					$brand = $this->getProductBrandName($product['product_id']);
					$cat = $this->getProductCatName($product['product_id']);
					
					// conversion to selected currency

     				$price = $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value'],false);
					$total = $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value'],false);

					// NO Conversion stick with default currency

					//$price = $product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0);
					//$total = $product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0);

					$pid = $this->tagmangerPmap($product['model'],$product['sku'],$product['product_id']);
					
					$title = $this->tagmangerPtitle($product['name'], $brand, $product['model'],$product['product_id']);
					
					
					$products[] = array(
						'name'     => $product['name'],
						'title'    => $title,
						'model'    => $product['model'],
						'pid'      => $pid,
						'category' => $category,
						'brand'    => $brand,
						'option'   => $option_data,
						'quantity' => $product['quantity'],
						'price'    => number_format((float)$price, 2, '.', ''),
						'total'    => number_format((float)$total, 2, '.', '')
					);
				}
		
		return $products;
		
	}    
  
	public function getOrderTax($order_id) {
				$tax_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' AND code = 'tax'");
				$order_tax = '0.00';
				if ($tax_query->num_rows) {
					$order_tax = $tax_query->row['value'];
				} 
				return $order_tax;

	}

	public function getOrderCoupon($order_id) {
				$coupon_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' AND code = 'coupon'");
				$order_coupon = '';
				if ($coupon_query->num_rows) {
					$order_coupon = $coupon_query->row['title'];
				} 
				return $order_coupon;

	}
  
	 public function tagmangerPmap($model='',$sku='',$product_id='') {
		$pmap = $this->config->get('module_tagmanager_product');
			$curr = $this->config->get('config_currency');
			
			$supported_currencies = array('GBP', 'USD', 'EUR', 'AUD', 'BRL', 'CZK', 'JPY', 'CHF', 'CAD', 'DKK', 'INR', 'MXN', 'NOK', 'PLN', 'RUB', 'SEK', 'TRY');
				
			if (!in_array($curr, $supported_currencies)) {
					$curr = 'GBP';
			}
			
			if($curr == 'GBP'){
					$currency = 'gb';
			}elseif($curr == 'USD'){
					$currency = 'us';
			}elseif($curr == 'AUD'){
					$currency = 'au';
			}elseif($curr == 'CAD'){
					$currency = 'ca';
			}elseif($curr == 'CHF'){
					$currency = 'ch';
			}elseif($curr == 'MXN'){
					$currency = 'mx';
			}elseif($curr == 'INR'){
					$currency = 'in';
			}
					
	   
		if ($pmap == 'product_id') {
		  $pid = $product_id;      
		} elseif ($pmap == 'model') {
		  $pid = $model;
		} elseif ($pmap == 'sku') {
		  $pid = $sku;
		} elseif ($pmap == 'model_product_id') {
		  $pid = $model . '_' . $product_id;
		} elseif ($pmap == 'product_id_currency') {
		  $pid = $product_id . '_' . $currency;
		} elseif ($pmap == 'product_id_language') {
		  $pid = $product_id . '_' . $this->config->get('config_language');      
		} else {
		  $pid = $product_id;
		}
		return $pid;
	}
	  
	
	public function tagmangerPtitle($name='', $brand='',$model='',$product_id='') {
		$ptitle = $this->config->get('module_tagmanager_ptitle');
			   
		if ($ptitle == 'name') {
		  $ptitle = $name;      
		} elseif ($ptitle == 'brand_model') {
		  $ptitle = $brand . ' ' . $model;
		} else {
		  $ptitle = $name;     
		}
		$ptitle = $this->cleanStr($ptitle);
		return htmlspecialchars($ptitle, ENT_QUOTES);
	}
	
	
	public function getProductCatNameEXT($product_id) {
		$query = $this->db->query("SELECT (SELECT DISTINCT GROUP_CONCAT(cd1.name ORDER BY cp.level SEPARATOR ' > ') FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category c1 ON (cp.category_id = c1.category_id) LEFT JOIN " . DB_PREFIX . "category c2 ON (cp.path_id = c2.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (cp.path_id = cd1.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (cp.category_id = cd2.category_id) WHERE c1.category_id = pc.category_id) AS category FROM " . DB_PREFIX . "category_description cd INNER JOIN " . DB_PREFIX . "product_to_category pc ON pc.category_id = cd.category_id INNER JOIN " . DB_PREFIX . "product p ON pc.product_id = p.product_id  WHERE 1 AND p.product_id = '".$product_id."' GROUP BY p.product_id");
		return (isset($query->row['category']) && $query->row['category']) ? $query->row['category'] : '';
	}

	public function getProductBrandName($product_id) {
		$query = $this->db->query("SELECT m.name from " . DB_PREFIX . "manufacturer m left join " . DB_PREFIX . "product p on m.manufacturer_id = p.manufacturer_id  WHERE 1 AND p.product_id = ".$product_id);
		if (isset($query->row['name'])) {
			$brand = $query->row['name'];
		} else {
			$brand = '';
		}
		$brand = $this->cleanStr($brand);
		return $brand;
	}

	public function escapeJsonString($value) { # list from www.json.org: (\b backspace, \f formfeed)
		$escapers = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c");
		$replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b");
		$result = str_replace($escapers, $replacements, $value);
		return $result;
	}

	public function getProductCatName($product_id) {
		
		$return_data = array();
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "' ORDER BY category_id DESC LIMIT 1 ");
			
			if($query->num_rows == 1){
				$return_data = $this->getparent($query->row['category_id']);
				$return_data = array_reverse($return_data);
			}
		  $cat = '';
		  $i=1;
			foreach ($return_data as $result) {
				if ($i>1) {
					$cat .= ' > ';
				}
				$cat .= $result['name'] ;
				$i++;
			} 
			$cat = $this->cleanStr($cat);
			return $cat;
		
	}

	public function getparent($cid) {
			$data = array();
			$temp  = $this->db->query("SELECT c.category_id, cd1.name AS name, c.parent_id FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (c.category_id = cd1.category_id)  WHERE cd1.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c.category_id = '".(int)$cid."'");
			
			if($temp->num_rows == 1) {
				$data[] = $temp->row;
				
				if($temp->row['parent_id'] != 0) {
					$data = array_merge($data,  $this->getparent($temp->row['parent_id']));
				}
			} else {
				return $data;
			}
			return $data;
	}
	
	public function cleanStr($data) {
		$data = str_replace('"', "", $data);
		$data = str_replace("'", "", $data);
		return $data;
	}
}

?>