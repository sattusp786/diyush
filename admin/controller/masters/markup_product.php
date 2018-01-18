<?php
class ControllerMastersMarkupProduct extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('masters/markup_product');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('masters/markup_product');

		$this->getList();
	}

	public function add() {
		$this->load->language('masters/markup_product');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('masters/markup_product');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_masters_markup_product->addProductMarkup($this->request->post);

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

			$this->response->redirect($this->url->link('masters/markup_product', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('masters/markup_product');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('masters/markup_product');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_masters_markup_product->editProductMarkup($this->request->get['markup_product_id'], $this->request->post);

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

			$this->response->redirect($this->url->link('masters/markup_product', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('masters/markup_product');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('masters/markup_product');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $markup_product_id) {
				$this->model_masters_markup_product->deleteProductMarkup($markup_product_id);
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

			$this->response->redirect($this->url->link('masters/markup_product', 'user_token=' . $this->session->data['user_token'] . $url, true));
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
			'href' => $this->url->link('masters/markup_product', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('masters/markup_product/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('masters/markup_product/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['markup_products'] = array();

		$markup_product_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$markup_product_total = $this->model_masters_markup_product->getTotalProductMarkups();

		$results = $this->model_masters_markup_product->getProductMarkups($markup_product_data);

		foreach ($results as $result) {
			$data['markup_products'][] = array(
				'markup_product_id' 		 => $result['markup_product_id'],
				'title'            => $result['title'],
				'code'            => $result['code'],
				'markup'            => $result['markup'],
				'status'            => $result['status'],
				'edit'            => $this->url->link('masters/markup_product/edit', 'user_token=' . $this->session->data['user_token'] . '&markup_product_id=' . $result['markup_product_id'] . $url, true)
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

		$data['sort_title'] = $this->url->link('masters/markup_product', 'user_token=' . $this->session->data['user_token'] . '&sort=title' . $url, true);
		$data['sort_code'] = $this->url->link('masters/markup_product', 'user_token=' . $this->session->data['user_token'] . '&sort=i.code' . $url, true);
		$data['sort_markup'] = $this->url->link('masters/markup_product', 'user_token=' . $this->session->data['user_token'] . '&sort=markup' . $url, true);	

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $markup_product_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('masters/markup_product', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($markup_product_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($markup_product_total - $this->config->get('config_limit_admin'))) ? $markup_product_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $markup_product_total, ceil($markup_product_total / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('masters/markup_product_list', $data));
	}

	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['markup_product_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		if (isset($this->error['markup_product'])) {
			$data['error_markup_product'] = $this->error['markup_product'];
		} else {
			$data['error_markup_product'] = array();
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
			'href' => $this->url->link('masters/markup_product', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['markup_product_id'])) {
			$data['action'] = $this->url->link('masters/markup_product/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('masters/markup_product/edit', 'user_token=' . $this->session->data['user_token'] . '&markup_product_id=' . $this->request->get['markup_product_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('masters/markup_product', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['markup_product_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$markup_product_info = $this->model_masters_markup_product->getProductMarkup($this->request->get['markup_product_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->post['code'])) {
			$data['code'] = $this->request->post['code'];
		} elseif (!empty($markup_product_info)) {
			$data['code'] = $markup_product_info['code'];
		} else {
			$data['code'] = '';
		}

		if (isset($this->request->post['title'])) {
			$data['title'] = $this->request->post['title'];
		} elseif (!empty($markup_product_info)) {
			$data['title'] = $markup_product_info['title'];
		} else {
			$data['title'] = '';
		}
		
		if (isset($this->request->post['markup'])) {
			$data['markup'] = $this->request->post['markup'];
		} elseif (!empty($markup_product_info)) {
			$data['markup'] = $markup_product_info['markup'];
		} else {
			$data['markup'] = '';
		}	
		
		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($markup_product_info)) {
			$data['status'] = $markup_product_info['status'];
		} else {
			$data['status'] = '0';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('masters/markup_product_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'masters/markup_product')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'masters/markup_product')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['markup_product_name'])) {
			$this->load->model('masters/markup_product');

			$markup_product_data = array(
				'markup_product_name' => $this->request->get['markup_product_name'],
				'start'       => 0,
				'limit'       => 5
			);

			$markup_products = $this->model_masters_markup_product->getMetals($markup_product_data);

			foreach ($markup_products as $markup_product) {
				$json[] = array(
					'markup_product_id' => $markup_product['markup_product_id'],
					'name'      => strip_tags(html_entity_decode($markup_product['group'] . ' &gt; ' . $markup_product['name'], ENT_QUOTES, 'UTF-8'))
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