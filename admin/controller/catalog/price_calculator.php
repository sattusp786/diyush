<?php
class ControllerCatalogPriceCalculator extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('catalog/price_calculator');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/price_calculator');

		$GLOBALS["pricing"] = array();
		
		$this->getForm();
	}

	public function add() {
		$this->load->language('catalog/price_calculator');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/price_calculator');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {

			$price_calculate = array();
			
			$total_price = array();
			
			$price = $this->model_catalog_price_calculator->calculatePrice($this->request->post);
			
			$GLOBALS["pricing"] = $price;
		}

		$this->getForm();
	}

	protected function getForm() {
		global $price;
		
		$data['text_form'] = !isset($this->request->get['price_calculator_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = array();
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
			'href' => $this->url->link('catalog/price_calculator', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['price_calculator_id'])) {
			$data['action'] = $this->url->link('catalog/price_calculator/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('catalog/price_calculator/edit', 'user_token=' . $this->session->data['user_token'] . '&price_calculator_id=' . $this->request->get['price_calculator_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('catalog/price_calculator', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('localisation/currency');
		$this->load->model('catalog/price_calculator');

		$cur_data = array();
		$data['default_currency'] =  $this->config->get('config_currency_catalog');
		
		$data['currencies'] = $this->model_localisation_currency->getCurrencies($cur_data);


		if (isset($this->request->post['currency'])) {
			$data['currency'] = $this->request->post['currency'];				
		} else {
			$data['currency'] = '';
		}
		$data['product_types'] = $this->model_catalog_price_calculator->getProductTypes();
		if (isset($this->request->post['product_type_id'])) {
			$data['product_type_id'] = $this->request->post['product_type_id'];				
		} else {
			$data['product_type_id'] = '';
		}
          
		
		if (isset($this->request->post['price_calculator_id'])) {
			$data['price_calculator_id'] = $this->request->post['price_calculator_id'];		
		} else {
			$data['price_calculator_id'] = '';
		}
		
		$data['options'] = $this->model_catalog_price_calculator->getOptionValue();

		if (isset($this->request->post['metal_purity'])) {			
			$data['metal_purity'] = $this->request->post['metal_purity'];		
		} else {			
			$data['metal_purity'] = '';			
		}
		if (isset($this->request->post['metal_weight'])) {
			$data['metal_weight'] = $this->request->post['metal_weight'];	
		} else {
			$data['metal_weight'] = '';
		}

		
		$data['markups'] = $this->model_catalog_price_calculator->getProductMarkup();
		
		if (isset($this->request->post['markup'])) {
			$data['markup'] = $this->request->post['markup'];	
		} else {
			$data['markup'] = '';
		}

		$data['taxss'] = $this->model_catalog_price_calculator->getProductTax();

		if (isset($this->request->post['tax'])) {
			$data['tax'] = $this->request->post['tax'];	
		} else {
			$data['tax'] ='';
		}
               
        if (isset($this->request->post['stores'])) {
			$data['store_id'] = $this->request->post['stores'];	
		} else {
			$data['store_id'] = 0;
		}
                
        $this->load->model('setting/store');
		 $data['stores'] = $this->model_setting_store->getStores();                
                 
		if (!isset($data['metal_price'])) {
			$data['metal_price'] = 0;
		}
		if (!isset($data['stone_price'])) {
			$data['stone_price'] = 0;
			
		}
		if (!isset($data['total_price'])) {
			$data['total_price'] = 0;			
		}
		
		$data['price'] = $GLOBALS["pricing"];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/price_calculator_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'catalog/price_calculator')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}


		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}
	
}