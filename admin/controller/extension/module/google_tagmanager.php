<?php
class ControllerExtensionModuleGoogleTagManager extends Controller {
	private $error = array();
	
	public function index() {
		$this->load->language('extension/module/google_tagmanager');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_tagmanager', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['code'])) {
			$data['error_code'] = $this->error['code'];
		} else {
			$data['error_code'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/google_tagmanager', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/google_tagmanager', 'user_token=' . $this->session->data['user_token'] , true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
		
		$data['user_token'] = $this->session->data['user_token'];
				
		if (isset($this->request->post['module_tagmanager_code'])) {
			$data['module_tagmanager_code'] = $this->request->post['module_tagmanager_code'];
		} else {
			$data['module_tagmanager_code'] = $this->config->get('module_tagmanager_code');
		}
		
		if (isset($this->request->post['module_tagmanager_status'])) {
			$data['module_tagmanager_status'] = $this->request->post['module_tagmanager_status'];
		} else {
			$data['module_tagmanager_status'] = $this->config->get('module_tagmanager_status');
		}
		
		if (isset($this->request->post['module_tagmanager_admin'])) {
			$data['module_tagmanager_admin'] = $this->request->post['module_tagmanager_admin'];
		} else {
			$data['module_tagmanager_admin'] = $this->config->get('module_tagmanager_admin');
		}
		
		if (isset($this->request->post['module_tagmanager_adword'])) {
			$data['module_tagmanager_adword'] = $this->request->post['module_tagmanager_adword'];
		} else {
			$data['module_tagmanager_adword'] = $this->config->get('module_tagmanager_adword');
		}
		
		if (isset($this->request->post['module_tagmanager_conversion_id'])) {
			$data['module_tagmanager_conversion_id'] = $this->request->post['module_tagmanager_conversion_id'];
		} else {
			$data['module_tagmanager_conversion_id'] = $this->config->get('module_tagmanager_conversion_id');
		}
		
		if (isset($this->request->post['module_tagmanager_conversion_label'])) {
			$data['module_tagmanager_conversion_label'] = $this->request->post['module_tagmanager_conversion_label'];
		} else {
			$data['module_tagmanager_conversion_label'] = $this->config->get('module_tagmanager_conversion_label');
		}
		
		if (isset($this->request->post['module_tagmanager_eu_cookie'])) {
			$data['module_tagmanager_eu_cookie'] = $this->request->post['module_tagmanager_eu_cookie'];
		} else {
			$data['module_tagmanager_eu_cookie'] = $this->config->get('module_tagmanager_eu_cookie');
		}
		if (isset($this->request->post['module_tagmanager_cookie_text'])) {
			$data['module_tagmanager_cookie_text'] = $this->request->post['module_tagmanager_cookie_text'];
		} else {
			$data['module_tagmanager_cookie_text'] = $this->config->get('module_tagmanager_cookie_text');
		}
		if (isset($this->request->post['module_tagmanager_cookie_link'])) {
			$data['module_tagmanager_cookie_link'] = $this->request->post['module_tagmanager_cookie_link'];
		} else {
			$data['module_tagmanager_cookie_link'] = $this->config->get('module_tagmanager_cookie_link');
		}
		if (isset($this->request->post['module_tagmanager_cookie_button1'])) {
			$data['module_tagmanager_cookie_button1'] = $this->request->post['module_tagmanager_cookie_button1'];
		} else {
			$data['module_tagmanager_cookie_button1'] = $this->config->get('module_tagmanager_cookie_button1');
		}
		if (isset($this->request->post['module_tagmanager_cookie_button2'])) {
			$data['module_tagmanager_cookie_button2'] = $this->request->post['module_tagmanager_cookie_button2'];
		} else {
			$data['module_tagmanager_cookie_button2'] = $this->config->get('module_tagmanager_cookie_button2');
		}
		if (isset($this->request->post['module_tagmanager_remarketing'])) {
			$data['module_tagmanager_remarketing'] = $this->request->post['module_tagmanager_remarketing'];
		} else {
			$data['module_tagmanager_remarketing'] = $this->config->get('module_tagmanager_remarketing');
		}
		if (isset($this->request->post['module_tagmanager_product'])) {
			$data['module_tagmanager_product'] = $this->request->post['module_tagmanager_product'];
		} else {
			$data['module_tagmanager_product'] = $this->config->get('module_tagmanager_product');
		}
		if (isset($this->request->post['module_tagmanager_pixel'])) {
			$data['module_tagmanager_pixel'] = $this->request->post['module_tagmanager_pixel'];
		} else {
			$data['module_tagmanager_pixel'] = $this->config->get('module_tagmanager_pixel');
		}
			if (isset($this->request->post['module_tagmanager_pixelcode'])) {
			$data['module_tagmanager_pixelcode'] = $this->request->post['module_tagmanager_pixelcode'];
		} else {
			$data['module_tagmanager_pixelcode'] = $this->config->get('module_tagmanager_pixelcode');
		}
		if (isset($this->request->post['module_tagmanager_ptitle'])) {
			$data['module_tagmanager_ptitle'] = $this->request->post['module_tagmanager_ptitle'];
		} else {
			$data['module_tagmanager_ptitle'] = $this->config->get('module_module_tagmanager_ptitle');
		}
		
		$data['product_map'] = array ('product_id','model','sku','model_product_id','product_id_currency');
		$data['product_title'] = array ('name','brand_model');
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/google_tagmanager', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/google_tagmanager')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['module_tagmanager_code']) {
			$this->error['code'] = $this->language->get('error_code');
		}			

		return !$this->error;
	}
}
