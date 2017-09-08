<?php
/**
 * @version [2.3.x.x] [Supported opencart version 2.3.x.x]
 * @category Webkul
 * @package Opencart-Amazon Connector
 * @author [Webkul] <[<http://webkul.com/>]>
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
class ControllerAmazonMapCustomer extends Controller {
	private $error = array();

	public function __construct($registory) {
		parent::__construct($registory);
		$this->load->model('amazon_map/order');
		$this->_amazonMapOrder = $this->model_amazon_map_order;
    }

    public function index() {
		$data = array();
		$data = array_merge($data, $this->load->language('amazon_map/customer'));

		$this->document->addScript('view/javascript/amazon_connector/webkul_amazon_connector.js');

		if(isset($this->request->get['account_id'])) {
			$data['account_id'] = $account_id = $this->request->get['account_id'];
		}else{
			$data['account_id'] = $account_id = 0;
		}

		if (isset($this->request->get['sort']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_customer_map')) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'id';
		}

		if (isset($this->request->get['order']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_customer_map')) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_customer_map')) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		$url .= '&status=account_customer_map';

		if (isset($this->request->get['account_id'])) {
			$url .= '&account_id=' . $this->request->get['account_id'];
		}

		if (isset($this->request->get['sort']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_customer_map')) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_customer_map')) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_customer_map')) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['amazon_customers'] = array();

		$filter_data = array(
			'filter_account_id' => $account_id,
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$amazonCustomerTotal  = $this->_amazonMapOrder->getTotalAmazonCustomerList($filter_data);

		$results 					    = $this->_amazonMapOrder->getAmazonCustomerList($filter_data);

		if($results){
			foreach ($results as $result) {

				$data['amazon_customers'][] = array(
					'map_id' 							=> $result['id'],
					'oc_order_id' 				=> $result['oc_customer_id'],
					'customer_name' 		  => $result['customer_name'],
					'customer_email'	 		=> $result['email'],
					'city'	 							=> $result['city'],
					'country'             => $result['country'],
					'view'								=> $this->url->link('customer/customer/edit', 'user_token=' . $this->session->data['user_token'] . '&customer_id='.$result['oc_customer_id'], true),
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

		$url .= '&status=account_customer_map';

		if (isset($this->request->get['account_id'])) {
			$url .= '&account_id=' . $this->request->get['account_id'];
		}

		$data['button_back_link'] = $this->url->link('amazon_map/account/edit', 'user_token=' . $this->session->data['user_token'] .$url, true);

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_customer_map')) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_map_id'] = $this->url->link('amazon_map/account/edit', 'user_token=' . $this->session->data['user_token'] . '&sort=id' . $url, true);
		$data['sort_oc_customer_id'] = $this->url->link('amazon_map/account/edit', 'user_token=' . $this->session->data['user_token'] . '&sort=acm.oc_customer_id' . $url, true);
		$data['sort_customer_name'] = $this->url->link('amazon_map/account/edit', 'user_token=' . $this->session->data['user_token'] . '&sort=customer_name' . $url, true);
    $data['sort_customer_email'] = $this->url->link('amazon_map/account/edit', 'user_token=' . $this->session->data['user_token'] . '&sort=c.email' . $url, true);
		$data['sort_city'] = $this->url->link('amazon_map/account/edit', 'user_token=' . $this->session->data['user_token'] . '&sort=acm.city' . $url, true);
		$data['sort_country'] = $this->url->link('amazon_map/account/edit', 'user_token=' . $this->session->data['user_token'] . '&sort=acm.country' . $url, true);

		$url = '';

		$url .= '&status=account_customer_map';

		if (isset($this->request->get['account_id'])) {
			$url .= '&account_id=' . $this->request->get['account_id'];
		}

		if (isset($this->request->get['sort']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_customer_map')) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order']) && (isset($this->request->get['status']) && $this->request->get['status'] == 'account_customer_map')) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$data['action_delete'] = $this->url->link('amazon_map/order/deleteMapOrder', 'user_token=' . $this->session->data['user_token'] .$url, true);

		$pagination = new Pagination();
		$pagination->total = $amazonCustomerTotal;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('amazon_map/account/edit', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($amazonCustomerTotal) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($amazonCustomerTotal - $this->config->get('config_limit_admin'))) ? $amazonCustomerTotal : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $amazonCustomerTotal, ceil($amazonCustomerTotal / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		return $this->load->view('amazon_map/customer', $data);
	}

}
