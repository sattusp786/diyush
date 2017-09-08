<?php
/**
 * @version [2.3.x.x] [Supported opencart version 2.3.x.x]
 * @category Webkul
 * @package Opencart-Amazon Connector
 * @author [Webkul] <[<http://webkul.com/>]>
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
class ModelAmazonMapOrder extends Model {
		/**
		 * [getOcAmazonOrderMap to get list of mapped Amazon order with opencart order]
		 * @param  array $data [description]
		 * @return [type]              [description]
		 */
		public function getOcAmazonOrderMap($data = array()){
				$sql = "SELECT *, aom.id as map_id FROM ".DB_PREFIX."amazon_order_map aom LEFT JOIN ".DB_PREFIX."order o ON(aom.oc_order_id = o.order_id) LEFT JOIN ".DB_PREFIX."customer c ON(o.customer_id = c.customer_id) WHERE o.language_id = '".(int)$this->config->get('config_language_id')."' AND c.language_id = '".(int)$this->config->get('config_language_id')."' ";

				if(!empty($data['account_id'])){
						$sql .= " AND aom.account_id = '".(int)$data['account_id']."' ";
				}

				if(!empty($data['filter_map_id'])){
						$sql .= " AND aom.id = '".(int)$data['filter_map_id']."' ";
				}

				if(!empty($data['amazon_order_id'])){
						$sql .= " AND aom.amazon_order_id = '".$this->db->escape($data['amazon_order_id'])."' ";
				}

				if(!empty($data['oc_order_id'])){
						$sql .= " AND aom.oc_order_id = '".(int)$data['oc_order_id']."' AND o.order_id = '".(int)$data['oc_order_id']."' ";
				}

					$sort_data = array(
						'aom.amazon_order_id',
						'aom.amazon_order_status',
						'o.oc_order_id',
						'o.total',
						'o.firstname',
						'o.lastname',
						'c.email'
					);

					if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
						$sql .= " ORDER BY " . $data['sort'];
					} else {
						$sql .= " ORDER BY aom.id";
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
				return $this->db->query($sql)->rows;
	  }

		/**
		 * [getTotalOcAmazonOrderMap to get total number of mapped Amazon orders with opencart orders]
		 * @param  array $data [description]
		 * @return [type]              [description]
		 */
		public function getTotalOcAmazonOrderMap($data = array()){
				$sql = "SELECT COUNT(DISTINCT aom.id) as total FROM ".DB_PREFIX."amazon_order_map aom LEFT JOIN ".DB_PREFIX."order o ON(aom.oc_order_id = o.order_id) LEFT JOIN ".DB_PREFIX."customer c ON(o.customer_id = c.customer_id) WHERE o.language_id = '".(int)$this->config->get('config_language_id')."' AND c.language_id = '".(int)$this->config->get('config_language_id')."' ";

				if(!empty($data['account_id'])){
						$sql .= " AND aom.account_id = '".(int)$data['account_id']."' ";
				}

				if(!empty($data['filter_map_id'])){
						$sql .= " AND aom.id = '".(int)$data['filter_map_id']."' ";
				}

				if(!empty($data['amazon_order_id'])){
						$sql .= " AND aom.amazon_order_id = '".$this->db->escape($data['amazon_order_id'])."' ";
				}

				if(!empty($data['oc_order_id'])){
						$sql .= " AND aom.oc_order_id = '".(int)$data['oc_order_id']."' AND o.order_id = '".(int)$data['oc_order_id']."' ";
				}

				$result = $this->db->query($sql)->row;

				return $result['total'];
	  }


		public function deleteMapOrders($map_id, $account_id){
				$result = $order_data = array();
				$this->load->model('sale/order');
				$getProductEntry = $this->getOcAmazonOrderMap(array('filter_map_id' => $map_id, 'account_id' => $account_id));

				if(!empty($getProductEntry) && isset($getProductEntry[0]['map_id']) && $getProductEntry[0]['map_id'] == $map_id){
						$order_data = $getProductEntry[0];
						$this->db->query("DELETE FROM " . DB_PREFIX . "amazon_order_map WHERE oc_order_id = '" . (int)$order_data['oc_order_id'] . "'");
						$this->model_sale_order->deleteOrder($order_data['oc_order_id']);
						$result = array('status' => true, 'message' => sprintf($this->language->get('text_success_delete'), $order_data['amazon_order_id']). $order_data['oc_order_id']);
				}else{
					$result = array('status' => false, 'message' => sprintf($this->language->get('error_order_delete'), $map_id));
				}
				return $result;
		}

		public function addOrderCustomer($data = array(), $account_id)
		{
				$getCustomerData = array();
				$this->load->model('customer/customer');
				$getCustomerData = $this->model_customer_customer->getCustomerByEmail($data['BuyerEmail']);

				if(empty($getCustomerData)){
						if(!empty($this->config->get('config_customer_group_display'))){
								$getCustomerGroup = $this->config->get('config_customer_group_display');
								if(isset($getCustomerGroup[0]) && $getCustomerGroup[0]){
										$data['customer_group_id'] = $getCustomerGroup[0];
								}else{
									$data['customer_group_id'] = 1;
								}
						}else{
							$data['customer_group_id'] = 1;
						}
						if(isset($data['BuyerName']) && $data['BuyerName']){
								$getCustomerName 	 = explode(' ', $data['BuyerName']);
								$data['firstname'] = $getCustomerName[0];
								$data['lastname']  = isset($getCustomerName[1]) ? $getCustomerName[1] : '';
						}else{
								$data['firstname'] = $data['BuyerEmail'];
								$data['lastname']  = $data['BuyerEmail'];
						}
						$data['telephone'] = '1234567890';

						$this->db->query("INSERT INTO ".DB_PREFIX."customer SET customer_group_id = '" . (int)$data['customer_group_id'] . "', language_id = '".(int)$this->config->get('config_language_id')."', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '".$this->db->escape($data['BuyerEmail'])."', telephone = '" . $this->db->escape($data['telephone']) . "', fax = '', custom_field = '" . $this->db->escape(isset($data['custom_field']) ? json_encode($data['custom_field']) : '') . "', newsletter = '0', salt = '" . $this->db->escape($salt = token(9)) . "', password = '".$this->db->escape(sha1($salt . sha1($salt . sha1($data['lastname'].'_'.$data['firstname']))))."', status = '1', approved = '1', safe = '0', date_added = NOW()");

						$customer_id = $this->db->getLastId();

						$city_name = $country_name = '';
						if (isset($data['ShippingAddress'])) {
								if(isset($data['ShippingAddress']['CountryCode'])){
									$country = $this->db->query("SELECT * FROM ".DB_PREFIX."country WHERE iso_code_2 = '".$this->db->escape($data['ShippingAddress']['CountryCode'])."' ")->row;

										if(isset($country['country_id'])){
											$country_id 	= $country['country_id'];
											$country_name = $country['name'];
										}else{
											$country_id = 0;
										}
									$city_name = $data['ShippingAddress']['City'];
								}else{
										$country_id = 0;
								}
								$this->db->query("INSERT INTO " . DB_PREFIX . "address SET customer_id = '" . (int)$customer_id . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', company = '', address_1 = '" . $this->db->escape($data['ShippingAddress']['AddressLine1']) . "', address_2 = '" . $this->db->escape(isset($data['ShippingAddress']['AddressLine2']) ? $data['ShippingAddress']['AddressLine2'] : '') . "', city = '" . $this->db->escape($data['ShippingAddress']['City']) . "', postcode = '" . $this->db->escape($data['ShippingAddress']['PostalCode']) . "', country_id = '" . (int)$country_id . "', zone_id = '0' ");

								$address_id = $this->db->getLastId();

								$this->db->query("UPDATE " . DB_PREFIX . "customer SET address_id = '" . (int)$address_id . "' WHERE customer_id = '" . (int)$customer_id . "'");

								$getCustomerData = $this->model_customer_customer->getCustomerByEmail($data['BuyerEmail']);
						}
						if($customer_id){
								$this->db->query("INSERT INTO ".DB_PREFIX."amazon_customer_map SET `oc_customer_id` = '".(int)$customer_id."', `customer_group_id` = '".(int)$data['customer_group_id']."', `name` = '".$this->db->escape($data['BuyerName'])."', `email` = '".$this->db->escape($data['BuyerEmail'])."', `city` = '".$this->db->escape($city_name)."', `country` = '".$this->db->escape($country_name)."', `account_id` = '".(int)$account_id."' ");
						}
				}
				return $getCustomerData;
		}


		public function createOrders($order, $orderProducts, $customerDetails, $orderReport)
		{
				  $sync_result = array();
				if(!empty($order)){
						$this->load->model('catalog/product');
						$this->load->model('amazon_map/product');
						$customerDetails['amazon_order_id'] = $order['AmazonOrderId'];
						$getOrderData = $this->createOrderProduct($orderProducts, $customerDetails, $orderReport);

						if(!empty($getOrderData) && isset($getOrderData['status']) && $getOrderData['status']){
								$sync_result = $this->__saveOrderData($order, $getOrderData, $customerDetails['account_id']);
						}else if(isset($getOrderData['status']) && !$getOrderData['status']){
								$sync_result = $getOrderData;
						}else{
								$sync_result = array(
																	'status'  => false,
																	'message' => 'Amazon order id : <b> '.$order['AmazonOrderId']." </b> failed to mapped with opencart order!",
																);
						}
				}
				return $sync_result;
		}

		public function createOrderProduct($Products, $customerDetails, $orderReport)
		{
				$order_data = array();
				$getAccountEntry = $this->Amazonconnector->getAccountDetails(array('account_id' => $customerDetails['account_id']));
				if(!empty($Products) && $getAccountEntry){
						$product_data = array();
						foreach ($Products as $key => $product) {
								$orderedProductId = false;
								$option_data = array();
								$product = (array)$product;

								if(isset($product['ASIN']) && $product['ASIN']){
									// check product is already imported to opencart store or note
									$getproductEntry = $this->model_amazon_map_product->getProductMappedEntry(array('filter_amazon_product_id' => $product['ASIN'], 'account_id' => $customerDetails['account_id']));

									if(empty($getproductEntry)){

											$getVaristionASINEntry = $this->Amazonconnector->filterVariationASINEntry(array('account_id' => $customerDetails['account_id'], 'id_value' => $product['ASIN']));

											if(empty($getVaristionASINEntry) && isset($orderReport['prd_reportId']) && isset($orderReport['qty_reportId'])){
													$result = $this->model_amazon_map_product->import_AmazonProduct(array('account_id' => $customerDetails['account_id'], 'product_asin' => $product['ASIN']));

													if(isset($result['status']) && $result['status'] && !empty($result)){
															if(isset($result['data']['success']) && !empty($result['data']['success'])){
																	foreach ($result['data']['success'] as $product_key => $p_value) {
																		// check product is already imported to opencart store or note

																		// $getproductEntryAgain = $this->model_amazon_map_product->getProductMappedEntry(array('filter_oc_product_id' => $product_key, 'filter_amazon_product_id' => 'B00Z7SBJD2', 'account_id' => 1));

																		$getproductEntryAgain = $this->model_amazon_map_product->getProductMappedEntry(array('filter_oc_product_id' => $product_key, 'filter_amazon_product_id' => $product['ASIN'], 'account_id' => $customerDetails['account_id']));

																			if(empty($getproductEntryAgain)){

																					$getVaristionASINEntryAgain = $this->Amazonconnector->filterVariationASINEntry(array('account_id' => $customerDetails['account_id'], 'id_value' => $product['ASIN']));
																					if(empty($getVaristionASINEntryAgain) && $orderReport['prd_reportId'] && $orderReport['qty_reportId']){
																						$product_id = false;
																						foreach ($getVaristionASINEntryAgain as $key => $opt_product) {
																								if(isset($opt_product['product_option_value_id']) && $opt_product['product_option_value_id'] && isset($opt_product['main_product_type_value']) && $opt_product['main_product_type_value']){
																											$option_data[] = array(
																																			'product_option_value_id' => $opt_product['product_option_value_id'],
																																			'product_option_id' 			=> $opt_product['product_option_id'],
																																			'name' 										=> 'Amazon Variations',
																																			'value' 									=> $opt_product['value_name'],
																																			'type' 										=> 'select',
																																		);
																											$product_id = $opt_product['product_id'];
																								}
																						}
                                            $getOcProductDetails = $this->model_catalog_product->getProduct($product_id);
                                            if(!empty($getOcProductDetails)){
                                                $order_quantity = $product['QuantityOrdered'];

																									$unit_price 		= $product['ItemPrice']['Amount'] * ($this->currency->getValue($this->config->get('config_currency')) / $getAccountEntry['wk_amazon_connector_currency_rate']);

																									$shipping_price = $product['ShippingPrice']['Amount'] * ($this->currency->getValue($this->config->get('config_currency')) / $getAccountEntry['wk_amazon_connector_currency_rate']);

																									$tax 						= $product['ItemTax']['Amount'] * ($this->currency->getValue($this->config->get('config_currency')) / $getAccountEntry['wk_amazon_connector_currency_rate']);

																									$product_data[] = array(
																																			'product_id'=> $getOcProductDetails['product_id'],
																																			'name'			=> $getOcProductDetails['name'],
																																			'model'			=> $getOcProductDetails['model'],
																																			'quantity'	=> $order_quantity,
																																			'price'			=> $unit_price,
																																			'total'			=> $unit_price * $order_quantity,
																																			'tax'				=> $tax,
																																			'shipping_price' => $shipping_price,
																																			'option'		=> $option_data,
																																		);
																							}else{
																									return array('status' => false, 'message' => 'Warning: Amazon Order\'s product ('.$product['ASIN'].') import error for Amazon Order-Id : '.$customerDetails['amazon_order_id'].'!');
																							}
																					}
																			}else{
																					if(isset($getproductEntryAgain[0]['oc_product_id']) && $getproductEntryAgain[0]['oc_product_id']){
																							$getOcProductDetailsAgain = $this->model_catalog_product->getProduct($getproductEntryAgain[0]['oc_product_id']);
																							if($getOcProductDetailsAgain){

																									$order_quantity = $product['QuantityOrdered'];

																									$unit_price 		= $product['ItemPrice']['Amount'] * ($this->currency->getValue($this->config->get('config_currency')) / $getAccountEntry['wk_amazon_connector_currency_rate']);

																									$shipping_price = $product['ShippingPrice']['Amount'] * ($this->currency->getValue($this->config->get('config_currency')) / $getAccountEntry['wk_amazon_connector_currency_rate']);

																									$tax 						= $product['ItemTax']['Amount'] * ($this->currency->getValue($this->config->get('config_currency')) / $getAccountEntry['wk_amazon_connector_currency_rate']);

																									$product_data[] = array(
																																				'product_id'=> $getOcProductDetailsAgain['product_id'],
																																				'name'			=> $getOcProductDetailsAgain['name'],
																																				'model'			=> $getOcProductDetailsAgain['model'],
																																				'quantity'	=> $order_quantity,
																																				'price'			=> $unit_price,
																																				'total'			=> $unit_price * $order_quantity,
																																				'tax'				=> $tax,
																																				'shipping_price' => $shipping_price,
																																				'option'		=> $option_data,
																																			);
																							}
																					}else{
																							return array('status' => false, 'message' => 'Warning: Amazon Order\'s product ('.$product['ASIN'].') import error for Amazon Order-Id : '.$customerDetails['amazon_order_id'].'!');
																					}
																			}
																	}
															}
															if(isset($result['data']['error']) && !empty($result['data']['error'])){
																	return array('status' => false, 'message' => 'Warning: Amazon Order\'s product ('.$product['ASIN'].') import error for Amazon Order-Id : '.$customerDetails['amazon_order_id'].'!');
															}
													}
											}else{
													$product_id = false;
													foreach ($getVaristionASINEntry as $key => $opt_product) {
															if(isset($opt_product['product_option_value_id']) && $opt_product['product_option_value_id'] && isset($opt_product['main_product_type_value']) && $opt_product['main_product_type_value']){
																		$option_data[] = array(
																										'product_option_value_id' => $opt_product['product_option_value_id'],
																										'product_option_id' 			=> $opt_product['product_option_id'],
																										'name' 										=> 'Amazon Variations',
																										'value' 									=> $opt_product['value_name'],
																										'type' 										=> 'select',
																									);
																		$product_id = $opt_product['product_id'];
															}
													}

														$getOcProductDetails = $this->model_catalog_product->getProduct($product_id);
														if(!empty($getOcProductDetails)){
																$order_quantity = $product['QuantityOrdered'];

																$unit_price 		= $product['ItemPrice']['Amount'] * ($this->currency->getValue($this->config->get('config_currency')) / $getAccountEntry['wk_amazon_connector_currency_rate']);

																$shipping_price = $product['ShippingPrice']['Amount'] * ($this->currency->getValue($this->config->get('config_currency')) / $getAccountEntry['wk_amazon_connector_currency_rate']);

																$tax 						= $product['ItemTax']['Amount'] * ($this->currency->getValue($this->config->get('config_currency')) / $getAccountEntry['wk_amazon_connector_currency_rate']);

																$product_data[] = array(
																										'product_id'=> $getOcProductDetails['product_id'],
																										'name'			=> $getOcProductDetails['name'],
																										'model'			=> $getOcProductDetails['model'],
																										'quantity'	=> $order_quantity,
																										'price'			=> $unit_price,
																										'total'			=> $unit_price * $order_quantity,
																										'tax'				=> $tax,
																										'shipping_price' => $shipping_price,
																										'option'		=> $option_data,
																									);
														}else{
																return array('status' => false, 'message' => 'Warning: Amazon Order\'s product ('.$product['ASIN'].') import error for Amazon Order-Id : '.$customerDetails['amazon_order_id'].'!');
														}
											  }
										}else{
												if(isset($getproductEntry[0]['oc_product_id']) && $getproductEntry[0]['oc_product_id']){
													$getOcProductDetails = $this->model_catalog_product->getProduct($getproductEntry[0]['oc_product_id']);

													if($getOcProductDetails){

															$order_quantity = $product['QuantityOrdered'];

															$unit_price 		= $product['ItemPrice']['Amount'] * ($this->currency->getValue($this->config->get('config_currency')) / $getAccountEntry['wk_amazon_connector_currency_rate']);

															$shipping_price = $product['ShippingPrice']['Amount'] * ($this->currency->getValue($this->config->get('config_currency')) / $getAccountEntry['wk_amazon_connector_currency_rate']);

															$tax 						= $product['ItemTax']['Amount'] * ($this->currency->getValue($this->config->get('config_currency')) / $getAccountEntry['wk_amazon_connector_currency_rate']);

															$product_data[] = array(
																										'product_id'=> $getOcProductDetails['product_id'],
																										'name'			=> $getOcProductDetails['name'],
																										'model'			=> $getOcProductDetails['model'],
																										'quantity'	=> $order_quantity,
																										'price'			=> $unit_price,
																										'total'			=> $unit_price * $order_quantity,
																										'tax'				=> $tax,
																										'shipping_price' => $shipping_price,
																										'option'		=> $option_data,
																									);
													}
											}else{
													return array('status' => false, 'message' => 'Warning: Amazon Order\'s product ('.$product['ASIN'].') import error for Amazon Order-Id : '.$customerDetails['amazon_order_id'].'!');
											}
									}// else of product entry check in opencart
								}else{
										return array('status' => false, 'message' => 'Warning: Amazon Order\'s product ASIN number not found for Amazon Order-Id : '.$customerDetails['amazon_order_id'].'!');
								}// product ASIN check
						} // product foreach loop end

						$getCustomerAddress = $this->model_customer_customer->getAddress($customerDetails['address_id']);
						$getCurrencyId = array();
						if($this->config->get('config_currency')){
							$getCurrencyId = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "currency WHERE code = '" . $this->db->escape($this->config->get('config_currency')) . "'")->row;
						}
						if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
							$forwarded_ip = $this->request->server['HTTP_X_FORWARDED_FOR'];
						} elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
							$forwarded_ip = $this->request->server['HTTP_CLIENT_IP'];
						} else {
							$forwarded_ip = '';
						}

						if (isset($this->request->server['HTTP_USER_AGENT'])) {
							$user_agent = $this->request->server['HTTP_USER_AGENT'];
						} else {
							$user_agent = '';
						}
						$order_data = [
									'status'					=> 1,
									'invoice_no'			=> 0,
									'invoice_prefix'	=> $this->config->get('config_invoice_prefix'),
									'store_id'				=> $this->config->get('wk_amazon_connector_default_store'),
									'store_name'			=> $this->config->get('config_name'),
									'store_url'				=> $this->config->get('config_store_id') ? $this->config->get('config_url') : ($this->request->server['HTTPS'] ? HTTPS_SERVER : HTTP_SERVER),
									'customer_id'			=> $customerDetails['customer_id'],
									'customer_group_id'=> $customerDetails['customer_group_id'],
									'firstname' 			=> $customerDetails['firstname'],
									'lastname' 				=> $customerDetails['lastname'],
									'email' 					=> $customerDetails['email'],
									'telephone' 			=> $customerDetails['telephone'],
									'fax' 						=> '',
									'account_id' 			=> $customerDetails['account_id'],
									'language_id' 		=> $this->config->get('config_language_id'),
									'currency_id' 		=> $getCurrencyId['currency_id'],
									'currency_code' 	=> $getCurrencyId['code'],
									'currency_value'	=> $getCurrencyId['value'],
									'order_status_id' => $this->config->get('wk_amazon_connector_order_status'),
													'shipping_firstname' 	=> $customerDetails['firstname'],
													'shipping_lastname' 	=> $customerDetails['lastname'],
													'shipping_address_1' 	=> $getCustomerAddress['address_1'],
													'shipping_address_2' 	=> $getCustomerAddress['address_2'],
													'shipping_city' 			=> $getCustomerAddress['city'],
													'shipping_postcode'		=> $getCustomerAddress['postcode'],
													'shipping_zone'				=> $getCustomerAddress['zone'],
													'shipping_zone_id'		=> $getCustomerAddress['zone_id'],
													'shipping_country'		=> $getCustomerAddress['country'],
													'shipping_country_id'	=> $getCustomerAddress['country_id'],
													'shipping_address_format'=> $getCustomerAddress['address_format'],
									'shipping_method'	=> '',
									'shipping_code' 	=> '',
									'payment_method' 	=> '',
									'payment_code' 	 	=> '',
									'products' 				=> $product_data,
									'affiliate_id' 		=> 0,
									'commission' 			=> 0,
									'marketing_id' 		=> 0,
									'tracking' 				=> '',
									'user_agent'  		=> $user_agent,
									'forwarded_ip'  	=> $forwarded_ip,
									'ip' 							=> $this->request->server['REMOTE_ADDR'],
						];
						return $order_data;
				}else{
						return array('status' => false, 'message' => 'Warning: There is no product found for Amazon Order-Id : '.$customerDetails['amazon_order_id'].'!');
				}// product array empty check
		}// function end

		public function __saveOrderData($amazonOrderData, $orderArray, $account_id)
		{
				  $sync_result = array();
					if ($amazonOrderData['PaymentMethod'] == 'Other') {
							//for all countries
							// $payment_method = $payment_method_arr['other'];
							$payment_method = 'Other';
					} elseif ($amazonOrderData['PaymentMethod'] == 'COD') {
							//for japan only
							// $payment_method = $payment_method_arr['cod'];
							$payment_method = 'cod';
					} elseif ($amazonOrderData['PaymentMethod'] == 'CVS') {
							//for japan only
							// $payment_method = $payment_method_arr['cvs'];
							$payment_method = 'cvs';
					}

					$orderArray['amazon_order_id']= $amazonOrderData['AmazonOrderId'];
					$orderArray['amazon_order_status']= $amazonOrderData['OrderStatus'];

					$orderArray['payment_method'] = $payment_method;
					$orderArray['payment_code'] 	= $payment_method;
					$orderArray['shipping_method']= 'Flat';
					$orderArray['shipping_code'] 	= 'flat.flat';

				$getOrderId = $this->addOrder($orderArray);
		    $this->addOrderHistory($getOrderId, $orderArray['order_status_id']);

		    if($getOrderId){
		    		$this->db->query("INSERT INTO ".DB_PREFIX."amazon_order_map SET `oc_order_id` = '".(int)$getOrderId."', `amazon_order_id` = '".$this->db->escape($orderArray['amazon_order_id'])."', `amazon_order_status` = '".$this->db->escape($orderArray['amazon_order_status'])."', `sync_date` = NOW(), `account_id` = '".(int)$orderArray['account_id']."' ");

		    		$map_id = $this->db->getLastId();

			    	if($map_id){
			    		$sync_result = array(
																	'status'  => true,
																	'message' => 'Success: Amazon order id : <b> '.$orderArray['amazon_order_id']." </b> has been synchronized with opencart's order id : <b> '" .$getOrderId. "' </b>.",
																);
			    	}else{
							$sync_result = array(
																	'status'  => false,
																	'message' => 'Warning: Amazon order id : <b> '.$orderArray['amazon_order_id'].' </b> failed to mapped with opencart order!',
																);
						}
		    }else{
						 $sync_result = array(
															'status'  => false,
															'message' => 'Warning: Amazon order id : <b> '.$orderArray['amazon_order_id'].' </b> failed to mapped with opencart order!',
														);
				}
		    return $sync_result;
		}


		public function addOrder($data)
		{
				$order_total = $order_sub_total = $order_total_shipping = 0;
				foreach ($data['products'] as $key => $product) {
					$order_sub_total 				+= $product['total'];
					$order_total_shipping 	+= $product['shipping_price'];
				}
				$order_total = $order_sub_total + $order_total_shipping;

				$this->db->query("INSERT INTO `" . DB_PREFIX . "order` SET invoice_prefix = '" . $this->db->escape($data['invoice_prefix']) . "', store_id = '" . (int)$data['store_id'] . "', store_name = '" . $this->db->escape($data['store_name']) . "', store_url = '" . $this->db->escape($data['store_url']) . "', customer_id = '" . (int)$data['customer_id'] . "', customer_group_id = '" . (int)$data['customer_group_id'] . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', fax = '" . $this->db->escape($data['fax']) . "', custom_field = '" . $this->db->escape(isset($data['custom_field']) ? json_encode($data['custom_field']) : '') . "', payment_firstname = '" . $this->db->escape($data['shipping_firstname']) . "', payment_lastname = '" . $this->db->escape($data['shipping_lastname']) . "', payment_company = '', payment_address_1 = '" . $this->db->escape($data['shipping_address_1']) . "', payment_address_2 = '" . $this->db->escape($data['shipping_address_2']) . "', payment_city = '" . $this->db->escape($data['shipping_city']) . "', payment_postcode = '" . $this->db->escape($data['shipping_postcode']) . "', payment_country = '" . $this->db->escape($data['shipping_country']) . "', payment_country_id = '" . (int)$data['shipping_country_id'] . "', payment_zone = '', payment_zone_id = '0', payment_custom_field = '" . $this->db->escape(isset($data['payment_custom_field']) ? json_encode($data['payment_custom_field']) : '') . "', payment_method = '" . $this->db->escape($data['payment_method']) . "', payment_code = '" . $this->db->escape($data['payment_code']) . "', shipping_firstname = '" . $this->db->escape($data['shipping_firstname']) . "', shipping_lastname = '" . $this->db->escape($data['shipping_lastname']) . "', shipping_company = '', shipping_address_1 = '" . $this->db->escape($data['shipping_address_1']) . "', shipping_address_2 = '" . $this->db->escape($data['shipping_address_2']) . "', shipping_city = '" . $this->db->escape($data['shipping_city']) . "', shipping_postcode = '" . $this->db->escape($data['shipping_postcode']) . "', shipping_country = '" . $this->db->escape($data['shipping_country']) . "', shipping_country_id = '" . (int)$data['shipping_country_id'] . "', shipping_zone = '', shipping_zone_id = '0', shipping_address_format = '" . $this->db->escape($data['shipping_address_format']) . "', shipping_custom_field = '" . $this->db->escape(isset($data['shipping_custom_field']) ? json_encode($data['shipping_custom_field']) : '') . "', shipping_method = '" . $this->db->escape($data['shipping_method']) . "', shipping_code = '" . $this->db->escape($data['shipping_code']) . "', total = '" . (float)$order_total . "', affiliate_id = '" . (int)$data['affiliate_id'] . "', commission = '" . (float)$data['commission'] . "', marketing_id = '" . (int)$data['marketing_id'] . "', tracking = '" . $this->db->escape($data['tracking']) . "', language_id = '" . (int)$data['language_id'] . "', currency_id = '" . (int)$data['currency_id'] . "', currency_code = '" . $this->db->escape($data['currency_code']) . "', currency_value = '" . (float)$data['currency_value'] . "', ip = '" . $this->db->escape($data['ip']) . "', forwarded_ip = '" .  $this->db->escape($data['forwarded_ip']) . "', user_agent = '" . $this->db->escape($data['user_agent']) . "', date_added = NOW(), date_modified = NOW()");

				$order_id = $this->db->getLastId();

				// Products
				if (isset($data['products'])) {
					foreach ($data['products'] as $product) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "order_product SET order_id = '" . (int)$order_id . "', product_id = '" . (int)$product['product_id'] . "', name = '" . $this->db->escape($product['name']) . "', model = '" . $this->db->escape($product['model']) . "', quantity = '" . (int)$product['quantity'] . "', price = '" . (float)$product['price'] . "', total = '" . (float)$product['total'] . "', tax = '" . (float)$product['tax'] . "', reward = '0'");

						$order_product_id = $this->db->getLastId();

						foreach ($product['option'] as $option) {
							$this->db->query("INSERT INTO " . DB_PREFIX . "order_option SET order_id = '" . (int)$order_id . "', order_product_id = '" . (int)$order_product_id . "', product_option_id = '" . (int)$option['product_option_id'] . "', product_option_value_id = '" . (int)$option['product_option_value_id'] . "', name = '" . $this->db->escape($option['name']) . "', `value` = '" . $this->db->escape($option['value']) . "', `type` = '" . $this->db->escape($option['type']) . "'");
						}
					}
				}

				$totals 	=	[array(
									'code'       => 'sub_total',
									'title'      => 'Sub-Total',
									'value'      => (float)$order_sub_total,
									'sort_order' => $this->config->get('sub_total_sort_order')
								),
								array(
									'code'       => 'shipping',
									'title'      => $data['shipping_method'],
									'value'      => (float)$order_total_shipping,
									'sort_order' => $this->config->get('shipping_sort_order')
								),
								array(
									'code'       => 'total',
									'title'      => 'Total',
									'value'      => (float)$order_total,
									'sort_order' => $this->config->get('total_sort_order')
								)];

				// Totals
				if (isset($totals)) {
					foreach ($totals as $total) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "order_total SET order_id = '" . (int)$order_id . "', code = '" . $this->db->escape($total['code']) . "', title = '" . $this->db->escape($total['title']) . "', `value` = '" . (float)$total['value'] . "', sort_order = '" . (int)$total['sort_order'] . "'");
					}
				}

				return $order_id;
		}

		public function addOrderHistory($order_id, $order_status_id, $comment = '', $notify = false){
			// Stock subtraction
			$order_product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

			foreach ($order_product_query->rows as $order_product) {
				$this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = (quantity - " . (int)$order_product['quantity'] . ") WHERE product_id = '" . (int)$order_product['product_id'] . "' AND subtract = '1'");

				$order_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product['order_product_id'] . "'");

				foreach ($order_option_query->rows as $option) {
					$this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET quantity = (quantity - " . (int)$order_product['quantity'] . ") WHERE product_option_value_id = '" . (int)$option['product_option_value_id'] . "' AND subtract = '1'");
				}
			}

			// Update the DB with the new statuses
			$this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$order_status_id . "', date_modified = NOW() WHERE order_id = '" . (int)$order_id . "'");

			$this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$order_status_id . "', notify = '" . (int)$notify . "', comment = '" . $this->db->escape($comment) . "', date_added = NOW()");
		}


		/************************************** Customer Section ******************************/

		public function getAmazonCustomerList($data = array()){
				$sql = "SELECT *, CONCAT(c.firstname, ' ', c.lastname) AS customer_name FROM ".DB_PREFIX."amazon_customer_map acm LEFT JOIN ".DB_PREFIX."customer c ON((acm.oc_customer_id = c.customer_id) AND (acm.customer_group_id = c.customer_group_id))  WHERE c.status = '1' AND c.approved = '1' ";

				$implode = array();

				if (!empty($data['filter_name'])) {
					$implode[] = "CONCAT(c.firstname, ' ', c.lastname) LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
				}

				if (!empty($data['filter_email'])) {
					$implode[] = "c.email LIKE '" . $this->db->escape($data['filter_email']) . "%'";
				}

				if (!empty($data['filter_customer_group_id'])) {
					$implode[] = "c.customer_group_id = '" . (int)$data['filter_customer_group_id'] . "'";
				}

				if (!empty($data['filter_account_id'])) {
					$implode[] = "acm.account_id = '" . (int)$data['filter_account_id'] . "'";
				}

				if ($implode) {
					$sql .= " AND " . implode(" AND ", $implode);
				}

				$sort_data = array(
					'customer_name',
					'c.email',
					'acm.city',
					'acm.country',
				);

				if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
					$sql .= " ORDER BY " . $data['sort'];
				} else {
					$sql .= " ORDER BY acm.id";
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
				return $this->db->query($sql)->rows;
	  }

		/**
		 * [getTotalOcAmazonOrderMap to get total number of mapped Amazon orders with opencart orders]
		 * @param  array $data [description]
		 * @return [type]              [description]
		 */
		public function getTotalAmazonCustomerList($data = array()){
				$sql = "SELECT DISTINCT acm.* FROM ".DB_PREFIX."amazon_customer_map acm LEFT JOIN ".DB_PREFIX."customer c ON((acm.oc_customer_id = c.customer_id) AND (acm.customer_group_id = c.customer_group_id))  WHERE c.status = '1' AND c.approved = '1' ";

				$implode = array();

				if (!empty($data['filter_name'])) {
					$implode[] = "CONCAT(c.firstname, ' ', c.lastname) LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
				}

				if (!empty($data['filter_email'])) {
					$implode[] = "c.email LIKE '" . $this->db->escape($data['filter_email']) . "%'";
				}

				if (!empty($data['filter_customer_group_id'])) {
					$implode[] = "c.customer_group_id = '" . (int)$data['filter_customer_group_id'] . "'";
				}

				if (!empty($data['filter_account_id'])) {
					$implode[] = "acm.account_id = '" . (int)$data['filter_account_id'] . "'";
				}

				if ($implode) {
					$sql .= " AND " . implode(" AND ", $implode);
				}

				$result = $this->db->query($sql)->rows;

				return count($result);
	  }
}
