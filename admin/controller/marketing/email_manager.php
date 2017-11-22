<?php 
class ControllerMarketingEmailManager extends Controller { 
	private $error = array();
   
  	public function index() {
		$this->load->language('marketing/email_manager');
	
    	$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('marketing/email_manager');
		
    	$this->getList();
  	}
              
  	public function insert() {

		$this->load->language('marketing/email_manager');
	
    	$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('marketing/email_manager');
			
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
      		$this->model_marketing_email_manager->addEmailManager($this->request->post);
		  	
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
						
      		$this->response->redirect($this->url->link('marketing/email_manager', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL'));
		}
	
    	$this->getForm();
  	}

  	public function update() {
		$this->load->language('marketing/email_manager');
	
    	$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('marketing/email_manager');
				
    	if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
	  		$this->model_marketing_email_manager->editEmailManager($this->request->get['email_manager_id'], $this->request->post);
			
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
			
			$this->response->redirect($this->url->link('marketing/email_manager', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL'));
    	}
	
    	$this->getForm();
  	}

  	public function delete() {
		$this->load->language('marketing/email_manager');
	
    	$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('marketing/email_manager');
		
    	if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $email_manager_id) {
				$this->model_marketing_email_manager->deleteEmailManager($email_manager_id);
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
			
			$this->response->redirect($this->url->link('marketing/email_manager', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL'));
   		}
	
    	$this->getList();
  	}
  
  	protected function getList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'emd.subject';
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
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], 'SSL'),
      		'separator' => false
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('marketing/email_manager', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
							
		$data['add'] = $this->url->link('marketing/email_manager/insert', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL');
		$data['delete'] = $this->url->link('marketing/email_manager/delete', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL');	

		$data['email_managers'] = array();
		
		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);
		
		$email_manager_total = $this->model_marketing_email_manager->getTotalEmailManager($filter_data);
	
		$results = $this->model_marketing_email_manager->getEmailManagers($filter_data);
		
    	foreach ($results as $result) {
			$action = array();
			
			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('marketing/email_manager/update', 'user_token=' . $this->session->data['user_token'] . '&email_manager_id=' . $result['email_manager_id'] . $url, 'SSL')
			);
						
			$data['email_managers'][] = array(
				'email_manager_id' => $result['email_manager_id'],
				'subject'               => $result['subject'],
				'short_description'     => $result['short_description'],			
				'code'					=> $result['code'],
				'sort_order'			=> $result['sort_order'],
				'selected'				=> isset($this->request->post['selected']) && in_array($result['email_manager_id'], $this->request->post['selected']),
				'action'             => $action
			);
		}	
	
		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_list'] = $this->language->get('text_list');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_confirm'] = $this->language->get('text_confirm');
		
		$data['column_name'] = $this->language->get('column_name');
		$data['column_subject'] = $this->language->get('column_subject');
		$data['column_code'] = $this->language->get('column_code');
		$data['column_sort_order'] = $this->language->get('column_sort_order');
		$data['column_action'] = $this->language->get('column_action');				

		$data['button_add'] = $this->language->get('button_add');
		$data['button_edit'] = $this->language->get('button_edit');
		$data['button_delete'] = $this->language->get('button_delete');
		
 
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

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		$data['sort_subject'] = $this->url->link('marketing/email_manager', 'user_token=' . $this->session->data['user_token'] . '&sort=subject' . $url, 'SSL');
		$data['sort_code'] = $this->url->link('marketing/email_manager', 'user_token=' . $this->session->data['user_token'] . '&sort=code' . $url, 'SSL');
		$data['sort_short_description'] = $this->url->link('marketing/email_manager', 'user_token=' . $this->session->data['user_token'] . '&sort=short_description' . $url, 'SSL');
		$data['sort_sort_order'] = $this->url->link('marketing/email_manager', 'user_token=' . $this->session->data['user_token'] . '&sort=ag.sort_order' . $url, 'SSL');
		
		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}
												
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		
		$pagination = new Pagination();
		$pagination->total = $email_manager_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('marketing/email_manager', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();
		
		$data['results'] = sprintf($this->language->get('text_pagination'), ($email_manager_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($email_manager_total - $this->config->get('config_limit_admin'))) ? $email_manager_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $email_manager_total, ceil($email_manager_total / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$this->load->model('design/layout');

		$data['layouts'] = $this->model_design_layout->getLayouts();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('marketing/email_manager_list', $data));
  	}
 
  	protected function getForm() {
     	$data['heading_title'] = $this->language->get('heading_title');

		$data['text_form'] = !isset($this->request->get['email_manager_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
		
    	$data['entry_short_description'] = $this->language->get('entry_short_description');
		$data['entry_subject'] = $this->language->get('entry_subject');
		$data['entry_content'] = $this->language->get('entry_content');

    	$data['button_save'] = $this->language->get('button_save');
    	$data['button_cancel'] = $this->language->get('button_cancel');
		
		$data['tab_general'] = $this->language->get('tab_general');
    	$data['tab_data'] = $this->language->get('tab_data');
		$data['tab_design'] = $this->language->get('tab_design');
		
		$data['help_code'] = $this->language->get('help_code');
		$data['entry_store'] = $this->language->get('entry_store');
		$data['entry_code'] = $this->language->get('entry_code');
		$data['entry_bottom'] = $this->language->get('entry_bottom');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['text_default'] = $this->language->get('text_default');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
    
 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

 		if (isset($this->error['name'])) {
			$data['error_short_description'] = $this->error['name'];
		} else {
			$data['error_short_description'] = array();
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
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], 'SSL'),    		
      		'separator' => false
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('marketing/email_manager', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
		
		if (!isset($this->request->get['email_manager_id'])) {
			$data['action'] = $this->url->link('marketing/email_manager/insert', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL');
		} else {
			$data['action'] = $this->url->link('marketing/email_manager/update', 'user_token=' . $this->session->data['user_token'] . '&email_manager_id=' . $this->request->get['email_manager_id'] . $url, 'SSL');
		}
			
		$data['cancel'] = $this->url->link('marketing/email_manager', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL');

		if (isset($this->request->get['email_manager_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
		    $email_manager_info = $this->model_marketing_email_manager->getEmailManager($this->request->get['email_manager_id']);
		}
		
		$data['user_token'] = $this->session->data['user_token'];
		
		$this->load->model('localisation/language');
		
		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		$this->load->model('setting/store');

		$data['stores'] = array();
		
		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		);
		
		$stores = $this->model_setting_store->getStores();

		foreach ($stores as $store) {
			$data['stores'][] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			);
		}
		
		if (isset($this->request->post['email_manager_description'])) {
			$data['email_manager_description'] = $this->request->post['email_manager_description'];
		} elseif (isset($this->request->get['email_manager_id'])) {
			$data['email_manager_description'] = $this->model_marketing_email_manager->getEmailManagerDescriptions($this->request->get['email_manager_id']);
		} else {
			$data['email_manager_description'] = array();
		}
		
		if (isset($this->request->post['email_manager_store'])) {
			$data['email_manager_store'] = $this->request->post['email_manager_store'];
		} elseif (isset($this->request->get['email_manager_id'])) {
			$data['email_manager_store'] = $this->model_marketing_email_manager->getEmailManagerStores($this->request->get['email_manager_id']);
		} else {
			$data['email_manager_store'] = array(0);
		}		
		
		
		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($email_manager_info)) {
			$data['status'] = $email_manager_info['status'];
		} else {
			$data['status'] = 1;
		}
		if (isset($this->request->post['code'])) {
			$data['code'] = $this->request->post['code'];
		} elseif (!empty($email_manager_info)) {
			$data['code'] = $email_manager_info['code'];
		} else {
			$data['code'] = '';
		}
				
		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($email_manager_info)) {
			$data['sort_order'] = $email_manager_info['sort_order'];
		} else {
			$data['sort_order'] = '';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('marketing/email_manager_form', $data));
  	}
  	
	protected function validateForm() {
    	if (!$this->user->hasPermission('modify', 'marketing/email_manager')) {
      		$this->error['warning'] = $this->language->get('error_permission');
    	}

    	foreach ($this->request->post['email_manager_description'] as $language_id => $value) {
			
      		if ((utf8_strlen($value['short_description']) < 2) || (utf8_strlen($value['short_description']) > 64)) {
        		$this->error['short_description'][$language_id] = $this->language->get('error_short_description');
      		}
			
			if (utf8_strlen($value['content']) < 3) {
				$this->error['description'][$language_id] = $this->language->get('error_description');
			}
    	}
		
		if (!$this->error) {
	  		return true;
		} else {
	  		return false;
		}
  	}

  	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'marketing/email_manager')) {
      		$this->error['warning'] = $this->language->get('error_permission');
    	}
		
		$this->load->model('marketing/email_manager');
	
		if (!$this->error) { 
	  		return true;
		} else {
	  		return false;
		}
  	}	  
}
?>