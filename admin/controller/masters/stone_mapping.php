<?php
class ControllerMastersStoneMapping extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('masters/stone_mapping');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('masters/stone_mapping');

		$this->getList();
	}

	public function add() {
		$this->load->language('masters/stone_mapping');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('masters/stone_mapping');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_masters_stone_mapping->addStoneMapping($this->request->post);

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

			$this->response->redirect($this->url->link('masters/stone_mapping', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('masters/stone_mapping');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('masters/stone_mapping');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_masters_stone_mapping->editStoneMapping($this->request->get['stone_mapping_id'], $this->request->post);

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

			$this->response->redirect($this->url->link('masters/stone_mapping', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('masters/stone_mapping');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('masters/stone_mapping');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $stone_mapping_id) {
				$this->model_masters_stone_mapping->deleteStoneMapping($stone_mapping_id);
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

			$this->response->redirect($this->url->link('masters/stone_mapping', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function getList() {
		$this->load->language('masters/stone_mapping');
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
			'href' => $this->url->link('masters/stone_mapping', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['export'] = $this->url->link('masters/stone_mapping/export', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL');
		
		$data['add'] = $this->url->link('masters/stone_mapping/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('masters/stone_mapping/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['copy'] = $this->url->link('masters/stone_mapping/copy', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['user_token'] = $this->session->data['user_token'];
		
		$data['stone_mappings'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$stone_mapping_total = $this->model_masters_stone_mapping->getTotalStoneMappings();

		$results = $this->model_masters_stone_mapping->getStoneMappings($filter_data);

		foreach ($results as $result) {
			$data['stone_mappings'][] = array(
				'stone_mapping_id'  	=> $result['stone_mapping_id'],
				'name'       			=> $result['name'],
				'certificate'       	=> $result['certificate'],
				'position'       		=> $result['position'],
				'markup_percent'       	=> $result['markup_percent'],
				'markup_fixed'       	=> $result['markup_fixed'],
				'edit'       			=> $this->url->link('masters/stone_mapping/edit', 'user_token=' . $this->session->data['user_token'] . '&stone_mapping_id=' . $result['stone_mapping_id'] . $url, true)
			);
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

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link('masters/stone_mapping', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);
		$data['sort_certificate'] = $this->url->link('masters/stone_mapping', 'user_token=' . $this->session->data['user_token'] . '&sort=certificate' . $url, true);
		$data['sort_position'] = $this->url->link('masters/stone_mapping', 'user_token=' . $this->session->data['user_token'] . '&sort=position' . $url, true);
		$data['sort_markup_percent'] = $this->url->link('masters/stone_mapping', 'user_token=' . $this->session->data['user_token'] . '&sort=markup_percent' . $url, true);
		$data['sort_markup_fixed'] = $this->url->link('masters/stone_mapping', 'user_token=' . $this->session->data['user_token'] . '&sort=markup_fixed' . $url, true);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $stone_mapping_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('masters/stone_mapping', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($stone_mapping_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($stone_mapping_total - $this->config->get('config_limit_admin'))) ? $stone_mapping_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $stone_mapping_total, ceil($stone_mapping_total / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('masters/stone_mapping_list', $data));
	}

	protected function getForm() {
		$this->load->language('masters/stone_mapping');

		$data['text_form'] = !isset($this->request->get['stone_mapping_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->get['stone_mapping_id'])) {
			$data['stone_mapping_id'] = $this->request->get['stone_mapping_id'];
		} else {
			$data['stone_mapping_id'] = 0;
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		if (isset($this->error['certificate'])) {
			$data['error_certificate'] = $this->error['certificate'];
		} else {
			$data['error_certificate'] = '';
		}

		if (isset($this->error['position'])) {
			$data['error_position'] = $this->error['position'];
		} else {
			$data['error_position'] = '';
		}

		if (isset($this->error['markup_percent'])) {
			$data['error_markup_percent'] = $this->error['markup_percent'];
		} else {
			$data['error_markup_percent'] = '';
		}

		if (isset($this->error['markup_fixed'])) {
			$data['error_markup_fixed'] = $this->error['markup_fixed'];
		} else {
			$data['error_markup_fixed'] = '';
		}

		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('masters/stone_mapping', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['stone_mapping_id'])) {
			$data['action'] = $this->url->link('masters/stone_mapping/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('masters/stone_mapping/edit', 'user_token=' . $this->session->data['user_token'] . '&stone_mapping_id=' . $this->request->get['stone_mapping_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('masters/stone_mapping', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->get['stone_mapping_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
      		$stone_mapping_info = $this->model_masters_stone_mapping->getStoneMappingValues($this->request->get['stone_mapping_id']);
    	}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($stone_mapping_info)) {
			$data['name'] = $stone_mapping_info['name'];
		} else {
			$data['name'] = '';
		}

		if (isset($this->request->post['certificate'])) {
			$data['certificate'] = $this->request->post['certificate'];
		} elseif (!empty($stone_mapping_info)) {
			$data['certificate'] = $stone_mapping_info['certificate'];
		} else {
			$data['certificate'] = '';
		}

		if (isset($this->request->post['position'])) {
			$data['position'] = $this->request->post['position'];
		} elseif (!empty($stone_mapping_info)) {
			$data['position'] = $stone_mapping_info['position'];
		} else {
			$data['position'] = '';
		}

		if (isset($this->request->post['markup_percent'])) {
			$data['markup_percent'] = $this->request->post['markup_percent'];
		} elseif (!empty($stone_mapping_info)) {
			$data['markup_percent'] = $stone_mapping_info['markup_percent'];
		} else {
			$data['markup_percent'] = '';
		}

		if (isset($this->request->post['markup_fixed'])) {
			$data['markup_fixed'] = $this->request->post['markup_fixed'];
		} elseif (!empty($stone_mapping_info)) {
			$data['markup_fixed'] = $stone_mapping_info['markup_fixed'];
		} else {
			$data['markup_fixed'] = '';
		}

		
		if (isset($this->request->post['option_value'])) {
			$option_values = $this->request->post['option_value'];
		} elseif (isset($this->request->get['stone_mapping_id'])) {
			$option_values = $this->model_masters_stone_mapping->getStoneMappingValueDescriptions($this->request->get['stone_mapping_id']);
		} else {
			$option_values = array();
		}
		
		$data['option_values'] = array();
		// echo"<pre>";print_r($option_values);echo"</pre>";
		foreach ($option_values as $option_value) {
			
			$data['option_values'][] = array(
				'stone_mapping_value_id'    => $option_value['stone_mapping_value_id'],
				'stone_mapping_id'					=> $option_value['stone_mapping_id'],
				'option_value'					    => $option_value['option_value'],
				'option_value_mapping'				=> $option_value['option_value_mapping']				
			);
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('masters/stone_mapping_form', $data));
	}

	public function copy() {
	
		$this->language->load('masters/stone_mapping');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('masters/stone_mapping');
		
		if (isset($this->request->post['selected']) && $this->validateCopy()) {
			foreach ($this->request->post['selected'] as $stone_mapping_id) {
			$this->model_masters_stone_mapping->copyStoneMapping($stone_mapping_id);
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

			$this->response->redirect($this->url->link('masters/stone_mapping', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
    }

	 protected function validateCopy() {
		if (!$this->user->hasPermission('modify', 'masters/stone_mapping')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
    }
	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'masters/stone_mapping')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'masters/stone_mapping')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}	
}