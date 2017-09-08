<?php
/**
 * @version [2.3.x.x] [Supported opencart version 2.3.x.x]
 * @category Webkul
 * @package Opencart-Amazon Connector
 * @author [Webkul] <[<http://webkul.com/>]>
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
class ControllerAmazonMapOrder extends Controller {
	private $error = array();

	public function __construct($registory) {
		parent::__construct($registory);
		$this->load->model('amazon_map/order');
		$this->_amazonMapOrder = $this->model_amazon_map_order;
    }

    public function index() {
		$data = array();
		$data = array_merge($data, $this->load->language('amazon_map/order'));

		$this->document->addScript('view/javascript/amazon_connector/webkul_amazon_connector.js');

		if(isset($this->request->get['account_id'])) {
			$data['account_id'] = $account_id = $this->request->get['account_id'];
		}else{
			$data['account_id'] = $account_id = 0;
		}

		if(isset($this->request->get['panel'])) {
			$data['panel'] = $this->request->get['panel'];
		}else{
			$data['panel'] = false;
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

		if(isset($this->session->data['order_delete_result'])){
				$data['order_delete_result'] = $this->session->data['order_delete_result'];

				unset($this->session->data['order_delete_result']);
		}else{
				$data['order_delete_result'] = array();
		}

		$url = '';

		$url .= '&status=account_order_map';

		if (isset($this->request->get['account_id'])) {
			$url .= '&account_id=' . $this->request->get['account_id'];
		}

		if (isset($this->request->get['sort']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_order_map')) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_order_map')) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_order_map')) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['import_from_ebay'] 	= $this->url->link('amazon_map/order', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['import_order_tab'] = $this->url->link('amazon_map/account/edit', 'user_token=' . $this->session->data['user_token']. '&panel=import_order'. $url, true);

		$data['import_order'] = array();

		$filter_data = array(
			'account_id' => $account_id,
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$getAccountEntry = $this->Amazonconnector->getAccountDetails(array('account_id' => $account_id));

		$amazonOrderTotal = $this->_amazonMapOrder->getTotalOcAmazonOrderMap($filter_data);
		$results 					= $this->_amazonMapOrder->getOcAmazonOrderMap($filter_data);

		if($results){
			foreach ($results as $result) {
				/**
				* Get product Variations
				*/
				$ordered_product 				= array();
				// $result['option_id'] 	= $getGlobalOption['option_id'];
				// $product_option 			= $this->_amazonMapProduct->getProductOptions($result);
				//
				// if(!empty($product_option)){
				// 	foreach ($product_option as $key => $opt_value) {
				// 		$ordered_product[] 	= array(
				// 			'name' => $opt_value['value_name'],
				// 			'asin' => $opt_value['id_value'],
				// 		);
				// 	}
				// }

				$data['import_orders'][] = array(
					'map_id' 							=> $result['map_id'],
					'oc_order_id' 				=> $result['oc_order_id'],
					'amazon_order_id' 		=> $result['amazon_order_id'],
					'ordered_products'		=> $ordered_product,
					'customer_name'	 			=> $result['firstname'].' '.$result['lastname'],
					'customer_email'	 		=> $result['email'],
					// 'total'	 							=> $this->currency->format($result['total'] * $getAccountEntry['wk_amazon_connector_currency_rate'], $getAccountEntry['wk_amazon_connector_currency_code']),
					'total'	 							=> $result['total'],
					'amazon_order_status' => $result['amazon_order_status'],
					'view'								=> $this->url->link('sale/order/info', 'user_token=' . $this->session->data['user_token'] . '&order_id='.$result['oc_order_id'], true),
				);
			}
		}

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

		$url .= '&status=account_order_map';

		if (isset($this->request->get['account_id'])) {
			$url .= '&account_id=' . $this->request->get['account_id'];
		}

		$data['button_back_link'] = $this->url->link('amazon_map/account/edit', 'user_token=' . $this->session->data['user_token'] .$url, true);

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_order_map')) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_map_id'] = $this->url->link('amazon_map/account/edit', 'user_token=' . $this->session->data['user_token'] . '&sort=id' . $url, true);
		$data['sort_oc_product_id'] = $this->url->link('amazon_map/account/edit', 'user_token=' . $this->session->data['user_token'] . '&sort=apm.oc_product_id' . $url, true);
		$data['sort_oc_name'] = $this->url->link('amazon_map/account/edit', 'user_token=' . $this->session->data['user_token'] . '&sort=product_name' . $url, true);
		$data['sort_oc_price'] = $this->url->link('amazon_map/account/edit', 'user_token=' . $this->session->data['user_token'] . '&sort=p.price' . $url, true);
		$data['sort_oc_quantity'] = $this->url->link('amazon_map/account/edit', 'user_token=' . $this->session->data['user_token'] . '&sort=p.quantity' . $url, true);

		$url = '';

		$url .= '&status=account_order_map';

		if (isset($this->request->get['account_id'])) {
			$url .= '&account_id=' . $this->request->get['account_id'];
		}

		if (isset($this->request->get['sort']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_order_map')) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_order_map')) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$data['action_delete'] = $this->url->link('amazon_map/order/deleteMapOrder', 'user_token=' . $this->session->data['user_token'] .$url, true);

		$pagination = new Pagination();
		$pagination->total = $amazonOrderTotal;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('amazon_map/account/edit', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($amazonOrderTotal) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($amazonOrderTotal - $this->config->get('config_limit_admin'))) ? $amazonOrderTotal : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $amazonOrderTotal, ceil($amazonOrderTotal / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		return $this->load->view('amazon_map/order', $data);
	}

	public function deleteMapOrder() {
		$result = array();
		$this->load->language('amazon_map/order');
		unset($this->session->data['order_delete_result']);
		if (isset($this->request->post['selected']) && $this->validateDelete()) {
				foreach ($this->request->post['selected'] as $map_id) {
					$result[] = $this->_amazonMapOrder->deleteMapOrders($map_id, $this->request->get['account_id']);
				}
				if(!empty($result)){
					$this->session->data['order_delete_result'] = $result;
				}
		}

		$url = '';
		$url .= '&status=account_order_map';

		if (isset($this->request->get['account_id'])) {
			$url .= '&account_id=' . $this->request->get['account_id'];
		}

		if (isset($this->request->get['sort']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_order_map')) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_order_map')) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$this->response->redirect($this->url->link('amazon_map/account/edit', 'user_token=' . $this->session->data['user_token'] . $url, true));
	}

	public function validateDelete() {
		if (!$this->user->hasPermission('modify', 'amazon_map/order')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

    public function getOrderList(){
        $json = $amazonOrderLists = array();
        $this->load->language('amazon_map/order');
        $accountId = $this->request->get['account_id'];

        $checkRecord = $this->validateOrder($this->request->post);
        if(empty($checkRecord)){

          $getAccountEntry = $this->Amazonconnector->getAccountDetails(array('account_id' => $accountId));
          if(isset($getAccountEntry['wk_amazon_connector_marketplace_id']) && $getAccountEntry['wk_amazon_connector_marketplace_id']) {
              $orderLists = $this->Amazonconnector->getOrderList($this->request->post, $accountId);

              if (empty($orderLists['error'])) {
                  if(isset($orderLists['ListOrdersResult']['Orders']['Order']) && $orderLists['ListOrdersResult']['Orders']['Order']){
                      $amazonOrderLists = $orderLists['ListOrdersResult']['Orders']['Order'];
											if(isset($amazonOrderLists[0])){
													$json['success'] = $amazonOrderLists;
											}else{
													$json['success'] = [$amazonOrderLists];
											}
                  }else{
                    $json['error'] = [$this->language->get('error_no_order_found')];
                  }
              }else{
                  $json['error'] = [$orderLists['error']];
              }
          }else{
            $json['error'] = [$this->language->get('error_no_account_details')];
          }
        }else{
          $json['warning'] = $checkRecord;
        }

        $this->response->addHeader('Content-Type: application/json');
    		$this->response->setOutput(json_encode($json));
    }

    public function validateOrder(){
      $error_data = array();

        if(utf8_strlen(trim($this->request->post['amazon_order_from'])) != 10){
            $error_data['error_date_from'] = $this->language->get('error_date_from');
        }

        if(utf8_strlen(trim($this->request->post['amazon_order_to'])) != 10){
            $error_data['error_date_to'] = $this->language->get('error_date_to');
        }

        if(trim($this->request->post['amazon_order_from']) > trim($this->request->post['amazon_order_to'])){
            $error_data['error_date_from'] = $this->language->get('error_invalid_date');
        }

        if(trim($this->request->post['amazon_order_from']) > date("Y-m-d")){
            $error_data['error_date_from'] = $this->language->get('error_lessthan_date');
        }
        if(trim($this->request->post['amazon_order_to']) > date("Y-m-d")){
            $error_data['error_date_to'] = $this->language->get('error_lessthan_date');
        }

        if($this->request->post['amazon_order_maximum'] && (utf8_strlen(trim($this->request->post['amazon_order_maximum'])) > 5)){
            $error_data['error_maximum_record'] = $this->language->get('error_maximum_order');
        }

        if($this->request->post['amazon_order_maximum'] && !preg_match('/^[0-9]+$/', $this->request->post['amazon_order_maximum'])){
            $error_data['error_maximum_record'] = $this->language->get('error_maximum_invalid');
        }
        return $error_data;
    }

    public function importOrder(){
      $json = $getOrderArray = $getOrderList = array();
			$getAllOrderArrays = array();
      $getAccountEntry = $this->Amazonconnector->getAccountDetails(array('account_id' => $this->request->get['account_id']));
			$this->load->model('amazon_map/product');
			$this->load->language('amazon_map/order');

      if(!empty($getAccountEntry) && isset($getAccountEntry['wk_amazon_connector_marketplace_id'])){
          if(!empty($this->request->post['selected']) && isset($this->request->post['selected'][0]) && $this->request->post['selected'][0] && preg_match('~^([0-9]+(\-[0-9]+)*)$~',$this->request->post['selected'][0])){

              $amazonOrderArray = array(
                            'amazonOrderIds'  => $this->request->post['selected'],
                            'prd_reportId'    => $getAccountEntry['wk_amazon_connector_listing_report_id'],
                            'qty_reportId'    => $getAccountEntry['wk_amazon_connector_inventory_report_id'],
                          );

              $getOrderList = $this->Amazonconnector->GetOrder($amazonOrderArray['amazonOrderIds'], $getAccountEntry['id']);

              if(empty($getOrderList['error']) && isset($getOrderList['GetOrderResult']['Orders']['Order']) && $getOrderList['GetOrderResult']['Orders']['Order']){
                  $getOrderArray = $getOrderList['GetOrderResult']['Orders']['Order'];
									if(isset($getOrderArray[0]) && $getOrderArray[0]){
										$getAllOrderArrays = $getOrderArray;
									}else{
										$getAllOrderArrays = [$getOrderArray];
									}

                  foreach ($getAllOrderArrays as $key => $order_data) {
											$checkOrderMapEntry = array();
                      $amazonOrderId 			= $order_data['AmazonOrderId'];

											$checkOrderMapEntry = $this->_amazonMapOrder->getOcAmazonOrderMap(array('amazon_order_id' => $amazonOrderId, 'account_id' => $getAccountEntry['id']));

                      if(empty($checkOrderMapEntry)){
                          if(isset($order_data['OrderStatus']) && ($order_data['OrderStatus'] == 'Shipped' || $order_data['OrderStatus'] == 'Unshipped' || $order_data['OrderStatus'] == 'PartiallyShipped')){
                              /**
                              * Create Customer of Amazon Order in Opencart Store
                              */
                              $getCustomer = $this->_amazonMapOrder->addOrderCustomer($order_data, $getAccountEntry['id']);
															if(!empty($getCustomer) && isset($getCustomer['customer_id']) && $getCustomer['customer_id']){
																	$getCustomer['account_id'] = $getAccountEntry['id'];
																	/**
																	* get Ordered Product with Order-Id
																	*/
																	$getOrderProducts = $this->Amazonconnector->ListOrderItems($amazonOrderId, $getAccountEntry['id']);

																	if(!empty($getOrderProducts) && isset($getOrderProducts) && $getOrderProducts){
																			$orderCreateStatus = 1;
																			$makeOrderProductArray = array();
																			if(isset($getOrderProducts[0][0])){
																					$makeOrderProductArray = $getOrderProducts[0];
																			}else{
																					$makeOrderProductArray = $getOrderProducts;
																			}

																			/**
																			* Check if order has any product with combination, then order will not import to opencart (Only If config wk_amazon_connector_variation condition is set to '2' )
																			*/
																			if($this->config->get('wk_amazon_connector_variation') == 2){
																					foreach ($makeOrderProductArray as $key => $order_product) {
																							$order_product = (array)$order_product;

																							if(isset($order_product['ASIN'])){
																									// check product is already imported to opencart store or note
																									$getproductEntry = $this->model_amazon_map_product->getProductMappedEntry(array('filter_amazon_product_id' => $order_product['ASIN'], 'account_id' => $getAccountEntry['id']));

																									if(empty($getproductEntry)){
																											$getVaristionASINEntry = $this->Amazonconnector->filterVariationASINEntry(array('account_id' => $getAccountEntry['id'], 'id_value' => $order_product['ASIN']));

																											if(empty($getVaristionASINEntry)){
																													$parentCheck = $this->Amazonconnector->getMatchedProduct($order_product['ASIN'], $getAccountEntry['id']);
																													if (isset($parentCheck['GetMatchingProductResult']['Product']['Relationships']['VariationParent'])){
																															$orderCreateStatus = 0;
																															break;
																													}
																											}
																									}
																							}
																					}
																			}

																			if($orderCreateStatus){
																					//create amazon order to opencart store
																					$result = $this->_amazonMapOrder->createOrders($order_data, $makeOrderProductArray, $getCustomer, $amazonOrderArray);
																					if(isset($result['status']) && $result['status']){
																							$json['success'][] = $result;
																					}else{
																							$json['error'][] = $result;
																					}
																			}else{
																					$json['error'][] = array('status' => false, 'message' => sprintf($this->language->get('error_order_combinat_product'), $amazonOrderId));
																			}
																	}else{
																			$json['error'][] = array('status' => false, 'message' => sprintf($this->language->get('error_no_product_found'), $amazonOrderId));
																	}
															}else{
																	$json['error'][] = array('status' => false, 'message' => sprintf($this->language->get('error_customer_notfound'), $amazonOrderId));
															}
                          }else{
                              $json['error'][] = array('status' => false, 'message' => sprintf($this->language->get('error_order_status'), $amazonOrderId));
                          }
                      }else{
                          $json['error'][] = array('status' => false, 'message' => sprintf($this->language->get('error_already_map').$checkOrderMapEntry[0]['oc_order_id'], $amazonOrderId));
                      }
                  }// order loop end here
              }else{
                  $json['warning'] = [$this->language->get('error_no_order_found')];
              }
          }else{
              $json['warning'] = [$this->language->get('error_order_required')];
          }

      }else{
        $json['warning'] = [$this->language->get('error_no_account_details')];
      }
      $this->response->addHeader('Content-Type: application/json');
  		$this->response->setOutput(json_encode($json));
    }

}
