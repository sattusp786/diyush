<?php
class ControllerMastersMetal extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('masters/metal');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('masters/metal');

		$this->getList();
	}

	public function add() {
		$this->load->language('masters/metal');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('masters/metal');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_masters_metal->addMetal($this->request->post);

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

			$this->response->redirect($this->url->link('masters/metal', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('masters/metal');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('masters/metal');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_masters_metal->editMetal($this->request->get['metal_id'], $this->request->post);

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

			$this->response->redirect($this->url->link('masters/metal', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('masters/metal');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('masters/metal');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $metal_id) {
				$this->model_masters_metal->deleteMetal($metal_id);
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

			$this->response->redirect($this->url->link('masters/metal', 'user_token=' . $this->session->data['user_token'] . $url, true));
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
			'href' => $this->url->link('masters/metal', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('masters/metal/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('masters/metal/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['metals'] = array();

		$metal_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$metal_total = $this->model_masters_metal->getTotalMetals();

		$results = $this->model_masters_metal->getMetals($metal_data);

		foreach ($results as $result) {
			$data['metals'][] = array(
				'metal_id' 		 => $result['metal_id'],
				'name'            => $result['name'],
				'code'            => $result['code'],
				'percent'         => $result['percent'],
				'price'            => $result['price'],
				'sort_order'      => $result['sort_order'],
				'edit'            => $this->url->link('masters/metal/edit', 'user_token=' . $this->session->data['user_token'] . '&metal_id=' . $result['metal_id'] . $url, true)
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

		$data['sort_name'] = $this->url->link('masters/metal', 'user_token=' . $this->session->data['user_token'] . '&sort=id.name' . $url, true);
		$data['sort_code'] = $this->url->link('masters/metal', 'user_token=' . $this->session->data['user_token'] . '&sort=i.code' . $url, true);
		$data['sort_percent'] = $this->url->link('masters/metal', 'user_token=' . $this->session->data['user_token'] . '&sort=i.percent' . $url, true);
		$data['sort_price'] = $this->url->link('masters/metal', 'user_token=' . $this->session->data['user_token'] . '&sort=i.price' . $url, true);		
		$data['sort_sort_order'] = $this->url->link('masters/metal', 'user_token=' . $this->session->data['user_token'] . '&sort=i.sort_order' . $url, true);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $metal_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('masters/metal', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($metal_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($metal_total - $this->config->get('config_limit_admin'))) ? $metal_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $metal_total, ceil($metal_total / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('masters/metal_list', $data));
	}

	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['metal_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		if (isset($this->error['metal'])) {
			$data['error_metal'] = $this->error['metal'];
		} else {
			$data['error_metal'] = array();
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
			'href' => $this->url->link('masters/metal', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['metal_id'])) {
			$data['action'] = $this->url->link('masters/metal/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('masters/metal/edit', 'user_token=' . $this->session->data['user_token'] . '&metal_id=' . $this->request->get['metal_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('masters/metal', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['metal_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$metal_info = $this->model_masters_metal->getMetal($this->request->get['metal_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['metal_description'])) {
			$data['metal_description'] = $this->request->post['metal_description'];
		} elseif (isset($this->request->get['metal_id'])) {
			$data['metal_description'] = $this->model_masters_metal->getMetalDescriptions($this->request->get['metal_id']);
		} else {
			$data['metal_description'] = array();
		}

		if (isset($this->request->post['code'])) {
			$data['code'] = $this->request->post['code'];
		} elseif (!empty($metal_info)) {
			$data['code'] = $metal_info['code'];
		} else {
			$data['code'] = '';
		}

		if (isset($this->request->post['price'])) {
			$data['price'] = $this->request->post['price'];
		} elseif (!empty($metal_info)) {
			$data['price'] = $metal_info['price'];
		} else {
			$data['price'] = '';
		}
		
		if (isset($this->request->post['feedname'])) {
			$data['feedname'] = $this->request->post['feedname'];
		} elseif (!empty($metal_info)) {
			$data['feedname'] = $metal_info['feedname'];
		} else {
			$data['feedname'] = '';
		}
		
		if (isset($this->request->post['percent'])) {
			$data['percent'] = $this->request->post['percent'];
		} elseif (!empty($metal_info)) {
			$data['percent'] = $metal_info['percent'];
		} else {
			$data['percent'] = '';
		}


		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($metal_info)) {
			$data['sort_order'] = $metal_info['sort_order'];
		} else {
			$data['sort_order'] = '';
		}
		
		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($metal_info)) {
			$data['status'] = $metal_info['status'];
		} else {
			$data['status'] = '0';
		}

		if (isset($this->request->post['metal'])) {
			$data['metals'] = $this->request->post['metal'];
		} elseif (isset($this->request->get['metal_id'])) {
			$data['metals'] = $this->model_masters_metal->getMetalDescriptions($this->request->get['metal_id']);
		} else {
			$data['metals'] = array();
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
		
		if (isset($this->request->post['metal_store'])) {
			$data['metal_store'] = $this->request->post['metal_store'];
		} elseif (isset($this->request->get['metal_id'])) {
			$data['metal_store'] = $this->model_masters_metal->getMetalStores($this->request->get['metal_id']);
		} else {
			$data['metal_store'] = array(0);
		}		

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('masters/metal_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'masters/metal')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'masters/metal')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['metal_name'])) {
			$this->load->model('masters/metal');

			$metal_data = array(
				'metal_name' => $this->request->get['metal_name'],
				'start'       => 0,
				'limit'       => 5
			);

			$metals = $this->model_masters_metal->getMetals($metal_data);

			foreach ($metals as $metal) {
				$json[] = array(
					'metal_id' => $metal['metal_id'],
					'name'      => strip_tags(html_entity_decode($metal['group'] . ' &gt; ' . $metal['name'], ENT_QUOTES, 'UTF-8'))
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
}