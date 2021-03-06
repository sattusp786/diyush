<?php
class ControllerMastersMetalPrice extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('masters/metal_price');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('masters/metal_price');

		$this->getList();
	}

	public function add() {
		$this->load->language('masters/metal_price');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('masters/metal_price');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_masters_metal_price->addMetalPrice($this->request->post);

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

			$this->response->redirect($this->url->link('masters/metal_price', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('masters/metal_price');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('masters/metal_price');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_masters_metal_price->editMetalPrice($this->request->get['metal_price_id'], $this->request->post);

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

			$this->response->redirect($this->url->link('masters/metal_price', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('masters/metal_price');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('masters/metal_price');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $metal_price_id) {
				$this->model_masters_metal_price->deleteMetalPrice($metal_price_id);
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

			$this->response->redirect($this->url->link('masters/metal_price', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function getList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'fgd.name';
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
			'href' => $this->url->link('masters/metal_price', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('masters/metal_price/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('masters/metal_price/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['metal_prices'] = array();

		$metal_price_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$metal_price_total = $this->model_masters_metal_price->getTotalMetalPrices();
	
		$results = $this->model_masters_metal_price->getMetalPrices($metal_price_data);

		foreach ($results as $result) {
			
			$data['metal_prices'][] = array(
				'metal_price_id' => $result['metal_price_id'],
				'name'			 => $result['name'],
				'option_value' => $result['option_value'],
				'code'			 => $result['code'],
				'purity'		 => $result['purity'],
				'percent'		 => $result['percent'],
				'gravity'		 => $result['gravity'],
				'price'			 => $result['price'],
				'sort_order'     => $result['sort_order'],
				'selected'       => isset($this->request->post['selected']) && in_array($result['metal_price_id'], $this->request->post['selected']),
				'edit' 			 => $this->url->link('masters/metal_price/edit', 'user_token=' . $this->session->data['user_token'] . '&metal_price_id=' . $result['metal_price_id'] . $url, 'SSL')
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
		
		$data['sort_name'] = $this->url->link('masters/metal_price', 'user_token=' . $this->session->data['user_token'] . '&sort=id.name' . $url, 'SSL');
		$data['sort_code'] = $this->url->link('masters/metal_price', 'user_token=' . $this->session->data['user_token'] . '&sort=i.code' . $url, 'SSL');
		$data['sort_purity'] = $this->url->link('masters/metal_price', 'user_token=' . $this->session->data['user_token'] . '&sort=i.purity' . $url, 'SSL');
		$data['sort_percent'] = $this->url->link('masters/metal_price', 'user_token=' . $this->session->data['user_token'] . '&sort=i.percent' . $url, 'SSL');
		$data['sort_price'] = $this->url->link('masters/metal_price', 'user_token=' . $this->session->data['user_token'] . '&sort=i.price' . $url, 'SSL');
		$data['sort_gravity'] = $this->url->link('masters/metal_price', 'user_token=' . $this->session->data['user_token'] . '&sort=i.gravity' . $url, 'SSL');
		$data['sort_sort_order'] = $this->url->link('masters/metal_price', 'user_token=' . $this->session->data['user_token'] . '&sort=i.sort_order' . $url, 'SSL');
		$data['sort_option_value'] = $this->url->link('masters/metal_price', 'user_token=' . $this->session->data['user_token'] . '&sort=option_value_id' . $url, 'SSL');

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $metal_price_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('masters/metal_price', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($metal_price_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($metal_price_total - $this->config->get('config_limit_admin'))) ? $metal_price_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $metal_price_total, ceil($metal_price_total / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('masters/metal_price_list', $data));
	}

	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['metal_price_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['metal_price'])) {
			$data['error_metal_price'] = $this->error['metal_price'];
		} else {
			$data['error_metal_price'] = array();
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
			'href' => $this->url->link('masters/metal_price', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['metal_price_id'])) {
			$data['action'] = $this->url->link('masters/metal_price/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('masters/metal_price/edit', 'user_token=' . $this->session->data['user_token'] . '&metal_price_id=' . $this->request->get['metal_price_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('masters/metal_price', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['metal_price_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$metal_price_info = $this->model_masters_metal_price->getMetalPricesInfo($this->request->get['metal_price_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		$data['optionvalues'] = $this->model_masters_metal_price->getOptionValue();
		
		if (isset($this->request->post['metal_price_description'])) {
			$data['metal_price_description'] = $this->request->post['metal_price_description'];
		} elseif (isset($this->request->get['metal_price_id'])) {
			$data['metal_price_description'] = $this->model_masters_metal_price->getMetalPriceDescriptions($this->request->get['metal_price_id']);
		} else {
			$data['metal_price_description'] = array();
		}
		
		if (isset($this->request->post['code'])) {
			$data['code'] = $this->request->post['code'];
		} elseif (!empty($metal_price_info)) {
			$data['code'] = $metal_price_info['code'];
		} else {
			$data['code'] = '';
		}
		
		if (isset($this->request->post['option_value'])) {
			$data['option_value'] = $this->request->post['option_value'];
		} elseif (!empty($metal_price_info)) {
			$data['option_value'] = $metal_price_info['option_value_id'];
		} else {
			$data['option_value'] = '';
		}


		if (isset($this->request->post['purity'])) {
			$data['purity'] = $this->request->post['purity'];
		} elseif (!empty($metal_price_info)) {
			$data['purity'] = $metal_price_info['purity'];
		} else {
			$data['purity'] = '';
		}
		
		if (isset($this->request->post['price'])) {
			$data['price'] = $this->request->post['price'];
		} elseif (!empty($metal_price_info)) {
			$data['price'] = $metal_price_info['price'];
		} else {
			$data['price'] = '';
		}
		
		if (isset($this->request->post['gravity'])) {
			$data['gravity'] = $this->request->post['gravity'];
		} elseif (!empty($metal_price_info)) {
			$data['gravity'] = $metal_price_info['gravity'];
		} else {
			$data['gravity'] = '';
		}
		

		if (isset($this->request->post['percent'])) {
			$data['percent'] = $this->request->post['percent'];
		} elseif (!empty($metal_price_info)) {
			$data['percent'] = $metal_price_info['percent'];
		} else {
			$data['percent'] = 0;
		}

		if (isset($this->request->post['markup_rate'])) {
			$data['markup_rate'] = $this->request->post['markup_rate'];
		} elseif (!empty($metal_price_info)) {
			$data['markup_rate'] = $metal_price_info['markup_rate'];
		} else {
			$data['markup_rate'] = 0;
		}

		$this->load->model('masters/metal');
		
    	if (isset($this->request->post['metal_id'])) {
      		$data['metal_id'] = $this->request->post['metal_id'];
			
		} elseif (!empty($metal_price_info)) {
			$data['metal_id'] = $metal_price_info['metal_id'];
		} else {
      		$data['metal_id'] = 0;
    	} 		
		
    	if (isset($this->request->post['metal'])) {
      		$data['metal'] = $this->request->post['metal'];
		} elseif (!empty($metal_price_info)) {
			$metal_info = $this->model_masters_metal->getMetal($metal_price_info['metal_id']);
			
			if ($metal_info) {		
				$data['metal'] = $metal_info['name'];
			} else {
				$data['metal'] = '';
			}	
		} else {
      		$data['metal'] = '';
    	} 

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
		
		if (isset($this->request->post['metal_price_store'])) {
			$data['metal_price_store'] = $this->request->post['metal_price_store'];
		} elseif (isset($this->request->get['metal_price_id'])) {
			$data['metal_price_store'] = $this->model_masters_metal_price->getMetalPriceStores($this->request->get['metal_price_id']);
		} else {
			$data['metal_price_store'] = array(0);
		}		
		
		
		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($metal_price_info)) {
			$data['status'] = $metal_price_info['status'];
		} else {
			$data['status'] = 1;
		}
				
		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($metal_price_info)) {
			$data['sort_order'] = $metal_price_info['sort_order'];
		} else {
			$data['sort_order'] = '';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('masters/metal_price_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'masters/metal_price')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'masters/metal_price')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}