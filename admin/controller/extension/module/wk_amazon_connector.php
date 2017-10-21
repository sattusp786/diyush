<?php
/**
 * @version [2.3.x.x] [Supported opencart version 2.3.x.x]
 * @category Webkul
 * @package Opencart-Amazon Connector
 * @author [Webkul] <[<http://webkul.com/>]>
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
class ControllerExtensionModuleWkAmazonConnector extends Controller {
	private $error = array();

	public function __construct($registory) {
		parent::__construct($registory);
		$this->load->model('extension/module/wk_amazon_connector');
		$this->_ebayModuleAmazonConnector = $this->model_extension_module_wk_amazon_connector;

		$this->load->language('extension/module/wk_amazon_connector');
    }

	public function install(){
		$this->_ebayModuleAmazonConnector->createTables();

		$this->load->model('user/user_group');
		$controllers = array(
			'amazon_map/account',
			'amazon_map/product',
			'amazon_map/order',
			'amazon_map/export_product',
		);

		foreach ($controllers as $key => $controller) {
			$this->model_user_user_group->addPermission($this->user->getId(),'access',$controller);
			$this->model_user_user_group->addPermission($this->user->getId(),'modify',$controller);
		}
	}

	public function index() {
		$data = array();
		$data = array_merge($data, $this->load->language('extension/module/wk_amazon_connector'));

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('wk_amazon_connector', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/wk_amazon_connector', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/wk_amazon_connector', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		$post_data = array(
			'wk_amazon_connector_status',
			//general tab
			'wk_amazon_connector_default_category',
			'wk_amazon_connector_default_quantity',
			'wk_amazon_connector_default_weight',
			'wk_amazon_connector_default_store',
			'wk_amazon_connector_order_status',
			'wk_amazon_connector_default_product_store',
			'wk_amazon_connector_variation',
			'wk_amazon_connector_import_update',
			'wk_amazon_connector_export_update',
			);
		foreach ($post_data as $key => $post) {
			if (isset($this->request->post[$post])) {
				$data[$post] = $this->request->post[$post];
			} else {
				$data[$post] = $this->config->get($post);
			}
		}

		$data['getOcParentCategory'] = $this->_ebayModuleAmazonConnector->get_OpencartCategories(array());

		$this->load->model('setting/store');
		$data['stores'] = $this->model_setting_store->getStores();

		$this->load->model('localisation/order_status');
		$data['order_status'] = $this->model_localisation_order_status->getOrderStatuses();

		$data['user_token'] =  $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/wk_amazon_connector', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/wk_amazon_connector')) {
			//$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function uninstall(){
		$this->_ebayModuleAmazonConnector->removeTables();
	}
}
