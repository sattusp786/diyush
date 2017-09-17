<?php
/**
 * @version [2.3.x.x] [Supported opencart version 2.3.x.x]
 * @category Webkul
 * @package Opencart-Amazon Connector
 * @author [Webkul] <[<http://webkul.com/>]>
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
class ControllerAmazonMapProduct extends Controller {
	private $error = array();

	public function __construct($registory) {
		parent::__construct($registory);
		$this->load->model('amazon_map/product');
		$this->_amazonMapProduct = $this->model_amazon_map_product;
    }

    public function index() {
		$data = array();
		$data = array_merge($data, $this->load->language('amazon_map/product'));

		$this->document->addScript('view/javascript/amazon_connector/webkul_amazon_connector.js');

		if(isset($this->request->get['account_id'])) {
			$data['account_id'] = $account_id = $this->request->get['account_id'];
		}else{
			$data['account_id'] = $account_id = 0;
		}

		if(isset($this->request->get['tab'])) {
			$data['tab'] = $this->request->get['tab'];
		}else{
			$data['tab'] = false;
		}

		if (isset($this->request->get['sort']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_product_map')) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'id';
		}

		if (isset($this->request->get['order']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_product_map')) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_product_map')) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		if(isset($this->session->data['product_delete_result'])){
				$data['product_delete_result'] = $this->session->data['product_delete_result'];

				unset($this->session->data['product_delete_result']);
		}else{
				$data['product_delete_result'] = array();
		}

		$url = '';

		$url .= '&status=account_product_map';

		if (isset($this->request->get['account_id'])) {
			$url .= '&account_id=' . $this->request->get['account_id'];
		}

		if (isset($this->request->get['sort']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_product_map')) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_product_map')) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_product_map')) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['import_from_ebay'] 	= $this->url->link('amazon_map/product', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] 			= $this->url->link('amazon_map/product/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['import_product_tab'] = $this->url->link('amazon_map/account/edit', 'user_token=' . $this->session->data['user_token']. '&tab=import_product'. $url, true);
		$data['export_product_tab'] = $this->url->link('amazon_map/account/edit', 'user_token=' . $this->session->data['user_token'] . '&tab=export_product'. $url, true);

		$data['import_products'] = array();

		$filter_data = array(
			'account_id' => $account_id,
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$getGlobalOption = $this->Amazonconnector->__getOcAmazonGlobalOption();

		$amazonProductTotal = $this->_amazonMapProduct->getTotalProductMappedEntry($filter_data);

		$results = $this->_amazonMapProduct->getProductMappedEntry($filter_data);

		if($results){
			foreach ($results as $result) {
				/**
				* Get product Variations
				*/
				$option_values 				= array();
				$result['option_id'] 	= $getGlobalOption['option_id'];
				$product_option 			= $this->_amazonMapProduct->getProductOptions($result);

				if(!empty($product_option)){
					foreach ($product_option as $key => $opt_value) {
						$option_values[] 	= array(
							'name' => $opt_value['value_name'],
							'asin' => $opt_value['id_value'],
						);
					}
				}

				$data['import_products'][] = array(
					'map_id' 					=> $result['map_id'],
					'oc_product_id' 	=> $result['oc_product_id'],
					'amazon_product_asin' => $result['amazon_product_id'],
					'option_values'		=> $option_values,
					'product_name'	 	=> $result['product_name'],
					'price'						=> $result['price'],
					'quantity'				=> $result['quantity'],
					'source'					=> $result['sync_source'],
				);
			}
		}

		$data['productCombinations'] = $this->_amazonMapProduct->getOcProductWithCombination();

		$exportProductArray = $this->_amazonMapProduct->getProductMappedEntry(array('sync_source' => 'Opencart Item'));

		if(!empty($exportProductArray)){
				foreach ($exportProductArray as $key => $product) {
					  $combinations = array();
						if($getCombinations = $this->Amazonconnector->_getProductVariation($product['oc_product_id'], $type = 'amazon_product_variation_value')){
								foreach ($getCombinations as $option_id => $combination_array) {
										 foreach ($combination_array['option_value'] as $key1 => $combination_value) {
											 		$exportProductArray[$key]['combinations'][] = array('name' => $combination_value['name'], 'id_value' => $combination_value['id_value']);
										 }
								 }
						}
				}
		}

		$data['updateproductData'] = $exportProductArray;

		$data['user_token'] 	= $this->session->data['user_token'];

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

		$url .= '&status=account_product_map';

		if (isset($this->request->get['account_id'])) {
			$url .= '&account_id=' . $this->request->get['account_id'];
		}

		$data['button_back_link'] = $this->url->link('amazon_map/account/edit', 'user_token=' . $this->session->data['user_token'] .$url, true);

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_product_map')) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_map_id'] = $this->url->link('amazon_map/account/edit', 'user_token=' . $this->session->data['user_token'] . '&sort=id' . $url, true);
		$data['sort_oc_product_id'] = $this->url->link('amazon_map/account/edit', 'user_token=' . $this->session->data['user_token'] . '&sort=apm.oc_product_id' . $url, true);
		$data['sort_oc_name'] = $this->url->link('amazon_map/account/edit', 'user_token=' . $this->session->data['user_token'] . '&sort=product_name' . $url, true);
		$data['sort_oc_price'] = $this->url->link('amazon_map/account/edit', 'user_token=' . $this->session->data['user_token'] . '&sort=p.price' . $url, true);
		$data['sort_oc_quantity'] = $this->url->link('amazon_map/account/edit', 'user_token=' . $this->session->data['user_token'] . '&sort=p.quantity' . $url, true);

		$url = '';

		$url .= '&status=account_product_map';

		if (isset($this->request->get['account_id'])) {
			$url .= '&account_id=' . $this->request->get['account_id'];
		}

		if (isset($this->request->get['sort']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_product_map')) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_product_map')) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$data['action_product'] = $this->url->link('amazon_map/product/deleteMapProduct', 'user_token=' . $this->session->data['user_token'] .$url, true);

		$pagination = new Pagination();
		$pagination->total = $amazonProductTotal;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('amazon_map/account/edit', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($amazonProductTotal) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($amazonProductTotal - $this->config->get('config_limit_admin'))) ? $amazonProductTotal : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $amazonProductTotal, ceil($amazonProductTotal / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;
		
		return $this->load->view('amazon_map/product', $data);
	}

	public function deleteMapProduct() {
		$result = array();
		$this->load->language('amazon_map/product');
		unset($this->session->data['product_delete_result']);
		if (isset($this->request->post['selected']) && $this->validateDelete()) {
				foreach ($this->request->post['selected'] as $map_id) {
					$result[] = $this->_amazonMapProduct->deleteMapProducts($map_id, $this->request->get['account_id']);
				}

				if(!empty($result)){
					$this->session->data['product_delete_result'] = $result;
				}
		}
		$url = '';

		$url .= '&status=account_product_map';

		if (isset($this->request->get['account_id'])) {
			$url .= '&account_id=' . $this->request->get['account_id'];
		}

		if (isset($this->request->get['sort']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_product_map')) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_product_map')) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$this->response->redirect($this->url->link('amazon_map/account/edit', 'user_token=' . $this->session->data['user_token'] . $url, true));
	}

	public function generate_report_id() {
		$json = array();
		$this->load->language('amazon_map/product');

		if (isset($this->request->get['account_id']) && $this->request->get['account_id']) {
			$getAccountEntry = $this->Amazonconnector->getAccountDetails(array('account_id' => $this->request->get['account_id']));

			if(isset($getAccountEntry) && !empty($getAccountEntry)){
				$result =  $this->_amazonMapProduct->updateAccountReportEntry($this->request->get['account_id']);
				if(isset($result['status']) && $result['status']){
					if(isset($this->request->get['status']) && $this->request->get['status'] == 'order'){
							$json['success'] = array('message' => sprintf($this->language->get('success_report_order_added'), $result['report_id']), 'report_id' => $result['report_id']);
					}else{
							$json['success'] = array('message' => $result['message'], 'report_id' => $result['report_id']);
					}
				}else{
					$json['error'] = $result['message'];
				}
			}else{
				$json['error'] = $this->language->get('error_account_not_exist');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}


	public function import_amazon_product(){
		$result = $json = array();

		$this->load->language('amazon_map/product');

		if((isset($this->request->get['account_id']) && $this->request->get['account_id']) && (isset($this->request->get['report_id']) && $this->request->get['report_id'])){
					$result = $this->_amazonMapProduct->import_AmazonProduct($this->request->get);
		}else if((isset($this->request->get['account_id']) && $this->request->get['account_id']) && (isset($this->request->get['product_asin']) && $this->request->get['product_asin'])){
					$result = $this->_amazonMapProduct->import_AmazonProduct($this->request->get);
		}

		if(isset($result['status']) && $result['status'] && !empty($result)){
				if(isset($result['data']['success']) && !empty($result['data']['success'])){
					$json['success'] = $result['data']['success'];
				}
				if(isset($result['data']['error']) && !empty($result['data']['error'])){
					$json['error'] = $result['data']['error'];
				}
		}else{
				$json['error'] = [$result['message']];
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function validateDelete() {
		if (!$this->user->hasPermission('modify', 'amazon_map/product')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function attributeAutocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'start'       => 0,
				'limit'       => 5
			);

			$results = $this->_amazonMapProduct->getAmazonAttributes($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'attribute_id'    => $result['attribute_id'],
					'name'            => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'attribute_group' => $result['attribute_group']
				);
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	  public function export_product($data = array())
	  {
				$json = array();
				$this->load->language('amazon_map/product');

				if(isset($this->request->get['account_id']) && isset($this->request->post['product_export_option']) && $this->request->post['product_export_option']){
						$results = $this->_amazonMapProduct->export_to_amazon($this->request->post, $this->request->get['account_id']);

						if($results['status'] && isset($results['feedSubmissionId']) && $results['feedSubmissionId']){
								foreach ($results['total_sync'] as $key => $product_data) {
									$json['success'][$key] = sprintf($this->language->get('success_export_to_amazon'), $product_data['name'].', Id : '.$product_data['product_id']). $product_data['id_value'];
								}
								if(!empty($results['notSyncIds']) && isset($results['notSyncIds'])){
										foreach ($results['notSyncIds'] as $key1 => $product_not_sync) {
												$json['error'][$key1] = sprintf($this->language->get('error_export_to_amazon'), $product_not_sync['name'].', Id : '.$product_not_sync['product_id']);
										}
								}
						}else{
								if(isset($results['notSyncIds']) && !empty($results['notSyncIds'])){
										foreach ($results['notSyncIds'] as $key_error => $product_warning) {
												$json['error'] = [$product_warning['message']];
										}
								}else{
										$json['error'] = [$results['message']];
								}
						}
				}else{
						$json['error'] = [$this->language->get('error_wrong_selection')];
				}

			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
	  }


		public function opration_export_product($data = array()){
			$json = $product_array = $exportSelectedArray = $exportProductArray = array();
			$this->load->language('amazon_map/product');

				if(isset($this->request->get['account_id']) && isset($this->request->post['product_update_delete_option']) && $this->request->post['product_update_delete_option']){

							$accountDetails = $this->Amazonconnector->getAccountDetails(array('account_id' => $this->request->get['account_id']));

							if($this->request->post['product_update_delete_option'] == 'all'){
											$exportProductArray = $this->_amazonMapProduct->getProductMappedEntry(array('sync_source' => 'Opencart Item'));
							}else if($this->request->post['product_update_delete_option'] == 'selected'  && isset($this->request->post['product_selected_export'])){
									foreach ($this->request->post['product_selected_export'] as $key => $product_id) {
											$exportSelectedArray = $this->_amazonMapProduct->getProductMappedEntry(array('sync_source' => 'Opencart Item', 'filter_oc_product_id' => $product_id));
											if(!empty($exportSelectedArray) && isset($exportSelectedArray[0]) && $exportSelectedArray[0]){
													array_push($exportProductArray, $exportSelectedArray[0]);
											}
									}
							}else{
									$exportProductArray = array();
							}

						 if(!empty($exportProductArray)){
							 	$UpdateQuantityArray = $UpdatePriceArray = $DeleteProductArray = array();

								foreach ($exportProductArray as $key => $product) {
										if ((isset($product['main_product_type']) && $product['main_product_type']) && (isset($product['main_product_type_value']) && $product['main_product_type_value'])) {
												if($getCombinations = $this->Amazonconnector->_getProductVariation($product['oc_product_id'], $type = 'amazon_product_variation_value')){

													 foreach ($getCombinations as $option_id => $combination_array) {
														 	$total_combinations = count($combination_array);
														 	foreach ($combination_array['option_value'] as $key => $combination_value) {
																	$product_data = array();

																	 if(isset($combination_value['price_prefix']) && $combination_value['price_prefix'] == '+'){
																			 $product_data['price'] = (float)$product['price'] + (float)$combination_value['price'];
																	 }else{
																			 $product_data['price'] = (float)$product['price'] - (float)$combination_value['price'];
																	 }

																	 if(isset($combination_value['quantity']) && $combination_value['quantity']){
																			 $product_data['quantity'] = $combination_value['quantity'];
																	 }else{
																			 $product_data['quantity'] = ($this->config->get('wk_amazon_connector_default_quantity') / $total_combinations);
																	 }
																	 $product_data['sku'] 				 = $combination_value['sku'];

																	 if(isset($this->request->post['export_option']) && $this->request->post['export_option'] == 'update'){
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
																	 }else if(isset($this->request->post['export_option']) && $this->request->post['export_option'] == 'delete'){
																			 //delete amazon product
					                             $DeleteProductArray[] = array(
					                                                         'sku' => $product_data['sku'],
					                                                     );
																	 }
														 	 }
													  }
														//final data of submit feed data
														$product_array[] = array('product_id' => $product['oc_product_id'],
																											'account_id' => $this->request->get['account_id'],
																											'name' 			 => $product['product_name'],
																											'id_value'	 => $product['main_product_type_value'],
																								);
												 }else{
													 		$product_data = array();
															$product_data['product_id'] 		= $product['oc_product_id'];
															$product_data['account_id'] 		= $this->request->get['account_id'];
															$product_data['name'] 					= $product['product_name'];
															$product_data['id_value'] 			= $product['main_product_type_value'];
													 		$product_data['price'] 					= (float)$product['price'];
															$product_data['quantity'] 			= (!empty($product['quantity']) ? $product['quantity'] : $this->config->get('wk_amazon_connector_default_quantity'));
															$product_data['sku'] 						= (!empty($product['sku']) ? $product['sku'] : 'oc_prod_'.$product['product_id']);

															if(isset($this->request->post['export_option']) && $this->request->post['export_option'] == 'update'){
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
															}else if(isset($this->request->post['export_option']) && $this->request->post['export_option'] == 'delete'){
																	//delete amazon product
																	$DeleteProductArray[] = array(
																															'sku' => $product_data['sku'],
																													);
															}
															//final data of submit feed data
															$product_array[] = $product_data;
												}// end else no combination found
										} else {
												$json['error'][] = sprintf($this->language->get('error_update_export_to_amazon'), $product['product_name'].', Id : '.$product['oc_product_id']);
										}
								} // product loop ending

								if(!empty($product_array)){
										if ($this->request->post['export_option'] == 'update') {
												$this->Amazonconnector->product['ActionType']  = 'UpdateQuantity';
												$this->Amazonconnector->product['ProductData'] = $UpdateQuantityArray;

												$feedType = '_POST_INVENTORY_AVAILABILITY_DATA_';

										} else if ($this->request->post['export_option'] == 'delete') {
												$this->Amazonconnector->product['ActionType']  = 'DeleteProduct';
												$this->Amazonconnector->product['ProductData'] = $DeleteProductArray;

												$feedType = '_POST_PRODUCT_DATA_';
										}

										$product_updated = $this->Amazonconnector->submitFeed($feedType, $this->request->get['account_id']);

										if (isset($product_updated['success']) && $product_updated['success']) {
												if ($this->request->post['export_option'] == 'update') {
														$this->Amazonconnector->product['ActionType']  = 'UpdatePrice';
														$this->Amazonconnector->product['ProductData'] = $UpdatePriceArray;

														$this->Amazonconnector->submitFeed($feedType = '_POST_PRODUCT_PRICING_DATA_', $this->request->get['account_id']);

														foreach ($product_array as $product_details) {
																$json['success'][] = sprintf($this->language->get('success_update_export_to_amazon'), $product_details['name'].', Id : '.$product_details['product_id']). $product_details['id_value'];
														}

												} else if ($this->request->post['export_option'] == 'delete') {
														foreach ($product_array as $product_details) {
																$this->_amazonMapProduct->deleteProductMapEntry($product_details);
																$json['success'][] = sprintf($this->language->get('success_delete_export_to_amazon'), $product_details['name'].', Id : '.$product_details['product_id']);
														}
												}
										} else {
												$json['error'][] = $this->language->get('error_occurs');
										}
								}else{
										$json['error'][] = $this->language->get('error_no_product_found');
								}
						}else{ // if exported product(s) found to update/delete
								$json['error'][] = $this->language->get('error_no_product_found');
						}
				}else{
						$json['error'] = [$this->language->get('error_wrong_selection')];
				}

			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		}
}
