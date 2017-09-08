<?php
/**
 * @version [2.3.x.x] [Supported opencart version 2.3.x.x]
 * @category Webkul
 * @package Opencart-Amazon Connector
 * @author [Webkul] <[<http://webkul.com/>]>
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
class ControllerAmazonMapAccount extends Controller {
	private $error = array();
	private $post_fields = array(
		'wk_amazon_connector_store_name',
		'wk_amazon_connector_attribute_group',
		'wk_amazon_connector_marketplace_id',
		'wk_amazon_connector_seller_id',
		'wk_amazon_connector_access_key_id',
		'wk_amazon_connector_secret_key',
		'wk_amazon_connector_country',
		'wk_amazon_connector_currency_rate',
		);

	public function __construct($registory) {
		parent::__construct($registory);
		$this->load->model('amazon_map/account');
		$this->_amazonAccount = $this->model_amazon_map_account;

		$this->load->model('localisation/country');
		$this->_countryList = $this->model_localisation_country;
    }

    public function index() {
    	$this->load->language('amazon_map/account');

		$this->getList();
	}

	public function delete() {
		$this->load->language('amazon_map/account');

		$this->document->setTitle($this->language->get('heading_title'));

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $account_id) {
				$this->_amazonAccount->deleteAccount($account_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('amazon_map/account', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function getList() {
		$data = array();
		$data = array_merge($data, $this->load->language('amazon_map/account'));

		$this->document->setTitle($this->language->get('heading_title'));

		if (isset($this->request->get['filter_account_id'])) {
			$filter_account_id = $this->request->get['filter_account_id'];
		} else {
			$filter_account_id = null;
		}

		if (isset($this->request->get['filter_store_name'])) {
			$filter_store_name = $this->request->get['filter_store_name'];
		} else {
			$filter_store_name = null;
		}

		if (isset($this->request->get['filter_marketplace_id'])) {
			$filter_marketplace_id = $this->request->get['filter_marketplace_id'];
		} else {
			$filter_marketplace_id = null;
		}

		if (isset($this->request->get['filter_seller_id'])) {
			$filter_seller_id = $this->request->get['filter_seller_id'];
		} else {
			$filter_seller_id = null;
		}

		if (isset($this->request->get['filter_added_date'])) {
			$filter_added_date = $this->request->get['filter_added_date'];
		} else {
			$filter_added_date = null;
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['filter_account_id'])) {
			$url .= '&filter_account_id=' . $this->request->get['filter_account_id'];
		}

		if (isset($this->request->get['filter_store_name'])) {
			$url .= '&filter_store_name=' . urlencode(html_entity_decode($this->request->get['filter_store_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_marketplace_id'])) {
			$url .= '&filter_marketplace_id=' . urlencode(html_entity_decode($this->request->get['filter_marketplace_id'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_seller_id'])) {
			$url .= '&filter_seller_id=' . urlencode(html_entity_decode($this->request->get['filter_seller_id'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_added_date'])) {
			$url .= '&filter_added_date=' . urlencode(html_entity_decode($this->request->get['filter_added_date'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('amazon_map/account', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add_account'] = $this->url->link('amazon_map/account/getForm', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('amazon_map/account/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['clear'] = $this->url->link('amazon_map/account', 'user_token=' . $this->session->data['user_token'], true);

		$data['user_token'] 	= $this->session->data['user_token'];

		$data['amazon_accounts'] = array();

		$filter_data = array(
			'filter_account_id'	  	=> $filter_account_id,
			'filter_store_name'	  	=> $filter_store_name,
			'filter_marketplace_id'	=> $filter_marketplace_id,
			'filter_seller_id'			=> $filter_seller_id,
			'filter_added_date'			=> $filter_added_date,
			'sort'  				=> $sort,
			'order' 				=> $order,
			'start' 				=> ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'				 	=> $this->config->get('config_limit_admin')
		);

		$amazonTotalAccount = $this->_amazonAccount->getTotalAmazonAccount($filter_data);

		$results = $this->_amazonAccount->getAmazonAccount($filter_data);

		if($results){
			foreach ($results as $result) {
				$data['amazon_accounts'][] = array(
					'account_id' 		=> $result['id'],
					'store_name'    => $result['wk_amazon_connector_store_name'],
					'marketplace_id'=> $result['wk_amazon_connector_marketplace_id'],
					'seller_id' 		=> $result['wk_amazon_connector_seller_id'],
					'added_date'	  => $result['wk_amazon_connector_date_added'],
					'edit'  				=> $this->url->link('amazon_map/account/edit', 'user_token=' . $this->session->data['user_token'] . '&account_id=' . $result['id'] . $url, true),
				);
			}
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

		if (isset($this->request->get['filter_account_id'])) {
			$url .= '&filter_account_id=' . $this->request->get['filter_account_id'];
		}

		if (isset($this->request->get['filter_store_name'])) {
			$url .= '&filter_store_name=' . urlencode(html_entity_decode($this->request->get['filter_store_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_marketplace_id'])) {
			$url .= '&filter_marketplace_id=' . urlencode(html_entity_decode($this->request->get['filter_marketplace_id'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_seller_id'])) {
			$url .= '&filter_seller_id=' . urlencode(html_entity_decode($this->request->get['filter_seller_id'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_added_date'])) {
			$url .= '&filter_added_date=' . urlencode(html_entity_decode($this->request->get['filter_added_date'], ENT_QUOTES, 'UTF-8'));
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_account_id'] = $this->url->link('amazon_map/account', 'user_token=' . $this->session->data['user_token'] . '&sort=id' . $url, true);
		$data['sort_store_name'] = $this->url->link('amazon_map/account', 'user_token=' . $this->session->data['user_token'] . '&sort=wk_amazon_connector_store_name' . $url, true);
		$data['sort_marketplace_id'] = $this->url->link('amazon_map/account', 'user_token=' . $this->session->data['user_token'] . '&sort=wk_amazon_connector_marketplace_id' . $url, true);
		$data['sort_seller_id'] = $this->url->link('amazon_map/account', 'user_token=' . $this->session->data['user_token'] . '&sort=wk_amazon_connector_seller_id' . $url, true);
		$data['sort_added_date'] = $this->url->link('amazon_map/account', 'user_token=' . $this->session->data['user_token'] . '&sort=wk_amazon_connector_date_added' . $url, true);

		$url = '';

		if (isset($this->request->get['filter_account_id'])) {
			$url .= '&filter_account_id=' . $this->request->get['filter_account_id'];
		}

		if (isset($this->request->get['filter_store_name'])) {
			$url .= '&filter_store_name=' . urlencode(html_entity_decode($this->request->get['filter_store_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_marketplace_id'])) {
			$url .= '&filter_marketplace_id=' . urlencode(html_entity_decode($this->request->get['filter_marketplace_id'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_seller_id'])) {
			$url .= '&filter_seller_id=' . urlencode(html_entity_decode($this->request->get['filter_seller_id'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_added_date'])) {
			$url .= '&filter_added_date=' . urlencode(html_entity_decode($this->request->get['filter_added_date'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $amazonTotalAccount;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('amazon_map/account', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($amazonTotalAccount) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($amazonTotalAccount - $this->config->get('config_limit_admin'))) ? $amazonTotalAccount : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $amazonTotalAccount, ceil($amazonTotalAccount / $this->config->get('config_limit_admin')));

		$data['filter_account_id'] 	= $filter_account_id;
		$data['filter_store_name'] 	= $filter_store_name;
		$data['filter_marketplace_id']	= $filter_marketplace_id;
		$data['filter_seller_id']	= $filter_seller_id;
		$data['filter_added_date']	= $filter_added_date;

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');


		$this->response->setOutput($this->load->view('amazon_map/account', $data));
	}

	public function add() {
		$data = array();
		$data = array_merge($data, $this->load->language('amazon_map/account'));

		$this->document->setTitle($this->language->get('heading_title_add'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateAccount()) {

			$this->_amazonAccount->__addAmazonAccount($this->request->post);
			$this->session->data['success'] = $this->language->get('text_success_add');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('amazon_map/account', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}


	public function edit() {
		$data = array();
		$data = array_merge($data, $this->load->language('amazon_map/account'));

		$this->document->setTitle($this->language->get('heading_title_edit'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateAccount()) {

			$this->_amazonAccount->__addAmazonAccount($this->request->post);
			$this->session->data['success'] = $this->language->get('text_success_add');

			$url = '';

			if (isset($this->request->get['status'])) {
				$url .= '&status=' . $this->request->get['status'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('amazon_map/account', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}


	public function getForm() {
		$data = array();
		$data = array_merge($data, $this->load->language('amazon_map/account'));

		if (!isset($this->request->get['account_id'])) {
			$this->document->setTitle($this->language->get('heading_title_add'));
			$data['heading_title'] = $this->language->get('heading_title_add');
		}else{
			$this->document->setTitle($this->language->get('heading_title_edit'));
			$data['heading_title'] = $this->language->get('heading_title_edit');
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('amazon_map/account', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title_add'),
			'href' => $this->url->link('amazon_map/account/add', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['account_id'])) {
			$data['action'] = $this->url->link('amazon_map/account/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('amazon_map/account/edit', 'user_token=' . $this->session->data['user_token'] . '&account_id=' . $this->request->get['account_id'] .$url, true);
		}

		$data['cancel'] = $this->url->link('amazon_map/account', 'user_token=' . $this->session->data['user_token'] , true);

		$data['user_token'] 	= $this->session->data['user_token'];

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['error_account_details'])) {
			$data['error_warning'] = $this->error['error_account_details'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['error_wk_amazon_connector_store_name'])) {
			$data['error_wk_amazon_connector_store_name'] = $this->error['error_wk_amazon_connector_store_name'];
		} else {
			$data['error_wk_amazon_connector_store_name'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		foreach ($this->post_fields as $key => $error_value) {
			if (isset($this->error['error_'.$error_value])  && $error_value != 'wk_amazon_connector_sites') {
				$data['error_'.$error_value] = $this->error['error_'.$error_value];
			} else {
				$data['error_'.$error_value] = '';
			}
		}

		$data['account_id'] = false;
		if (isset($this->request->get['account_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$account_info = $this->_amazonAccount->getAmazonAccount(array('filter_account_id' => $this->request->get['account_id']));
		}

		if(isset($this->request->get['account_id'])){
			$data['account_id'] = $this->request->get['account_id'];
		}

		foreach ($this->post_fields as $key => $post_value) {
			if (isset($this->request->post[$post_value])) {
				$data[$post_value] = $this->request->post[$post_value];
			} elseif (!empty($account_info[0]) && isset($account_info[0][$post_value])) {
				$data[$post_value] = $account_info[0][$post_value];
			} else {
				$data[$post_value] = '';
			}
		}
		$this->load->model('catalog/attribute_group');
		$data['attribute_groups'] = $this->model_catalog_attribute_group->getAttributeGroups(array('get_module_list' => true));

		//get all opencart country list
		$data['countries'] = $this->_countryList->getCountries();

		$data['product_map'] 	= $this->load->controller('amazon_map/product');
		$data['order_map'] 		= $this->load->controller('amazon_map/order');
		$data['customer_map'] = $this->load->controller('amazon_map/customer');


		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('amazon_map/account_form', $data));
	}

	protected function validateAccount() {
		if (!$this->user->hasPermission('modify', 'amazon_map/account')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		//post fields blank check
		foreach ($this->post_fields as $key => $post_value) {
			if(empty($this->request->post[$post_value])){
				$this->error['error_'.$post_value]	=	$this->language->get('error_field_required');
			}
		}

		if(!empty($this->request->post['wk_amazon_connector_currency_rate'])){
				if(!is_numeric($this->request->post['wk_amazon_connector_currency_rate'])) {
						$this->error['error_wk_amazon_connector_currency_rate'] = $this->language->get('error_invalid_currency_rate');
				}
		}

		if(!$this->error){
			if(!isset($this->request->get['account_id']) && $this->request->get['route'] == 'amazon_map/account/add'){
				$getEbayAccount = $this->_amazonAccount->getAmazonAccount(array('filter_store_name' => $this->request->post['wk_amazon_connector_store_name']));
				if(isset($getEbayAccount[0]['id']) && $getEbayAccount[0]['id']){
					$this->error['error_wk_amazon_connector_store_name'] = $this->language->get('error_wk_amazon_connector_store_name');
				}
			}

			if(isset($this->request->get['account_id']) && $this->request->get['route'] == 'amazon_map/account/edit'){
				$getEbayAccount = $this->_amazonAccount->getAmazonAccount(array('filter_store_name' => $this->request->post['wk_amazon_connector_store_name']));

				if(isset($getEbayAccount[0]['id']) && $getEbayAccount[0]['id'] !== $this->request->get['account_id']){
					$this->error['error_wk_amazon_connector_store_name'] = $this->language->get('error_wk_amazon_connector_store_name');
				}
			}
		}

		if(!$this->error){
			$result = array();
			$result = $this->Amazonconnector->getListMarketplaceParticipations($this->request->post);

			if(isset($result['error']) && $result['error']){
				$this->error['error_account_details'] = $this->language->get('error_account_details');
			}else{
				$this->request->post['wk_amazon_connector_currency_code'] = (string)$result['currency_code'][0];
			}


		}
		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'amazon_map/account')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
