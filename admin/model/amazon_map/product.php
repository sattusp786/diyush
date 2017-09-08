<?php
/**
 * @version [2.3.x.x] [Supported opencart version 2.3.x.x]
 * @category Webkul
 * @package Opencart-Amazon Connector
 * @author [Webkul] <[<http://webkul.com/>]>
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
class ModelAmazonMapProduct extends Model {

	private $product_manage 	= array();
	private $report_product_all = array();

	/**
	 * [updateAccountReportEntry to update the product report listing id and inverntory id]
	 * @param  boolean $account_id [description]
	 * @return [type]              [description]
	 */
	public function updateAccountReportEntry($account_id = false){
		$result = array();
		$data = array();
		try{
			$getProductReportId =  $this->Amazonconnector->requestReport('_GET_MERCHANT_LISTINGS_DATA_', $account_id);
			$getProdQtyReportId =  $this->Amazonconnector->requestReport('_GET_AFN_INVENTORY_DATA_', $account_id);

			if($getProductReportId && $getProdQtyReportId){
				do {
                sleep(5);
                $productReportRequestList = $this->Amazonconnector->getReportRequestList($getProductReportId, $account_id);
                $productQtyReportRequestList = $this->Amazonconnector->getReportRequestList($getProdQtyReportId, $account_id);

                $status = $this->getReportStatus($productReportRequestList, $productQtyReportRequestList);
            } while (!$status);

                if((isset($productReportRequestList['GeneratedReportId']) && $productReportRequestList['GeneratedReportId']) && (isset($productQtyReportRequestList['GeneratedReportId']) && $productQtyReportRequestList['GeneratedReportId'])){
                	$data['list_report_id'] 			= $productReportRequestList['GeneratedReportId'];
                	$data['inventory_report_id'] 	= $productQtyReportRequestList['GeneratedReportId'];
                }else if(isset($productReportRequestList['GeneratedReportId'])){
                	$data['list_report_id'] 			= $productReportRequestList['GeneratedReportId'];
                	$data['inventory_report_id'] 	= '0';
                }else{
									$data['list_report_id'] 			= '0';
									$data['inventory_report_id'] 	= '0';
								}

                if (isset($data['list_report_id']) && isset($data['inventory_report_id'])) {
                	$this->db->query("UPDATE ".DB_PREFIX."amazon_accounts SET `wk_amazon_connector_listing_report_id` = '".$this->db->escape($data['list_report_id'])."', `wk_amazon_connector_inventory_report_id` = '".$this->db->escape($data['inventory_report_id'])."' WHERE id = '".(int)$account_id."' ");

                	$result = array('status' => true, 'message' => sprintf($this->language->get('success_report_added'), $data['list_report_id']), 'report_id' => $data['list_report_id']);
                }else{
                	$result = array('status' => false, 'message' => $this->language->get('error_report_list_id'));
                }
			}else{
				$result = array('status' => false, 'message' => $this->language->get('error_report_id'));
			}
		} catch (\Exception $e) {
            $result = array('status' => false,'message' => $e->getMessage());
        }
		return $result;
	}

	/**
	 * [getReportStatus to check listing/inventory id]
	 * @param  [type] $productRequestList [description]
	 * @param  [type] $QtyRequestList     [description]
	 * @return [type]                     [description]
	 */
	private function getReportStatus($productRequestList, $QtyRequestList) {
      $status = isset($productRequestList['success']) && $productRequestList['success'] && $productRequestList['GeneratedReportId'] && isset($QtyRequestList['success']) && $QtyRequestList['success'] && $QtyRequestList['GeneratedReportId'];
      if (!$status) {
          $status =  isset($productRequestList['success']) && $productRequestList['success'] && $productRequestList['GeneratedReportId']
                  || isset($QtyRequestList['success']) && $QtyRequestList['success'] && $QtyRequestList['GeneratedReportId'];
      }
      return $status;
    }

	/**
	* use to import amazon store product to opencart store (with / without combinations/variations/options)
	*/
  public function import_AmazonProduct($data = array()){
		$this->product_manage = array();
		$this->load->language('amazon_map/product');
		$sync_result = array();

  		try {
  			$getAmazonClient 	= $this->Amazonconnector->getAccountDetails($data);

					if(isset($getAmazonClient['wk_amazon_connector_listing_report_id']) && $getAmazonClient['wk_amazon_connector_listing_report_id']){
						$finalReportData =  $newQtyReport = [];

						$productReportData = $this->Amazonconnector->getReportFinal($getAmazonClient['wk_amazon_connector_listing_report_id'], $data['account_id']);

						if(!empty($productReportData) && isset($productReportData) && $productReportData){

							$qtyReportData = $this->Amazonconnector->getReportFinal($getAmazonClient['wk_amazon_connector_inventory_report_id'], $data['account_id']);

							foreach ($productReportData as $key => $product_data) {
								$productInventoryExist = false;
								if(isset($qtyReportData) && $qtyReportData){
										foreach ($qtyReportData as $key => $qty_data) {
												if($product_data['asin1'] == $qty_data['asin']){

													foreach ($qty_data as $qtyKey => $qtyValue) {
				                      $newQtyReport[trim($qtyKey)] = $qtyValue;
				                  }
				                  $productInventoryExist = true;
				                  $product_data['qty_avail'] = $newQtyReport['Quantity Available'];

				                  break;
												}
												if (!$productInventoryExist) {
				                    $product_data['qty_avail'] = $product_data['quantity'];
				                }
										}
								}else{
										$product_data['qty_avail'] = $product_data['quantity'];
								}
								$finalReportData[] = $product_data;
							}
						}else{
							$sync_result = array(
				                'data' 		=> array(),
				                'message' 	=> $this->language->get('error_no_product_found'),
				                'status'	=> false,
				            );
						}

						if(!empty($finalReportData)){
							if(isset($data['product_asin'])){
								$product_asin = $data['product_asin'];
							}else{
								$product_asin = false;
							}
							$results = $this->getFullAmazonProductDetails($finalReportData, $data['account_id'], $product_asin);
							$sync_result = array(
				                'data' 		=> $results,
				                'message' => $this->language->get('success_get_amazon_product'),
				                'status'	=> true,
				            );
						}
					}else {
	            $sync_result = array(
	            		'data' 		=> array(),
	                'message' => $this->language->get('error_generate_report_first'),
	                'status'	=> false,
	            );
	        }
	    } catch (\Exception $e) {
          $sync_result = array('data' => array(),'message' => $e->getMessage(), 'status' => false);
          $this->log->write('Amazon Product imported Result Error :: '. $e->getMessage());
      }
      return $sync_result;
	}

	/**
	 * getFullAmazonProductDetails function to get the full details of imported amazon product using ASIN and other UIN type (work with both either import all the products or one by one from amazon store)
	 *
	 * @return array
	 * @author webkul
	 **/
	public function getFullAmazonProductDetails($productFinalReport, $account_id = false, $product_asin = false)
	{
		$this->load->model('setting/store');
		$this->load->model('catalog/attribute');
		$this->load->model('catalog/option');
		$this->load->model('localisation/language');
		$response 		= array();

		$getAccountDetails = $this->Amazonconnector->getAccountDetails(array('account_id' => $account_id));

		if(!$product_asin){
				foreach ($productFinalReport as $key => $product_field) {
					$this->report_product_all[$product_field['asin1']] = $product_field;
				}
				foreach ($productFinalReport as $key => $product_field) {
					$result = $this->getAmazonProductByAsin(array('product_asin' => $product_field['asin1'], 'product_data' => $product_field, 'account_details' => $getAccountDetails));

					if(isset($result['success']) && $result['success']){
						$response['success'][$result['success']['data']] 	= $result['success'];
					}
					if(isset($result['error']) && $result['error']){
						$response['error'][] 	= $result['error'];
					}
				}
		}else{
				foreach ($productFinalReport as $key => $product_field) {
					$this->report_product_all[$product_field['asin1']] = $product_field;
				}
				$result = $this->getAmazonProductByAsin(array('product_asin' => $product_asin, 'account_details' => $getAccountDetails));

				if(isset($result['success']) && $result['success']){
					$response['success'][$result['success']['data']] 	= $result['success'];
				}
				if(isset($result['error']) && $result['error']){
					$response['error'][] 	= $result['error'];
				}
		}

		return $response;
	}

	 /**
	 * getAmazonProductByAsin function used to get the process all the import part of product to opencart like save product specification, combinations of products and save them to opencart end.
	 */
	 public function getAmazonProductByAsin($data = array())
    {
        $product_attribute  = $product_array = $import_products = array();

        $languages  = $this->model_localisation_language->getLanguages();
				$stores     = $this->model_setting_store->getStores();

				array_push($stores,array('store_id' => 0,'name' => 'Default Store','url' => HTTP_CATALOG, 'ssh' => ''));

        if(isset($data['product_asin'])){
            /**
             * get AttributeSets of amazon product
             */
            $getProductDetails  = $this->Amazonconnector->getMatchedProduct($data['product_asin'], $data['account_details']['id']);

            if(isset($getProductDetails['GetMatchingProductResult']['@attributes']['status']) && $getProductDetails['GetMatchingProductResult']['@attributes']['status'] != 'Success'){
								if(isset($getProductDetails['GetMatchingProductResult']['Error'])){
										$import_products['error'] = array(
	                                                'status'    => false,
	                                                'message'   => 'Warning: '. $getProductDetails['GetMatchingProductResult']['Error']['Message'],
	                                                );
								}else{
										$import_products['error'] = array(
																									'status'    => false,
																									'message'   => "Error: Amazon product Id: ".$data['product_asin']." failed for mapped with opencart as product status is not Success from amazon store!",
																									);
								}

                return $import_products;
            }

            // get config value
            $product_quantity   = $this->config->get('wk_amazon_connector_default_quantity');
            $product_price      = '50';
            $product_weight     = $this->config->get('wk_amazon_connector_default_weight');
            $product_category   = $this->config->get('wk_amazon_connector_default_category');

            // get default attribute group value
            if(isset($data['account_details']['wk_amazon_connector_attribute_group']) && $data['account_details']['wk_amazon_connector_attribute_group']){
                $product_attribute_group = $data['account_details']['wk_amazon_connector_attribute_group'];
            }else{
                $product_attribute_group = 3;
            }

            /**
            * get amazon product's attributes and Save them at opencatr end (specification)
            */
            $product_dimensions = array();
            if(isset($getProductDetails['GetMatchingProductResult']['Product']['AttributeSets']['ns2ItemAttributes']) && !empty($getProductDetails['GetMatchingProductResult']['Product']['AttributeSets']['ns2ItemAttributes'])){
                $product_attributes = $getProductDetails['GetMatchingProductResult']['Product']['AttributeSets']['ns2ItemAttributes'];
                $mappedAttributes   = $this->createAttributes($product_attributes, $product_attribute_group, $data['account_details']['id']);

                if(isset($mappedAttributes['product_dimensions']['ItemDimensions']) && $mappedAttributes['product_dimensions']['ItemDimensions']){
                    $product_dimensions = array(
                                            'length'    => $mappedAttributes['product_dimensions']['ItemDimensions']['lenght'],
                                            'width'     => $mappedAttributes['product_dimensions']['ItemDimensions']['width'],
                                            'height'    => $mappedAttributes['product_dimensions']['ItemDimensions']['height'],
                                            'length_class_id'   => 1,
                                            );
                    $product_weight     = $mappedAttributes['product_dimensions']['ItemDimensions']['height'];
                }else{
									$product_dimensions = array('length'    => 0,'width'     => 0,'height'    => 0,'length_class_id'   => 0,);
								}
                if(isset($mappedAttributes['attributes_data']) && $mappedAttributes['attributes_data']){
                    $product_attribute = $mappedAttributes['attributes_data'];
                }
            }else{
                $product_dimensions = array('length'    => 0,'width'     => 0,'height'    => 0,'length_class_id'   => 0,);
            }

						// if product data is not set, then get price through product_asin
            if(empty($data['product_data']) && !isset($data['product_data'])){
                // $getCompletePriceProductData = $this->Amazonconnector->GetMyPriceForASIN($data['product_asin'], $data['account_details']['id']);

                foreach ($languages as $key => $language) {
                    $product_description[$language['language_id']] = array(
                        'name'          => str_replace('"', '', $getProductDetails['GetMatchingProductResult']['Product']['AttributeSets']['ns2ItemAttributes']['ns2Title']),
                        'description'   => str_replace('"', '', $getProductDetails['GetMatchingProductResult']['Product']['AttributeSets']['ns2ItemAttributes']['ns2Title']),
                        'meta_title'    => str_replace('"', '', $getProductDetails['GetMatchingProductResult']['Product']['AttributeSets']['ns2ItemAttributes']['ns2Title']));
                }
                $product_sku  = $getProductDetails['GetMatchingProductResult']['Product']['Identifiers']['MarketplaceASIN']['ASIN'];

                if (isset($getCompletePriceProductData['GetMyPriceForASINResult']['Product']['Offers']['Offer'])) {
                    if (isset($getCompletePriceProductData['GetMyPriceForASINResult']['Product']['Offers']['Offer']['RegularPrice'])) {
                        // get product price
                        $product_price = $getCompletePriceProductData['GetMyPriceForASINResult']['Product']['Offers']['Offer']['RegularPrice']['Amount']  * ($this->currency->getValue($this->config->get('config_currency')) / $data['account_details']['wk_amazon_connector_currency_rate']);
                    }
                }else{
										if(isset($this->report_product_all[$data['product_asin']]) && $this->report_product_all[$data['product_asin']]){
												$product_price      = $this->report_product_all[$data['product_asin']]['price'] * ($this->currency->getValue($this->config->get('config_currency')) / $data['account_details']['wk_amazon_connector_currency_rate']);
												$product_quantity   = $this->report_product_all[$data['product_asin']]['qty_avail'];
										}
								}
            }else{
                /**
                 * set product fields from current product data array $data['product_data']
                 */
                 foreach ($languages as $key => $language) {
                    $product_description[$language['language_id']] = array(
                        'name'          => str_replace('"', '', $data['product_data']['item-name']),
                        'description'   => str_replace('"', '', $data['product_data']['item-description']),
                        'meta_title'    => str_replace('"', '', $data['product_data']['item-name']));
                }
                $product_sku        = $data['product_data']['seller-sku'];
                $product_price      = $data['product_data']['price'] * ($this->currency->getValue($this->config->get('config_currency')) / $data['account_details']['wk_amazon_connector_currency_rate']);
                $product_quantity   = $data['product_data']['qty_avail'];
            }

						if(isset($getProductDetails['GetMatchingProductResult']['Product']['AttributeSets']['ns2ItemAttributes']['ns2SmallImage']['ns2URL'])){
							$defaultImage = $this->__saveproductImage($getProductDetails['GetMatchingProductResult']['Product']['AttributeSets']['ns2ItemAttributes']['ns2SmallImage']['ns2URL']);
						}else{
							$defaultImage = array('image' => '', 'amazon_image' => '');
						}
            // save product image to opencart

            // if product doesn't have any variation (Simple Product)
            if (empty($getProductDetails['GetMatchingProductResult']['Product']['Relationships'])) {
                /**For without variation product**/
			    		$product_array = array(
									'ItemID'							=> $data['product_asin'],
									'account_id'					=> $data['account_details']['id'],
				          'attribute_group'     => $product_attribute_group,
				          'model'            		=> $data['product_asin'],
									'sku'              		=> $product_sku,
									'quantity'         		=> $product_quantity,
									'stock_status_id'  		=> 7,
									'image'            		=> $defaultImage['image'],
									'amazon_image'      	=> $defaultImage['amazon_image'],
									'price'            		=> (float)$product_price,
									'tax_class_id'     		=> 0,
									'weight'           		=> $product_weight,
									'weight_class_id'  		=> 2,
									'subtract'         		=> 1,
									'minimum'          		=> 1,
									'sort_order'       		=> 1,
									'status'           		=> 1,
									'shipping'         		=> 1,
									'category_id'					=> $product_category,
									'product_description' => $product_description,
									'product_attribute'	  => $product_attribute,
									'product_option'			=> array(),
									'product_condition'	  => array(),
									'product_store'				=> $stores,
									'amazonProductType' 	=> 'ASIN',
									'amazonProductTypeValue' => $data['product_asin'],
								);

            }else{
							if($this->config->get('wk_amazon_connector_variation') && ($this->config->get('wk_amazon_connector_variation') == 2)){
									$import_products['error'] = array(
																							'status'    => false,
																							'message'   => "Error: Amazon product Id: ".$data['product_asin']." will not imported because this product has variations!",
																							);
									return $import_products;
							}
								/**
                * If product have variations
                */
								$product_array = array(
									'ItemID'							=> $data['product_asin'],
									'account_id'					=> $data['account_details']['id'],
									'attribute_group'     => $product_attribute_group,
									'model'            		=> $data['product_asin'],
									'sku'              		=> $data['product_asin'],
									'quantity'         		=> $product_quantity,
									'stock_status_id'  		=> 7,
									'image'            		=> $defaultImage['image'],
									'amazon_image'      	=> $defaultImage['amazon_image'],
									'price'            		=> (float)$product_price,
									'tax_class_id'     		=> 0,
									'weight'           		=> $product_weight,
									'weight_class_id'  		=> 2,
									'subtract'         		=> 1,
									'minimum'          		=> 1,
									'sort_order'       		=> 1,
									'status'           		=> 1,
									'shipping'         		=> 1,
									'category_id'					=> $product_category,
									'product_description' => $product_description,
									'product_attribute'		=> $product_attribute,
									'product_store'				=> $stores,
								);

								/**
								* Check if variation(option) have nay parent product(means main product in opencart terms)
								*/
                if (isset($getProductDetails['GetMatchingProductResult']['Product']['Relationships']['VariationParent'])) {
                    $parentAsinId = $this->Amazonconnector->checkParentAsinValue($getProductDetails['GetMatchingProductResult']['Product']['Relationships']['VariationParent']);
										$product_array['parent_id'] = $parentAsinId;
                }else{
										$parentAsinId = $data['product_asin'];
								}
								$product_array['amazonProductType'] 			= 'ASIN';
								$product_array['amazonProductTypeValue'] 	= $parentAsinId;

                $parentAsinData   = $this->Amazonconnector->getMatchedProduct($parentAsinId, $data['account_details']['id']);

								$getTotalQuantity = $getMinimumOptionPrice = 0;
                if (isset($parentAsinData['GetMatchingProductResult']['Product']['Relationships']['ns2VariationChild']) && $parentAsinData['GetMatchingProductResult']['Product']['Relationships']['ns2VariationChild']) {

										foreach ($languages as $key => $language) {
											$product_array['product_description'][$language['language_id']] = array(
												'name'          => str_replace('"', '', $parentAsinData['GetMatchingProductResult']['Product']['AttributeSets']['ns2ItemAttributes']['ns2Title']),
												'description'   => str_replace('"', '', isset($data['product_data']['item-description']) ? $data['product_data']['item-description'] : ''),
												'meta_title'    => str_replace('"', '', $parentAsinData['GetMatchingProductResult']['Product']['AttributeSets']['ns2ItemAttributes']['ns2Title']));
										}
									$product_array['ItemID'] 	= $product_array['model'] = $product_array['sku'] = $parentAsinData['GetMatchingProductResult']['Product']['Identifiers']['MarketplaceASIN']['ASIN'];

									/**
									* Save Option images to oc
									*/
									$product_array_image 			= $this->__saveproductImage($parentAsinData['GetMatchingProductResult']['Product']['AttributeSets']['ns2ItemAttributes']['ns2SmallImage']['ns2URL']);
									$product_array['image'] 				= $product_array_image['image'];
									$product_array['amazon_image'] 	= $product_array_image['amazon_image'];

                    foreach ($parentAsinData as $key => $parentVal) {
												if($key == 'GetMatchingProductResult'){
                          $product_images = $option_details = $product_option = array();

                            if(isset($parentVal['Product']['Relationships']['ns2VariationChild']['Identifiers'])){
															$parentVal['Product']['Relationships']['ns2VariationChild'] = [$parentVal['Product']['Relationships']['ns2VariationChild']];
														}
														if(isset($parentVal['Product']['Relationships']['ns2VariationChild'])){
                                foreach ($parentVal['Product']['Relationships']['ns2VariationChild'] as $key_var => $variation_array) {


                                    $childAsin = '';
                                    if (isset($variation_array['Identifiers'])) {
                                        $savedVariation = $this->createVariationOpencart($variation_array);
                                        $childAsin      = $variation_array['Identifiers']['MarketplaceASIN']['ASIN'];
                                    }

                                    $amzAssociateData   = $this->Amazonconnector->getMatchedProduct($childAsin, $data['account_details']['id']);

                                    if(isset($savedVariation['id']) && $savedVariation['id'] && (isset($this->report_product_all[$childAsin]))){
                                        $opt_name       = $this->report_product_all[$childAsin]['item-name'];
                                        $opt_sku        = $this->report_product_all[$childAsin]['seller-sku'];
																				$opt_id_type    = $savedVariation['id_type'];
																				$opt_id_value   = $savedVariation['id_value'];
                                        $opt_image_name = $this->__saveproductImage($amzAssociateData['GetMatchingProductResult']['Product']['AttributeSets']['ns2ItemAttributes']['ns2SmallImage']['ns2URL']);

																				$opt_price 		= $this->report_product_all[$childAsin]['price'] ;
																				$opt_quantity = $this->report_product_all[$childAsin]['quantity'];

																				//total product quantity
																				$getTotalQuantity = $getTotalQuantity + $opt_quantity;

																				//get minimum option price for product
																				if($getMinimumOptionPrice == 0){
																					$getMinimumOptionPrice = $opt_price;
																				}else if($opt_price < $getMinimumOptionPrice){
																					$getMinimumOptionPrice = $opt_price;
																				}

										                    $product_images[] = array('sort_order' => $key, 'image' => $opt_image_name['image']);
										                    $option_details = array(
										                        'name'          => str_replace('"', '', $opt_name),
										                        'sku'           => $opt_sku,
																						'id_type'				=> $opt_id_type,
																						'id_value'			=> $opt_id_value,
										                        'price'         => $opt_price,
										                        'quantity'      => $opt_quantity,
										                        'image'         => $opt_image_name['image'],
										                        'amazon_image'  => $opt_image_name['amazon_image'],
										                    );
										                    $product_option[] = array_merge($savedVariation, $option_details);

										                    $this->db->query("UPDATE ".DB_PREFIX."option_value SET `image` = '".$opt_image_name['image']."' WHERE option_value_id = '".(int)$savedVariation['variation_value_id']."' AND option_id = '".(int)$savedVariation['variation_id']."' ");
										                    }
										                }
										            }

                            $product_options = $product_option_value = array();
                            if(!empty($product_option)){
                                $option_id = false;
                                foreach ($product_option as $key_opt => $p_option) {
                                        $option_id = $p_option['variation_id'];
                                        $opt_price = $p_option['price'];

                                        if((float)$opt_price >(float)$getMinimumOptionPrice){
                                            $option_price = (float)$opt_price - (float)$getMinimumOptionPrice;
                                            $prefix = '+';
                                        }else{
                                            $option_price = (float)$getMinimumOptionPrice - (float)$opt_price;
                                            $prefix = '-';
                                        }
                                        $product_option_value[] = array(
                                            'option_value_id'	=> $p_option['variation_value_id'],
                                            'quantity'				=> $p_option['quantity'],
                                            'price'						=> $option_price  * ($this->currency->getValue($this->config->get('config_currency')) / $data['account_details']['wk_amazon_connector_currency_rate']),
                                            'subtract'				=> 1,
                                            'price_prefix' 		=> $prefix,
																						'sku'           	=> $p_option['sku'],
																						'id_type'					=> $p_option['id_type'],
																						'id_value' 				=> $p_option['id_value'],
																					);
                                }
                                $product_options[] = array(
                                    'name'				=> 'Amazon Variations',
                                    'type'				=> 'select',
                                    'option_id'		=> $option_id,
                                    'required'		=> 1,
                                    'product_option_value' => $product_option_value,);
                            }
												$product_array['price']					= $getMinimumOptionPrice  * ($this->currency->getValue($this->config->get('config_currency')) / $data['account_details']['wk_amazon_connector_currency_rate']);
												$product_array['quantity']			= $getTotalQuantity;
                        $product_array['product_image']	= $product_images;
                        $product_array['product_option']= $product_options;
												$this->product_manage[$product_array['ItemID']] = $product_array;
                    }// if condition
									}// loop for variation of child
            }
        }

						$this->product_manage[$data['product_asin']] = $product_array = array_merge($product_array, $product_dimensions);

		        $getMappedEntry = $this->getProductMappedEntry(array('filter_amazon_product_id' => $product_array['ItemID'], 'account_id' => $product_array['account_id']));

						if(!empty($getMappedEntry) && isset($getMappedEntry[0])){
							$product_array['product_id'] = $getMappedEntry[0]['oc_product_id'];
								if($product_id = $this->__editAmazonProduct($product_array)){
									$import_products['success'] = array(
			                                            'data'      => $product_id,
			                                            'message'   => "Success: Amazon product Id: ".$product_array['ItemID']." successfully updated with Opencart product Id: ".$product_id,
			                                            );
								}
						}else{
								if($product_id = $this->__saveAmazonProduct($product_array)){
									$import_products['success'] = array(
			                                            'data'      => $product_id,
			                                            'message'   => "Success: Amazon product Id: ".$product_array['ItemID']." successfully mapped with Opencart product Id: ".$product_id,
			                                            );
								}
						}
        }

        return $import_products;
    }

	/**
	* getProductMappedEntry used to get the mapped product with filter conditions
	*/
	public function getProductMappedEntry($data = array()) {

		$sql = "SELECT apm.*, apf.*, apm.id as map_id, pd.name as product_name, p.model, p.price, p.quantity FROM ".DB_PREFIX."amazon_product_map apm LEFT JOIN ".DB_PREFIX."amazon_product_fields apf ON (apm.oc_product_id = apf.product_id) LEFT JOIN ".DB_PREFIX."product p ON (apm.oc_product_id = p.product_id) LEFT JOIN ".DB_PREFIX."product_description pd ON(apm.oc_product_id = pd.product_id) WHERE p.status = '1' AND pd.language_id = '".(int)$this->config->get('config_language_id')."' ";

		if(!empty($data['filter_map_id'])){
			$sql .= " AND apm.id = '".(int)$data['filter_map_id']."' ";
		}

		if(!empty($data['filter_oc_product_id'])){
			$sql .= " AND apm.oc_product_id = '".(int)$data['filter_oc_product_id']."' AND p.product_id = '".(int)$data['filter_oc_product_id']."' ";
		}

		if(!empty($data['filter_amazon_product_id'])){
			$sql .= " AND apm.amazon_product_id = '".$this->db->escape($data['filter_amazon_product_id'])."' ";
		}

		if(isset($data['filter_amazon_product_sku']) && $data['filter_amazon_product_sku']){
            $sql .= " AND apm.amazon_product_sku = '".$this->db->escape($data['filter_amazon_product_sku'])."' ";
        }

		if(isset($data['account_id']) && $data['account_id']){
        $sql .= " AND apm.account_id = '".(int)$data['account_id']."' ";
    }

		if(isset($data['sync_source']) && $data['sync_source']){
        $sql .= " AND apm.sync_source = '".$this->db->escape($data['sync_source'])."' ";
    }

			$sort_data = array(
				'product_name',
				'p.model',
				'p.price',
				'p.quantity',
				'apm.id',
				'apm.oc_product_id',
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY apm.id";
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
	* getTotalProductMappedEntry used to get the total number of mapped products with filter conditions
	*/
	public function getTotalProductMappedEntry($data = array()) {
		$sql = "SELECT COUNT(DISTINCT apm.id) as total FROM ".DB_PREFIX."amazon_product_map apm LEFT JOIN ".DB_PREFIX."product p ON (apm.oc_product_id = p.product_id) LEFT JOIN ".DB_PREFIX."product_description pd ON(apm.oc_product_id = pd.product_id) WHERE p.status = '1' AND pd.language_id = '".(int)$this->config->get('config_language_id')."' ";

		if(!empty($data['filter_map_id'])){
			$sql .= " AND apm.id = '".(int)$data['filter_map_id']."' ";
		}

		if(!empty($data['filter_oc_product_id'])){
			$sql .= " AND apm.oc_product_id = '".(int)$data['filter_oc_product_id']."' AND p.product_id = '".(int)$data['filter_oc_product_id']."' ";
		}

		if(!empty($data['filter_amazon_product_id'])){
			$sql .= " AND apm.amazon_product_id = '".$this->db->escape($data['filter_amazon_product_id'])."' ";
		}

		if(isset($data['filter_amazon_product_sku']) && $data['filter_amazon_product_sku']){
						$sql .= " AND apm.amazon_product_sku = '".$this->db->escape($data['filter_amazon_product_sku'])."' ";
				}

		if(isset($data['account_id']) && $data['account_id']){
				$sql .= " AND apm.account_id = '".(int)$data['account_id']."' ";
		}

		if(isset($data['sync_source']) && $data['sync_source']){
				$sql .= " AND apm.sync_source = '".$this->db->escape($data['sync_source'])."' ";
		}

		$result = $this->db->query($sql)->row;

		return $result['total'];
	}

		public function deleteMapProducts($map_id, $account_id){
				$result = $product_data = array();
				$this->load->model('catalog/product');
				$getProductEntry = $this->getProductMappedEntry(array('filter_map_id' => $map_id, 'account_id' => $account_id));

				if(!empty($getProductEntry) && isset($getProductEntry[0]['map_id']) && $getProductEntry[0]['map_id'] == $map_id){
						$product_data = $getProductEntry[0];

						$getOrderProduct = $this->orderProductCheck($product_data);
						if(empty($getOrderProduct)){
								$product_id = $product_data['oc_product_id'];
								if($product_data['sync_source'] == 'Amazon Item'){
										$this->db->query("DELETE FROM " . DB_PREFIX . "amazon_product_fields WHERE product_id = '" . (int)$product_id . "'");
										$this->db->query("DELETE FROM " . DB_PREFIX . "amazon_product_variation_map WHERE product_id = '" . (int)$product_id . "'");
										$this->db->query("DELETE FROM " . DB_PREFIX . "amazon_product_map WHERE oc_product_id = '" . (int)$product_id . "'");
										$this->model_catalog_product->deleteProduct($product_id);
								}else{
										$DeleteProductArray = array();
										$this->db->query("DELETE FROM " . DB_PREFIX . "amazon_product_variation_map WHERE product_id = '" . (int)$product_id . "'");
										$this->db->query("DELETE FROM " . DB_PREFIX . "amazon_product_map WHERE oc_product_id = '" . (int)$product_id . "'");

										if((isset($product_data['main_product_type']) && $product_data['main_product_type']) && (isset($product_data['main_product_type_value']) && $product_data['main_product_type_value'])){

												if($getCombinations = $this->Amazonconnector->_getProductVariation($product_id, $type = 'amazon_product_variation_value')){
														foreach ($getCombinations as $option_id => $combination_array) {
																foreach ($combination_array['option_value'] as $key => $combination_value) {
																	//delete amazon product
																	$DeleteProductArray[] = array(
																															'sku' => $combination_value['sku'],
																													);
																}
														}
												}else{
														//delete amazon product
														$DeleteProductArray[] = array(
																											'sku' => (!empty($product_data['sku']) ? $product_data['sku'] : 'oc_prod_'.$product_id),
																									);
												}
												$this->Amazonconnector->product['ActionType']  = 'DeleteProduct';
												$this->Amazonconnector->product['ProductData'] = $DeleteProductArray;

												$product_updated = $this->Amazonconnector->submitFeed($feedType = '_POST_PRODUCT_DATA_', $account_id);
										}
								}
								$result = array('status' => true, 'message' => sprintf($this->language->get('text_success_delete'), $product_data['amazon_product_id']). $product_id);
						}else if(isset($getOrderProduct[0]['order_product_id']) && $getOrderProduct[0]['order_product_id']){
								$result = array('status' => false, 'message' => sprintf($this->language->get('error_found_order1'), $getOrderProduct[0]['name']).sprintf($this->language->get('error_found_order2'), $getOrderProduct[0]['amazon_order_id'].' which is mapped with Opencart Order-Id: '. $getOrderProduct[0]['order_id']));
						}
				}
				return $result;
		}

		public function orderProductCheck($data = array()){
			$sql = "SELECT op.*, aom.amazon_order_id FROM ".DB_PREFIX."order_product op LEFT JOIN ".DB_PREFIX."order o ON(op.order_id = o.order_id) LEFT JOIN ".DB_PREFIX."amazon_order_map aom ON(o.order_id = aom.oc_order_id) WHERE o.language_id = '".(int)$this->config->get('config_language_id')."' ";

				if(!empty($data['account_id'])){
					$sql .= " AND aom.account_id = '".(int)$data['account_id']."' ";
				}

				if(!empty($data['oc_product_id'])){
					$sql .= " AND op.product_id = '".(int)$data['oc_product_id']."' ";
				}

				if(!empty($data['oc_order_id'])){
					$sql .= " AND aom.oc_order_id = '".(int)$data['oc_order_id']."' AND o.order_id = '".(int)$data['oc_order_id']."' ";
				}

				$results = $this->db->query($sql)->rows;
				return $results;
		}

	  /**
    * createAttributes used to manage the amazon product specifications, and saved to opencart store as attributes.
    */
    public function createAttributes($amazon_attributes, $attribute_group_id, $account_id)
    {
        $attribute_data = $oc_attributes = $product_dimensions = array();
        $remove_attributes = array('Title', 'SmallImage', 'PackageQuantity');

        $languages = $this->model_localisation_language->getLanguages();
        foreach ($amazon_attributes as $key => $p_attribute) {
            $attribute_name     = substr($key,3);
            $oc_attribute_value = array();
            $oc_attributes      = array(
                                    'attribute_group_id'    => $attribute_group_id,
                                    'sort_order'            => 0,
                                    );
            if(!in_array($attribute_name, $remove_attributes)){
                if($attribute_name == 'ItemDimensions' && is_array($p_attribute)){
                    $itemDimStr = '';
                    // (L*W*H)-> inch to cm
                    if(isset($p_attribute['ns2Length'])){
                        $product_dimensions['ItemDimensions']['lenght'] = $p_attribute['ns2Length'] * 2.54;
                        $itemDimStr .= number_format(($p_attribute['ns2Length'] * 2.54), 1, '.', '') .' * ';
                    }else{
                        $product_dimensions['ItemDimensions']['lenght'] = 0;
                    }
                    if(isset($p_attribute['ns2Width'])){
                        $product_dimensions['ItemDimensions']['width'] = $p_attribute['ns2Width'] * 2.54;
                        $itemDimStr .= number_format(($p_attribute['ns2Width'] * 2.54), 1, '.', '') .' * ';
                    }else{
                        $product_dimensions['ItemDimensions']['width'] = 0;
                    }
                    if(isset($p_attribute['ns2Height'])){
                        $product_dimensions['ItemDimensions']['height'] = $p_attribute['ns2Height'] * 2.54;
                        $itemDimStr .= number_format(($p_attribute['ns2Height'] * 2.54), 1, '.', '') .' cm';
                    }else{
                        $product_dimensions['ItemDimensions']['height'] = 0;
                    }
                    foreach ($languages as $key1 => $language) {
                       $oc_attributes['attribute_description'][$language['language_id']] = array('name' => $attribute_name, 'value' => $itemDimStr);
                       $oc_attribute_value[$language['language_id']] = array('text' => $itemDimStr);
                    }
                    // (weight)-> pound to gram
                    if(isset($p_attribute['ns2Weight'])){
                        $product_dimensions['ItemDimensions']['weight'] = $p_attribute['ns2Weight'] * 453.592;
                    }
                }else if($attribute_name == 'PackageDimensions' && is_array($p_attribute)){
                    $packDimStr = '';
                    // (L*W*H)-> inch to cm
                    if(isset($p_attribute['ns2Length'])){
                        $product_dimensions['PackageDimensions']['lenght'] = $p_attribute['ns2Length'] * 2.54;
                        $packDimStr .= number_format(($p_attribute['ns2Length'] * 2.54), 1, '.', '')  .' * ';
                    }else{
                        $product_dimensions['PackageDimensions']['lenght'] = 0;
                    }
                    if(isset($p_attribute['ns2Width'])){
                        $product_dimensions['PackageDimensions']['width'] = $p_attribute['ns2Width'] * 2.54;
                        $packDimStr .= number_format(($p_attribute['ns2Width'] * 2.54), 1, '.', '') .' * ';
                    }else{
                        $product_dimensions['PackageDimensions']['width'] = 0;
                    }
                    if(isset($p_attribute['ns2Height'])){
                        $product_dimensions['PackageDimensions']['height'] = $p_attribute['ns2Height'] * 2.54;
                        $packDimStr .= number_format(($p_attribute['ns2Height'] * 2.54), 1, '.', '') .' cm';
                    }else{
                        $product_dimensions['PackageDimensions']['height'] = 0;
                    }
                    foreach ($languages as $key1 => $language) {
                       $oc_attributes['attribute_description'][$language['language_id']] = array('name' => $attribute_name, 'value' => $packDimStr);
                       $oc_attribute_value[$language['language_id']] = array('text' => $packDimStr);
                    }
                    // (weight)-> pound to gram
                    if(isset($p_attribute['ns2Weight'])){
                        $product_dimensions['PackageDimensions']['weight'] = $p_attribute['ns2Weight'] * 453.592;
                    }
                }else if(!is_array($p_attribute) && isset($p_attribute)){
                   foreach ($languages as $key1 => $language) {
                       $oc_attributes['attribute_description'][$language['language_id']] = array('name' => $attribute_name, 'value' => $p_attribute);
                       $oc_attribute_value[$language['language_id']] = array('text' => $p_attribute);
                   }
                }
                if(!empty($oc_attributes) && isset($oc_attributes['attribute_description'])){
                    $filter_data = array(
                        'account_group_id'  => $attribute_group_id,
                        'account_id'        => $account_id,
                        'attr_code_map'     => 'attr_'.strtolower($attribute_name).'_'.$account_id.'_'.$attribute_group_id,
                    );
										$result = $this->checkAttributeMapEntry($filter_data);

                    if(!$result = $this->checkAttributeMapEntry($filter_data)){
                        if($getAttributeId  = $this->model_catalog_attribute->addAttribute($oc_attributes)){
                            if($getAttributeMapId  = $this->saveAttributeMapEntry(array('oc_attribute_id' => $getAttributeId, 'account_group_id' => $attribute_group_id, 'attr_code_map' => 'attr_'.strtolower($attribute_name).'_'.$account_id.'_'.$attribute_group_id, 'account_id' => $account_id))){

                                array_push($attribute_data, array('attribute_id' => $getAttributeId, 'product_attribute_description' => $oc_attribute_value, 'attribute_map_id' => $getAttributeMapId));
                            }
                        }
                    }else{
                        array_push($attribute_data, array('attribute_id' => $result['attribute_id'], 'product_attribute_description' => $oc_attribute_value, 'attribute_map_id' => $result['id']));
                    }
                }
            }
        }

        return array('attributes_data' => $attribute_data, 'product_dimensions' => $product_dimensions);
    }

	 /**
    * checkAttributeMapEntry used to check the amazon product's attributes entry already saved or not to opencart store attributes based on account_id, account_group_id and attr_code_map
    */
    public function checkAttributeMapEntry($data = array()){
        $sql = "SELECT * FROM ".DB_PREFIX."amazon_attribute_map aam LEFT JOIN ".DB_PREFIX."attribute a ON ((aam.oc_attribute_id = a.attribute_id) AND (aam.account_group_id = a.attribute_group_id)) LEFT JOIN ".DB_PREFIX."attribute_description ad ON(a.attribute_id = ad.attribute_id) WHERE ad.language_id = '".(int)$this->config->get('config_language_id')."' ";

        if(!empty($data['account_group_id'])){
					$sql .= " AND aam.account_group_id ='".(int)$data['account_group_id']."' AND a.attribute_group_id = '".(int)$data['account_group_id']."' ";
				}

        if(!empty($data['oc_attribute_id'])){
					$sql .= " AND aam.oc_attribute_id ='".(int)$data['oc_attribute_id']."' AND a.attribute_id = '".(int)$data['oc_attribute_id']."' ";
				}

        if(!empty($data['account_id'])){
					$sql .= " AND aam.account_id ='".(int)$data['account_id']."' ";
				}

				if(!empty($data['attr_code_map'])){
					$sql .= " AND aam.attr_code_map = '".$this->db->escape($data['attr_code_map'])."' ";
				}

        return $result = $this->db->query($sql)->row;
    }
    /**
    * saveAttributeMapEntry used to save the amazon product's attributes entry to opencart store
    */
    public function saveAttributeMapEntry($data = array()){
        $result = false;
        if(isset($data['oc_attribute_id']) && $data['oc_attribute_id']){
            $this->db->query("INSERT INTO ".DB_PREFIX."amazon_attribute_map SET `oc_attribute_id` = '".(int)$data['oc_attribute_id']."', `account_group_id` = '".(int)$data['account_group_id']."', `attr_code_map` = '".$this->db->escape($data['attr_code_map'])."', `account_id` = '".(int)$data['account_id']."' ");

            $result = $this->db->getLastId();
        }
        return $result;
    }

  /**
	* createVariationOpencart function used to manage the amazon product's combinations/variations/options and make array structure like in opencart product's options.
	*/
	public function createVariationOpencart($variation_data = array()){
        $languages = $this->model_localisation_language->getLanguages();
        $savedVariation = array();
        try{
            $allVariations = $this->getVariationWithValues($variation_data);

            if(!empty($allVariations) && isset($allVariations['option_value'])) {
                $getVariation = $this->__getVariationEntry(array('variation_name' => $allVariations['option_value']));

								if(empty($getVariation)){
										$savedVariation = $this->__save_Variation($allVariations);
								}else{
                    $savedVariation = $getVariation;
                }
								$savedVariation['id_type']	= $allVariations['id_type'];
								$savedVariation['id_value']	= $allVariations['id_value'];
						}

        } catch (\Exception $e) {
            $this->log->write('Create variationOpencart : '.$e->getMessage());
        }

        return $savedVariation;
    }

	/**
	 * __save_Variation function used to save the amazon product's combinations/variations/options in opencart store as options.
	 */
	 public function __save_Variation($option = array()){
		$amazon_OptVar = array();

		if(!empty($option) && ($getGlobal_Option = $this->Amazonconnector->__getOcAmazonGlobalOption())){
				if (isset($option['option_value'])) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "option_value SET option_id = '" . (int)$getGlobal_Option['option_id'] . "', image = '" . $this->db->escape(html_entity_decode('', ENT_QUOTES, 'UTF-8')) . "', sort_order = '0'");

						$option_value_id = $this->db->getLastId();

						$this->db->query("INSERT INTO " . DB_PREFIX . "option_value_description SET option_value_id = '" . (int)$option_value_id . "', language_id = '1', option_id = '" . (int)$getGlobal_Option['option_id'] . "', name = '" . $this->db->escape($option['option_value']) . "'");

						try {
		            $this->db->query("INSERT INTO ".DB_PREFIX."amazon_variation_map SET `variation_id` = '".(int)$getGlobal_Option['option_id']."', `variation_value_id` = '".(int)$option_value_id."',  `value_name` = '".$this->db->escape($option['option_value'])."', `label` = '".serialize($option['option_name'])."' ");

								$amazon_OptVar_Id = $this->db->getLastId();
	              if($amazon_OptVar_Id){
	                  $amazon_OptVar = array('id' => $amazon_OptVar_Id, 'variation_id' => $getGlobal_Option['option_id'], 'variation_value_id' => $option_value_id, 'value_name' => $this->db->escape($option['option_value']), 'label' => serialize($option['option_name']), 'id_type' => $option['id_type'], 'id_value' => $option['id_value'] );
	              }
	          } catch (\Exception $e) {
	              $this->log->write('Save Variation to Opencart Store : '.$e->getMessage());
	          }
			 }
      return $amazon_OptVar;
		}

		return $amazon_OptVar;
	}

		/**
    * getVariationWithValues Function used to make variations like $key => $value pair.
    */
    public function getVariationWithValues($variation_data)
    {
        $variation_array    = $variations_arr = $opt_value = array();
        $make_option_value  = '';
        // if not ['Identifiers']
        if(isset($variation_data[0])){
            $variation_array = $variation_data;
        }else{
            //if [Identifiers]
            $variation_array = array($variation_data);
        }

        foreach ($variation_array as $key => $variations) {
            foreach ($variations as $var_key => $variation) {
                if($var_key == ''  || $variation == ''){
                    continue;
                }
                if($var_key != 'Identifiers'){
                    $opt_value['option_name'][]     = str_replace('ns2', '', $var_key);

                    if(isset($opt_value['option_value']) && $opt_value['option_value'] != ''){
											$opt_value['option_value'] = $make_option_value .' # '. $variation;
										}else{
											$opt_value['option_value']  = $make_option_value = $variation;
										}
                    $variations_arr = $opt_value;
                }
								if($var_key == 'Identifiers'){
										if(isset($variation['MarketplaceASIN']['ASIN'])){
												$opt_value['id_type'] = 'ASIN';
												$opt_value['id_value'] = $variation['MarketplaceASIN']['ASIN'];
										}else{
											$getIdType = array_slice($variation['MarketplaceASIN'], 1, 1);
												foreach ($getIdType as $key => $value) {
													$opt_value['id_type'] = strtoupper($key);
													$opt_value['id_value'] = $value;
												}
										}
										$variations_arr = $opt_value;
								}
            }
        }

        return $variations_arr;
    }

	/**
	* __getVariationEntry Function used to get the amazon saved variations with filters.
	*/
	public function __getVariationEntry($variation_data = array()){
		$result = array();
		if($variation_data){
			$getGlobalOption = $this->Amazonconnector->__getOcAmazonGlobalOption();

            $sql = "SELECT avm.* FROM ".DB_PREFIX."amazon_variation_map avm LEFT JOIN ".DB_PREFIX."option_value ov ON((avm.variation_id = ov.option_id) AND (avm.variation_value_id = ov.option_value_id)) LEFT JOIN ".DB_PREFIX."option op ON(avm.variation_id = op.option_id) WHERE 1 ";

            if(!empty($getGlobal_Option['option_id']) && !isset($variation_data['variation_id'])){
                $sql .= " AND avm.variation_id = '".(int)$getGlobalOption['option_id']."' AND ov.option_id = '".(int)$getGlobalOption['option_id']."' AND op.option_id = '".(int)$getGlobalOption['option_id']."' ";
            }else if(!empty($variation_data['variation_id'])){
                 $sql .= " AND avm.variation_id = '".(int)$variation_data['variation_id']."' AND ov.option_id = '".(int)$variation_data['variation_id']."' AND op.option_id = '".(int)$variation_data['variation_id']."' ";
            }

            if(!empty($variation_data['variation_name'])){
                $sql .= " AND avm.value_name = '".$this->db->escape($variation_data['variation_name'])."' ";
            }

            if(!empty($variation_data['id'])){
                $sql .= " AND avm.id = '".$this->db->escape($variation_data['id'])."' ";
            }
            $result = $this->db->query($sql)->row;
		}
		return $result;
	}

	/**
	* getProductOptions Function used to get the amazon imported product's options.
	*/
	public function getProductOptions($data = array())
	{
		$sql = "SELECT pov.*, avm.*, apm.oc_product_id, apm.amazon_product_id, apvm.id_type, apvm.id_value, apvm.sku FROM ".DB_PREFIX."product_option_value pov LEFT JOIN ".DB_PREFIX."product_option po ON ((pov.product_option_id = po.product_option_id) AND (pov.option_id = po.option_id) AND (pov.product_id = po.product_id)) LEFT JOIN ".DB_PREFIX."option_value ov ON ((pov.option_value_id = ov.option_value_id) AND (pov.option_id = ov.option_id)) LEFT JOIN ".DB_PREFIX."option_value_description ovd ON ((ov.option_id = ovd.option_id) AND (ov.option_value_id = ovd.option_value_id)) LEFT JOIN ".DB_PREFIX."amazon_variation_map avm ON ((pov.option_id = avm.variation_id) AND (pov.option_value_id = avm.variation_value_id)) LEFT JOIN ".DB_PREFIX."amazon_product_variation_map apvm ON ((pov.product_option_value_id = apvm.product_option_value_id) AND (pov.option_value_id = apvm.option_value_id) AND (pov.product_id = apvm.product_id)) LEFT JOIN ".DB_PREFIX."amazon_product_map apm ON(pov.product_id = apm.oc_product_id) WHERE ovd.language_id = '".(int)$this->config->get('config_language_id')."' ";

		if(!empty($data['oc_product_id'])){
			$sql .= " AND pov.product_id = '".(int)$data['oc_product_id']."' AND apm.oc_product_id = '".(int)$data['oc_product_id']."' ";
		}

		if(!empty($data['amazon_product_id'])){
			$sql .= " AND apm.amazon_product_id = '".$this->db->escape($data['amazon_product_id'])."' ";
		}

		if(!empty($data['option_id'])){
			$sql .= " AND pov.option_id = '".(int)$data['option_id']."' AND po.option_id = '".(int)$data['option_id']."' AND ov.option_id = '".(int)$data['option_id']."' AND ovd.option_id = '".(int)$data['option_id']."' AND avm.variation_id = '".(int)$data['option_id']."' ";
		}

		return $this->db->query($sql)->rows;
	}

  public function __saveproductImage($imageURL = false){
		$result = $allowed = array();
		$imageURLNew = urldecode($imageURL);
		if($imageURLNew && $this->checkImageExist($imageURLNew)){
			$path = DIR_IMAGE.'catalog/amazon_connector/';
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
			$explodeImagePath   = explode('/', $imageURLNew);
      $imageName          = end($explodeImagePath);
      $sperateNameExt     = explode('.', $imageName);

      $imgName = '';
      for ($i=0; $i < (count($sperateNameExt) -1) ; $i++) {
          $imgName        .= $sperateNameExt[$i].'.';
      }
      $checkExtention	     = strtolower(end($sperateNameExt));
			$extension_allowed   = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_ext_allowed'));
			$filetypes           = explode("\n", $extension_allowed);

      foreach ($filetypes as $filetype) {
				$allowed[]       = trim($filetype);
			}
			if(in_array($checkExtention, $allowed) && ($checkExtention !== 'php')){

				file_put_contents(html_entity_decode($path.trim($imgName, '.').'.'.$checkExtention), file_get_contents($imageURLNew));
                $image = new Image($path.trim($imgName, '.').'.'.$checkExtention);
				$image->resize(228, 228);
				$image->save($path.trim($imgName, '.').'.'.$checkExtention);
				return $result   = array('image' => 'catalog/amazon_connector/'.trim($imgName, '.').'.'.$checkExtention, 'amazon_image' => $imageURLNew);
			}
		}
	}


		function checkImageExist($url) {
		    $ch = curl_init();
		    curl_setopt($ch, CURLOPT_URL, $url);
		    curl_setopt($ch, CURLOPT_NOBODY, 1);
		    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		    if (curl_exec($ch) !== FALSE) {
		        return true;
		    } else {
		        return false;
		    }
		}

	public function __saveAmazonProduct($data = array()){
		$this->db->query("INSERT INTO " . DB_PREFIX . "product SET model = '" . $this->db->escape($data['model']) . "', sku = '" . $this->db->escape($data['sku']) . "', quantity = '" . (int)$data['quantity'] . "', minimum = '" . (int)$data['minimum'] . "', subtract = '" . (int)$data['subtract'] . "', stock_status_id = '" . (int)$data['stock_status_id'] . "', shipping = '" . (int)$data['shipping'] . "', price = '" . (float)$data['price'] . "', weight = '" . (float)$data['weight'] . "', weight_class_id = '" . (int)$data['weight_class_id'] . "', length = '" . (float)$data['length'] . "', width = '" . (float)$data['width'] . "', height = '" . (float)$data['height'] . "', length_class_id = '" . (int)$data['length_class_id'] . "', status = '" . (int)$data['status'] . "', tax_class_id = '" . (int)$data['tax_class_id'] . "', sort_order = '" . (int)$data['sort_order'] . "', date_added = NOW()");

		$product_id = $this->db->getLastId();

		if($product_id){
			$this->db->query("INSERT INTO " . DB_PREFIX . "amazon_product_map SET oc_product_id = '" . (int)$product_id . "', amazon_product_id = '" . $data['ItemID'] . "', `amazon_product_sku` = '".$this->db->escape($data['sku'])."', oc_category_id = '" . (int)$data['category_id'] . "', `amazon_image` = '".$this->db->escape($data['amazon_image'])."',  account_id = '".(int)$data['account_id']."', `sync_source` = 'Amazon Item', added_date = NOW() ");

			$map_product_id = $this->db->getLastId();

			$this->db->query("INSERT INTO " . DB_PREFIX . "amazon_product_fields SET product_id = '" . (int)$product_id . "', main_product_type = '".$data['amazonProductType']."', `main_product_type_value` = '".$this->db->escape($data['amazonProductTypeValue'])."' ");
		}


		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape($data['image']) . "' WHERE product_id = '" . (int)$product_id . "'");
		}

		foreach ($data['product_description'] as $language_id => $value) {
            str_replace('"', '', $value['name']);
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape(str_replace('"', '', $value['name'])) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape(str_replace('"', '', $value['meta_title'])) . "'");
		}

		if (isset($data['product_store'])) {
			foreach ($data['product_store'] as $store) {
                if($store['store_id'] == $this->config->get('wk_amazon_connector_default_product_store')){
                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store['store_id'] . "'");
                }
			}
		}

		if (isset($data['product_attribute'])) {
			foreach ($data['product_attribute'] as $product_attribute) {
				if ($product_attribute['attribute_id']) {
					// Removes duplicates
					$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

					foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
						$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "' AND language_id = '" . (int)$language_id . "'");

						$this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" .  $this->db->escape($product_attribute_description['text']) . "'");
					}
				}
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM ".DB_PREFIX."amazon_product_variation_map WHERE product_id = '".(int)$product_id."' ");

		if (isset($data['product_option'])) {
			foreach ($data['product_option'] as $product_option) {
				if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
					if (isset($product_option['product_option_value'])) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', required = '" . (int)$product_option['required'] . "'");

						$product_option_id = $this->db->getLastId();

						foreach ($product_option['product_option_value'] as $product_option_value) {
							$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '".(int)$product_option_id."', product_id = '" .(int)$product_id. "', option_id = '" . (int)$product_option['option_id'] . "', option_value_id = '" . (int)$product_option_value['option_value_id'] . "', quantity = '" . (int)$product_option_value['quantity'] . "', subtract = '" . (int)$product_option_value['subtract'] . "', price = '" . (float)$product_option_value['price'] . "', price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "', points = '0', points_prefix = '+', weight = '0.00000000', weight_prefix = '+'");

							$product_option_value_id = $this->db->getLastId();

							if(isset($product_option_value_id)){

									$this->db->query("INSERT INTO ".DB_PREFIX."amazon_product_variation_map SET `product_id` = '".(int)$product_id."', `product_option_value_id` = '".(int)$product_option_value_id."', `option_value_id` = '".(int)$product_option_value['option_value_id']."', `id_type` = '".$this->db->escape($product_option_value['id_type'])."', `id_value` = '".$this->db->escape($product_option_value['id_value'])."', `sku` = '".$this->db->escape($product_option_value['sku'])."', `main_product_type` = '".$this->db->escape($data['amazonProductType'])."', `main_product_type_value` = '".$this->db->escape(isset($data['amazonProductTypeValue']) ? $data['amazonProductTypeValue'] : '')."' ");
							}

						}
					}
				}
			}
		}
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "' AND category_id = '".(int)$data['category_id']."' ");

		if (isset($data['category_id'])) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$data['category_id'] . "'");
		}

		if (isset($data['product_image'])) {
			foreach ($data['product_image'] as $product_image) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape($product_image['image']) . "', sort_order = '0'");
			}
		}

		$this->cache->delete('product');

		return $product_id;
	}

	 public function __editAmazonProduct($data){
		$product_id = $data['product_id'];

		$this->db->query("UPDATE " . DB_PREFIX . "product SET model = '" . $this->db->escape($data['model']) . "', sku = '" . $this->db->escape($data['sku']) . "', quantity = '" . (int)$data['quantity'] . "', minimum = '" . (int)$data['minimum'] . "', subtract = '" . (int)$data['subtract'] . "', stock_status_id = '" . (int)$data['stock_status_id'] . "', shipping = '" . (int)$data['shipping'] . "', price = '" . (float)$data['price'] . "', weight = '" . (float)$data['weight'] . "', weight_class_id = '" . (int)$data['weight_class_id'] . "', length = '" . (float)$data['length'] . "', width = '" . (float)$data['width'] . "', height = '" . (float)$data['height'] . "', length_class_id = '" . (int)$data['length_class_id'] . "', status = '" . (int)$data['status'] . "', tax_class_id = '" . (int)$data['tax_class_id'] . "', sort_order = '" . (int)$data['sort_order'] . "' WHERE product_id = '".(int)$product_id."' ");

		if($product_id){
			$this->db->query("UPDATE " . DB_PREFIX . "amazon_product_map SET `amazon_product_sku` = '".$this->db->escape($data['sku'])."', oc_category_id = '" . (int)$data['category_id'] . "', `amazon_image` = '".$this->db->escape($data['amazon_image'])."'  WHERE oc_product_id = '".(int)$product_id."' AND amazon_product_id = '".$data['ItemID']."' AND account_id = '".(int)$data['account_id']."' ");

			$this->db->query("DELETE FROM " . DB_PREFIX . "amazon_product_fields WHERE product_id = '" . (int)$product_id . "'");

			$this->db->query("INSERT INTO " . DB_PREFIX . "amazon_product_fields SET product_id = '" . (int)$product_id . "', main_product_type = '".$data['amazonProductType']."', `main_product_type_value` = '".$this->db->escape($data['amazonProductTypeValue'])."' ");
		}


		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape($data['image']) . "' WHERE product_id = '" . (int)$product_id . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");

		foreach ($data['product_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "' ");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_store'])) {
			foreach ($data['product_store'] as $store) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store['store_id'] . "'");
			}
		}

		if (isset($data['product_attribute'])) {
			foreach ($data['product_attribute'] as $product_attribute) {
				if ($product_attribute['attribute_id']) {
					// Removes duplicates
					$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

					foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
						$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "' AND language_id = '" . (int)$language_id . "'");

						$this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" .  $this->db->escape($product_attribute_description['text']) . "'");
					}
				}
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM ".DB_PREFIX."amazon_product_variation_map WHERE product_id = '".(int)$product_id."' ");

		if (isset($data['product_option'])) {
			foreach ($data['product_option'] as $product_option) {
				if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
					if (isset($product_option['product_option_value'])) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', required = '" . (int)$product_option['required'] . "'");

						$product_option_id = $this->db->getLastId();

						foreach ($product_option['product_option_value'] as $product_option_value) {
							$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', option_value_id = '" . (int)$product_option_value['option_value_id'] . "', quantity = '" . (int)$product_option_value['quantity'] . "', subtract = '" . (int)$product_option_value['subtract'] . "', price = '" . (float)$product_option_value['price'] . "', price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "', points = '0', points_prefix = '+', weight = '0.00000000', weight_prefix = '+'");

							$product_option_value_id = $this->db->getLastId();

							if(isset($product_option_value_id)){
									$this->db->query("INSERT INTO ".DB_PREFIX."amazon_product_variation_map SET `product_id` = '".(int)$product_id."', `product_option_value_id` = '".(int)$product_option_value_id."', `option_value_id` = '".(int)$product_option_value['option_value_id']."', `id_type` = '".$this->db->escape($product_option_value['id_type'])."', `id_value` = '".$this->db->escape($product_option_value['id_value'])."', `sku` = '".$this->db->escape($product_option_value['sku'])."', `main_product_type` = '".$this->db->escape($data['amazonProductType'])."', `main_product_type_value` = '".$this->db->escape(isset($data['amazonProductTypeValue']) ? $data['amazonProductTypeValue'] : '')."' ");
							}

						}
					}
				}
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "' AND category_id = '".(int)$data['category_id']."' ");

		if (isset($data['category_id'])) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$data['category_id'] . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_image'])) {
			foreach ($data['product_image'] as $product_image) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape($product_image['image']) . "', sort_order = '0'");
			}
		}

		$this->cache->delete('product');

		return $product_id;
	}

	/**
	* to get amazon product specification/attribute
	*/
	public function getAmazonAttributes($data = array()) {
		$sql = "SELECT *, (SELECT agd.name FROM " . DB_PREFIX . "attribute_group_description agd WHERE agd.attribute_group_id = a.attribute_group_id AND agd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS attribute_group FROM " . DB_PREFIX . "amazon_attribute_map aam LEFT JOIN ".DB_PREFIX."attribute a ON((a.attribute_id = aam.oc_attribute_id) AND (a.attribute_group_id = aam.account_group_id)) LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE ad.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND ad.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_attribute_group_id'])) {
			$sql .= " AND a.attribute_group_id = '" . $this->db->escape($data['filter_attribute_group_id']) . "'";
		}

		$sort_data = array(
			'ad.name',
			'attribute_group',
			'a.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY attribute_group, ad.name";
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
	 * [__saveOpencartProductData assign amazon specification to opencart product]
	 * @param  boolean $product_id [description]
	 * @param  array   $data       [description]
	 * @return [type]              [description]
	 */
	public function __saveOpencartProductData($product_id = false, $data = array(), $type = 'add'){
		if($product_id){
			$this->load->model('localisation/language');
			$languages = $this->model_localisation_language->getLanguages();

			if((isset($data['amazonProductType']) && $data['amazonProductType']) && (isset($data['amazonProductTypeValue']) && $data['amazonProductTypeValue'])){
					$this->db->query("DELETE FROM ".DB_PREFIX."amazon_product_fields WHERE product_id = '".(int)$product_id."' ");

					$this->db->query("INSERT INTO ".DB_PREFIX."amazon_product_fields SET `product_id` = '".(int)$product_id."', `main_product_type` = '".strtoupper($data['amazonProductType'])."', `main_product_type_value` = '".trim($data['amazonProductTypeValue'])."' ");
			}

				if(isset($data['amazon_product_specification']) && $data['amazon_product_specification']){
						foreach ($data['amazon_product_specification'] as $product_attribute) {
								if ($product_attribute['attribute_id']) {
										$getAttributeEntry = $this->checkAttributeMapEntry(array('oc_attribute_id' => $product_attribute['attribute_id']));

										if (isset($getAttributeEntry) && $getAttributeEntry) {
												// Removes duplicates
													$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

													foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
															$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "' AND language_id = '" . (int)$language_id . "'");

															$this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" .  $this->db->escape($product_attribute_description['text']) . "'");
													}
										}
								}
					 }
				}

				$this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "'");
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "'");
				$this->db->query("DELETE FROM ".DB_PREFIX."amazon_product_variation_map WHERE product_id = '".(int)$product_id."' ");

				if (isset($data['amazon_product_variation_value'])) {
						foreach ($data['amazon_product_variation_value'] as $key => $product_option) {
								$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', required = '1'");

								$product_option_id = $this->db->getLastId();

								if (isset($product_option['option_value']) && !empty($product_option['option_value'])) {
										foreach ($product_option['option_value'] as $key1 => $product_option_value) {
												$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET `product_option_id` = '".(int)$product_option_id. "', `product_id` = '".(int)$product_id."', `option_id` = '".(int)$product_option['option_id']."', `option_value_id` = '".(int)$key1."', `quantity` = '".(int)$product_option_value['quantity']."', `subtract` = '1', `price` = '".(float)$product_option_value['price']."', `price_prefix` = '".$this->db->escape($product_option_value['price_prefix'])."', `points` = '0', `points_prefix` = '+', `weight` = '0', `weight_prefix` = '+'");

												$product_option_value_id = $this->db->getLastId();

												if(isset($product_option_value_id)){
														$this->db->query("INSERT INTO ".DB_PREFIX."amazon_product_variation_map SET `product_id` = '".(int)$product_id."', `product_option_value_id` = '".(int)$product_option_value_id."', `option_value_id` = '".(int)$key1."', `id_type` = '".$this->db->escape($product_option_value['id_type'])."', `id_value` = '".$this->db->escape($product_option_value['id_value'])."', `sku` = '".$this->db->escape($product_option_value['sku'])."', `main_product_type` = '".$this->db->escape($data['amazonProductType'])."', `main_product_type_value` = '".$this->db->escape(isset($data['amazonProductTypeValue']) ? $data['amazonProductTypeValue'] : '')."' ");
												}
										}
								}
						}
				}
		}

		if($type == 'edit'){
				$this->updateRealTimeProduct($product_id);
		}

		return true;
	}

	public function updateRealTimeProduct($product_id = false){
			$product_details = $product_array = array();
			if($product_id){
					$getMapEntry = $this->getProductMappedEntry(array('filter_oc_product_id' => $product_id));
					if(!empty($getMapEntry) && isset($getMapEntry[0]) && $getMapEntry[0]){
							$product_details = $getMapEntry[0];

							if($product_details['sync_source'] == 'Amazon Item' && $this->config->get('wk_amazon_connector_import_update') != 'on'){
									return true;
							}

							if($product_details['sync_source'] == 'Opencart Item' && $this->config->get('wk_amazon_connector_export_update') != 'on'){
									return true;
							}

							$UpdateQuantityArray = $UpdatePriceArray = $DeleteProductArray = array();

							if(isset($product_details['account_id'])){
									$accountDetails = $this->Amazonconnector->getAccountDetails(array('account_id' => $product_details['account_id']));

									if(isset($accountDetails['wk_amazon_connector_marketplace_id']) && $accountDetails['wk_amazon_connector_marketplace_id']){

											if ((isset($product_details['main_product_type']) && $product_details['main_product_type']) && (isset($product_details['main_product_type_value']) && $product_details['main_product_type_value'])) {

													if($getCombinations = $this->Amazonconnector->_getProductVariation($product_details['oc_product_id'], $type = 'amazon_product_variation_value')){
															foreach ($getCombinations as $option_id => $combination_array) {
																	 $total_combinations = count($combination_array);
																	 foreach ($combination_array['option_value'] as $key => $combination_value) {
																			 $product_data = array();

																				if(isset($combination_value['price_prefix']) && $combination_value['price_prefix'] == '+'){
																						$product_data['price'] = (float)$product_details['price'] + (float)$combination_value['price'];
																				}else{
																						$product_data['price'] = (float)$product_details['price'] - (float)$combination_value['price'];
																				}

																				if(isset($combination_value['quantity']) && $combination_value['quantity']){
																						$product_data['quantity'] = $combination_value['quantity'];
																				}else{
																						$product_data['quantity'] = ($this->config->get('wk_amazon_connector_default_quantity') / $total_combinations);
																				}
																				$product_data['sku'] 				 = $combination_value['sku'];

																				//Update qty of amazon product
																				$UpdateQuantityArray[] = array(
																																	'sku' => $product_data['sku'],
																																	'qty' => $product_data['quantity'],
																																);

																				//Update price of amazon product
																				$UpdatePriceArray[] = array(
																															 'sku' 							=> $product_data['sku'],
																															 'currency_symbol' 	=> $accountDetails['wk_amazon_connector_currency_code'],
																															 'price' 						=> (float)$product_data['price'] * $accountDetails['wk_amazon_connector_currency_rate'],
																														 );
																		}
															 }
															 //final data of submit feed data
	 														$product_array[] = array('product_id'  => $product_details['oc_product_id'],
	 																											'account_id' => $accountDetails['id'],
	 																											'name' 			 => $product_details['product_name'],
	 																											'id_value'	 => $product_details['main_product_type_value'],
	 																								);

													}else{ // if not combination
															$product_data = array();
															$product_data['product_id'] 		= $product_details['oc_product_id'];
															$product_data['account_id'] 		= $accountDetails['id'];
															$product_data['name'] 					= $product_details['product_name'];
															$product_data['id_value'] 			= $product_details['main_product_type_value'];
															$product_data['price'] 					= (float)$product_details['price'];
															$product_data['quantity'] 			= (!empty($product_details['quantity']) ? $product_details['quantity'] : $this->config->get('wk_amazon_connector_default_quantity'));
															$product_data['sku'] 						= (!empty($product_details['sku']) ? $product_details['sku'] : 'oc_prod_'.$product_details['oc_product_id']);

															//Update qty of amazon product
															$UpdateQuantityArray[] = array(
																												'sku' => $product_data['sku'],
																												'qty' => $product_data['quantity'],
																											);

															//Update price of amazon product
															$UpdatePriceArray[] = array(
																										 'sku' 							=> $product_data['sku'],
																										 'currency_symbol' 	=> $accountDetails['wk_amazon_connector_currency_code'],
																										 'price' 						=> (float)$product_data['price'] * $accountDetails['wk_amazon_connector_currency_rate'],
																									 );
														$product_array[] = $product_data;
													}
											}

											if(!empty($product_array)){
													$this->Amazonconnector->product['ActionType']  = 'UpdateQuantity';
													$this->Amazonconnector->product['ProductData'] = $UpdateQuantityArray;


													$product_updated = $this->Amazonconnector->submitFeed('_POST_INVENTORY_AVAILABILITY_DATA_', $accountDetails['id']);

													if (isset($product_updated['success']) && $product_updated['success']) {
															$this->Amazonconnector->product['ActionType']  = 'UpdatePrice';
															$this->Amazonconnector->product['ProductData'] = $UpdatePriceArray;

															$this->Amazonconnector->submitFeed('_POST_PRODUCT_PRICING_DATA_', $accountDetails['id']);
													}
											}
									}
							}
					}
			}
	}

	public function getOcProductWithCombination($data = array()){
		$product_array = array();
		 $sql = "SELECT p.*, pd.name, pd.description, apf.main_product_type, apf.main_product_type_value FROM ".DB_PREFIX."product p LEFT JOIN ".DB_PREFIX."product_description pd ON (p.product_id = pd.product_id) LEFT JOIN ".DB_PREFIX."amazon_product_fields apf ON(p.product_id = apf.product_id) WHERE pd.language_id = '".(int)$this->config->get('config_language_id')."' AND p.status = '1' AND p.product_id NOT IN (SELECT oc_product_id FROM ".DB_PREFIX."amazon_product_map) ";

		 if(!empty($data['product_ids'])){
			 $sql .= " AND p.product_id IN (".$data['product_ids'].")";
		 }

		 foreach ($this->db->query($sql)->rows as $key => $product) {
			 $combinationArray = $getCombinations = $specificationArray = array();

			 if($getCombinations = $this->Amazonconnector->_getProductVariation($product['product_id'], $type = 'amazon_product_variation_value')){
				 foreach ($getCombinations as $option_id => $combination_array) {
					 foreach ($combination_array['option_value'] as $key => $combination_value) {
					 		$combinationArray[$option_id][$key] = $combination_value;
					 }
				 }
			 }

			 $specificationArray = $this->Amazonconnector->getProductSpecification($product['product_id']);

			 $product_array[] = array(
					 'product_id' => $product['product_id'],
					 'name' 			=> $product['name'],
					 'model' 			=> $product['model'],
					 'description'=> $product['description'],
					 'sku' 				=> $product['sku'],
					 'quantity'   => $product['quantity'],
					 'price'   		=> $product['price'],
					 'main_type'  => $product['main_product_type'],
					 'main_value' => $product['main_product_type_value'],
					 'combinations'   => $combinationArray,
					 'specifications' => $specificationArray,
			 );
		 }

		 return $product_array;
	}

	public function export_to_amazon($data = array(), $accountId){
		$result = $getProductArray = array();

			if(isset($data['product_export_option']) && $data['product_export_option'] == 'all'){
					$getProductArray 	= $this->getOcProductWithCombination();
			}else if(isset($data['product_export_option']) && $data['product_export_option'] == 'selected' && isset($data['product_combitaion'])){
				$product_string 		= '';
				foreach ($data['product_combitaion'] as $product_id) {
					$product_string  .= "'$product_id',";
				}
				$product_string 		= trim($product_string,',');
				$getProductArray 		= $this->getOcProductWithCombination(array('product_ids' => $product_string));
			}

			if(!empty($getProductArray)){
					$result = $this->startExportProcess($getProductArray, $accountId);
			}else{
					$result = array('status' 			=> false,
													'message' 		=> $this->language->get('error_no_product_found'),
													'total_sync'  => array());
			}

			return $result;
	}

	public function startExportProcess($data = array(), $accountId)
	{
			if(!empty($data)){
					$final_product_data = $UpdateQuantityArray = $addProductArray = $notSyncIds = array();
					$accountDetails = $this->Amazonconnector->getAccountDetails(array('account_id' =>$accountId));

					if(isset($accountDetails['wk_amazon_connector_marketplace_id']) && $accountDetails['wk_amazon_connector_marketplace_id']){

							foreach ($data as $key => $product) {
									if ((isset($product['main_type']) && $product['main_type']) && (isset($product['main_value']) && $product['main_value'])) {

											if(isset($product['combinations']) && !empty($product['combinations'])){

													foreach ($product['combinations'] as $option_id => $option_value_array) {
														$total_combinations = count($option_value_array);
															foreach ($option_value_array as $option_value_id => $option_value_data) {
																	$product_data = array();
																	if(isset($option_value_data['price_prefix']) && $option_value_data['price_prefix'] == '+'){
																			$product_data['price'] = (float)$product['price'] + (float)$option_value_data['price'];
																	}else{
																			$product_data['price'] = (float)$product['price'] - (float)$option_value_data['price'];
																	}
																	if(isset($option_value_data['quantity']) && $option_value_data['quantity']){
																			$product_data['quantity'] = $option_value_data['quantity'];
																	}else{
																			$product_data['quantity'] = ($this->config->get('wk_amazon_connector_default_quantity') / $total_combinations);
																	}
																	$product_data['product_id']= $product['product_id'];
																	$product_data['name'] 		 = $option_value_data['name'];
																	$product_data['id_type'] 	 = $option_value_data['id_type'];
																	$product_data['id_value']  = $option_value_data['id_value'];
																	$product_data['sku'] 			 = $option_value_data['sku'];
																	$product_data['description']			 = '';

																	//Add new product on Amazon store
					                        $addProductArray[] = array(
					                                                'sku' 										=> $product_data['sku'],
																													'exportProductType' 			=> $product_data['id_type'],
																					                'exportProductTypeValue' 	=> $product_data['id_value'],
					                                                'name' 										=> $product_data['name'],
					                                                'description' 						=> strip_tags(htmlspecialchars_decode($product_data['description'])),
					                                        			);

																	//Update qty of amazon product
																	$UpdateQuantityArray[] = array(
																														'sku' => $product_data['sku'],
																														'qty' => $product_data['quantity'],
																													);

																	//Update price of amazon product
						 											$UpdatePriceArray[] = array(
						 																						 'sku' 							=> $product_data['sku'],
																												 'currency_symbol' 	=> $accountDetails['wk_amazon_connector_currency_code'],
																												 'price' 						=> (float)$product_data['price'] * $accountDetails['wk_amazon_connector_currency_rate'],
						 																					 );
															}
													} // combination foreach loop
												//final data of submit feed data
												$final_product_data[] = array('product_id' => $product['product_id'],
																											'account_id' => $accountId,
																											'name' 			 => $product['name'],
																											'category_id' => $this->config->get('wk_amazon_connector_default_category'),
																											'id_type'			=> $product['main_type'],
																											'id_value'		=> $product['main_value'],
																											'sku'					=> (!empty($product['sku']) ? $product['sku'] : 'oc_prod_'.$product['product_id']),

												);
											}else{
																	$product_data = array();
																	$product_data['product_id']= $product['product_id'];
																	$product_data['price'] 		= (float)$product['price'];
																	$product_data['quantity'] = (!empty($product['quantity']) ? $product['quantity'] : $this->config->get('wk_amazon_connector_default_quantity'));
																	$product_data['name'] 		= $product['name'];
																	$product_data['id_type'] 	= $product['main_type'];
																	$product_data['id_value'] = $product['main_value'];
																	$product_data['sku'] 			= (!empty($product['sku']) ? $product['sku'] : 'oc_prod_'.$product['product_id']);
																	$product_data['description'] = strip_tags(htmlspecialchars_decode($product['description']));
																	$product_data['account_id'] = $accountId;
																	$product_data['category_id'] = $this->config->get('wk_amazon_connector_default_category');

																	//Add new product on Amazon store
																	$addProductArray[] = array(
																													'sku' 										=> $product_data['sku'],
																													'exportProductType' 			=> $product_data['id_type'],
																													'exportProductTypeValue' 	=> $product_data['id_value'],
																													'name' 										=> $product_data['name'],
																													'description' 						=> strip_tags(htmlspecialchars_decode($product_data['description'])),
																											);

																	//Update qty of amazon product
																	$UpdateQuantityArray[] = array(
																															'sku' => $product_data['sku'],
																															'qty' => $product_data['quantity'],
																													);

																	//Update price of amazon product
																 $UpdatePriceArray[] = array(
																													 'sku' 							=> $product_data['sku'],
																													 'currency_symbol' 	=> $accountDetails['wk_amazon_connector_currency_code'],
																													 'price' 						=> (float)$product_data['price'] * $accountDetails['wk_amazon_connector_currency_rate'],
																												 );
																 //final data of submit feed data
																	$final_product_data[] = $product_data;
											} // combination if condition
									} else {
											$notSyncIds[$product['product_id']] = array('product_id' => $product['product_id'], 'name' => $product['name'], 'message' => 'Warning: ASIN Number is missing for opencart product : <b>'.$product['name'].'</b> !');
									}
							} // Product foreach loop

							if (!empty($final_product_data)) {
									$this->Amazonconnector->product['ActionType'] 	= 'AddProduct';
									$this->Amazonconnector->product['ProductData'] 	= $addProductArray;
									$product_created = $this->Amazonconnector->submitFeed($feedType = '_POST_PRODUCT_DATA_', $accountId);

									if (isset($product_created['success']) && $product_created['success']) {
											$this->Amazonconnector->product['ActionType'] 	= 'UpdateQuantity';
											$this->Amazonconnector->product['ProductData'] 	= $UpdateQuantityArray;
											$this->Amazonconnector->submitFeed($feedType 		= '_POST_INVENTORY_AVAILABILITY_DATA_', $accountId);

											$this->Amazonconnector->product['ActionType'] = 'UpdatePrice';
											$this->Amazonconnector->product['ProductData'] = $UpdatePriceArray;
											$this->Amazonconnector->submitFeed($feedType = '_POST_PRODUCT_PRICING_DATA_', $accountId);

											foreach ($final_product_data as $product_details) {
													$product_details['feed_id'] = $product_created['feedSubmissionId'];
													$this->saveExportMapEntry($product_details);
											}

											$result_data = array('status' 					=> true,
																					'feedSubmissionId' 	=> $product_created['feedSubmissionId'],
																					'total_sync' 				=> $final_product_data,
																					'notSyncIds' 				=> $notSyncIds,
																			);
									} else {
											if (isset($product_created['comment']) && $product_created['comment']) {
													$result_data = array('status' 	=> false,
																								'message' => $product_created['comment'],
																								'total_sync'  => array());
											} else {
													$result_data = array('status' 	=> false,
																							 'message' 	=> $this->language->get('error_occurs'),
																						 	 'total_sync'  => array());
											}
									}
							} else {
									$result_data = array('status' 			=> false,
																			'message' 			=> $this->language->get('error_no_product_found'),
																			'total_sync'  	=> array(),
																			'notSyncIds' 		=> $notSyncIds,);
							}
					}else{
						$result_data = array('status' 			=> false,
																	'message' 		=> $this->language->get('error_account_not_exist'),
																	'total_sync'  => array());
					}
			}else{
				$result_data = array('status' 			=> false,
															'message' 		=> $this->language->get('error_no_product_found'),
															'total_sync'  => array());
			}
		return $result_data;
	}

		public function saveExportMapEntry($product_details = array()){
				if(isset($product_details['feed_id']) && $product_details['feed_id'] && isset($product_details['account_id'])){
						$this->db->query("INSERT INTO " . DB_PREFIX . "amazon_product_map SET oc_product_id = '" . (int)$product_details['product_id'] . "', amazon_product_id = '" . $product_details['id_value'] . "', `amazon_product_sku` = '".$this->db->escape($product_details['sku'])."', oc_category_id = '" . (int)$product_details['category_id'] . "', `amazon_image` = '',  account_id = '".(int)$product_details['account_id']."', `sync_source` = 'Opencart Item', added_date = NOW() ");
				}
		}

		public function deleteProductMapEntry($data = array()){
				if((isset($data['product_id']) && $data['product_id']) && (isset($data['id_value']) && $data['id_value']) && (isset($data['account_id']) && $data['account_id']) ){
						$this->db->query("DELETE FROM ".DB_PREFIX."amazon_product_map WHERE oc_product_id = '".(int)$data['product_id']."' AND account_id = '".(int)$data['account_id']."' ");
				}
				return true;
		}
}
