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
		$this->load->language('masters/metal_price');
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
			'href' => $this->url->link('masters/metal_price', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['export'] = $this->url->link('masters/metal_price/export', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL');
		
		$data['add'] = $this->url->link('masters/metal_price/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('masters/metal_price/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['user_token'] = $this->session->data['user_token'];
		
		$data['metal_prices'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$metal_price_total = $this->model_masters_metal_price->getTotalMetalPrices();

		$results = $this->model_masters_metal_price->getMetalPrices($filter_data);

		foreach ($results as $result) {
			$data['metal_prices'][] = array(
				'metal_price_id'  => $result['metal_price_id'],
				'name'       => $result['name'],
				'code'       => $result['code'],
				'price'   	=> $result['price'],
				'status'     => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'edit'       => $this->url->link('masters/metal_price/edit', 'user_token=' . $this->session->data['user_token'] . '&metal_price_id=' . $result['metal_price_id'] . $url, true)
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

		$data['sort_name'] = $this->url->link('masters/metal_price', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);
		$data['sort_code'] = $this->url->link('masters/metal_price', 'user_token=' . $this->session->data['user_token'] . '&sort=code' . $url, true);
		$data['sort_price'] = $this->url->link('masters/metal_price', 'user_token=' . $this->session->data['user_token'] . '&sort=price' . $url, true);
		$data['sort_status'] = $this->url->link('masters/metal_price', 'user_token=' . $this->session->data['user_token'] . '&sort=status' . $url, true);

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
		$this->load->language('masters/metal_price');

		$data['text_form'] = !isset($this->request->get['metal_price_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->get['metal_price_id'])) {
			$data['metal_price_id'] = $this->request->get['metal_price_id'];
		} else {
			$data['metal_price_id'] = 0;
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

		if (isset($this->error['code'])) {
			$data['error_code'] = $this->error['code'];
		} else {
			$data['error_code'] = '';
		}

		if (isset($this->error['price'])) {
			$data['error_price'] = $this->error['price'];
		} else {
			$data['error_price'] = '';
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
			'href' => $this->url->link('masters/metal_price', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['metal_price_id'])) {
			$data['action'] = $this->url->link('masters/metal_price/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('masters/metal_price/edit', 'user_token=' . $this->session->data['user_token'] . '&metal_price_id=' . $this->request->get['metal_price_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('masters/metal_price', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['metal_price_id']) && (!$this->request->server['REQUEST_METHOD'] != 'POST')) {
			$metal_price_info = $this->model_masters_metal_price->getMetalPrice($this->request->get['metal_price_id']);
		}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($metal_price_info)) {
			$data['name'] = $metal_price_info['name'];
		} else {
			$data['name'] = '';
		}

		if (isset($this->request->post['code'])) {
			$data['code'] = $this->request->post['code'];
		} elseif (!empty($metal_price_info)) {
			$data['code'] = $metal_price_info['code'];
		} else {
			$data['code'] = '';
		}

		if (isset($this->request->post['price'])) {
			$data['price'] = $this->request->post['price'];
		} elseif (!empty($metal_price_info)) {
			$data['price'] = $metal_price_info['price'];
		} else {
			$data['price'] = '';
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($metal_price_info)) {
			$data['status'] = $metal_price_info['status'];
		} else {
			$data['status'] = true;
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

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 128)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if ((utf8_strlen($this->request->post['code']) < 3) || (utf8_strlen($this->request->post['code']) > 10)) {
			$this->error['code'] = $this->language->get('error_code');
		}

		if ((utf8_strlen($this->request->post['price']) < 1) || (utf8_strlen($this->request->post['price']) > 10)) {
			$this->error['price'] = $this->language->get('error_price');
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'masters/metal_price')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
	
	public function import() {

		$this->load->language('masters/metal_price');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('masters/metal_price');

		//&& $this->validateImport()
		if (($this->request->server['REQUEST_METHOD'] == 'POST') ) {

			$this->model_masters_metal_price->importMetalPrice($this->request->files);
			
			$this->session->data['success'] = $this->language->get('text_success');
			
			$url = '';
			
			/*
			if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' .$this->request->get['filter_model'];
			}
			if (isset($this->request->get['filter_title'])) {
				$url .= '&filter_title=' .$this->request->get['filter_title'];
			}
			
			if (isset($this->request->get['filter_category'])) {
				$url .= '&filter_category=' .$this->request->get['filter_category'];
			}
			*/
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

		if (isset($this->error['import_file'])) {
			$this->error['warning'] = $this->error['import_file'];
		}

		$this->getList();
	}
	
	public function export()
    {
        $this->load->model('masters/metal_price');
        
        $data = array(
            'sort' => 'code',
            'order' => 'ASC'
        );
        
        $metal_price = "Metal Price ID,Name,Code,Price,Status\n";
        $results     = $this->model_masters_metal_price->getMetalPriceExport($data);		
		
			if($results){
				foreach ($results as $result) {
					
					$metal_price .= $this->db->escape($result['metal_price_id']) . "," . $this->db->escape($result['name']) . "," . $this->db->escape($result['code']) . "," . $this->db->escape($result['price']) . "," . $this->db->escape($result['status']) . "\n";
				}
			}
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Length: " . strlen($metal_price));
			header("Content-type: text/x-csv");
			header("Content-Disposition: attachment; filename=metal_price.csv");
			echo $metal_price;
			exit;
    }
}