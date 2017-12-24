<?php
class ControllerMastersStonePrice extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('masters/stone_price');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('masters/stone_price');

		$this->getList();
	}

	public function add() {
		$this->load->language('masters/stone_price');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('masters/stone_price');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_masters_stone_price->addStonePrice($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			
			if (isset($this->request->get['filter_stone'])) {
				$url .= '&filter_stone=' . $this->request->get['filter_stone'];
			}
			
			if (isset($this->request->get['filter_shape'])) {
				$url .= '&filter_shape=' . $this->request->get['filter_shape'];
			}
			
			if (isset($this->request->get['filter_crt_from'])) {
				$url .= '&filter_crt_from=' . $this->request->get['filter_crt_from'];
			}
			
			if (isset($this->request->get['filter_crt_to'])) {
				$url .= '&filter_crt_to=' . $this->request->get['filter_crt_to'];
			}
			
			if (isset($this->request->get['filter_weight'])) {
				$url .= '&filter_weight=' . $this->request->get['filter_weight'];
			}
			
			if (isset($this->request->get['filter_clarity'])) {
				$url .= '&filter_clarity=' . $this->request->get['filter_clarity'];
			}
			
			if (isset($this->request->get['filter_color'])) {
				$url .= '&filter_color=' . $this->request->get['filter_color'];
			}
			
			if (isset($this->request->get['filter_lab'])) {
				$url .= '&filter_lab=' . $this->request->get['filter_lab'];
			}
			
			if (isset($this->request->get['filter_cut'])) {
				$url .= '&filter_cut=' . $this->request->get['filter_cut'];
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

			$this->response->redirect($this->url->link('masters/stone_price', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('masters/stone_price');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('masters/stone_price');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_masters_stone_price->editStonePrice($this->request->get['stone_price_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			
			if (isset($this->request->get['filter_stone'])) {
				$url .= '&filter_stone=' . $this->request->get['filter_stone'];
			}
			
			if (isset($this->request->get['filter_shape'])) {
				$url .= '&filter_shape=' . $this->request->get['filter_shape'];
			}
			
			if (isset($this->request->get['filter_crt_from'])) {
				$url .= '&filter_crt_from=' . $this->request->get['filter_crt_from'];
			}
			
			if (isset($this->request->get['filter_crt_to'])) {
				$url .= '&filter_crt_to=' . $this->request->get['filter_crt_to'];
			}
			
			if (isset($this->request->get['filter_weight'])) {
				$url .= '&filter_weight=' . $this->request->get['filter_weight'];
			}
			
			if (isset($this->request->get['filter_clarity'])) {
				$url .= '&filter_clarity=' . $this->request->get['filter_clarity'];
			}
			
			if (isset($this->request->get['filter_color'])) {
				$url .= '&filter_color=' . $this->request->get['filter_color'];
			}
			
			if (isset($this->request->get['filter_lab'])) {
				$url .= '&filter_lab=' . $this->request->get['filter_lab'];
			}
			
			if (isset($this->request->get['filter_cut'])) {
				$url .= '&filter_cut=' . $this->request->get['filter_cut'];
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

			$this->response->redirect($this->url->link('masters/stone_price', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('masters/stone_price');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('masters/stone_price');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $stone_price_id) {
				$this->model_masters_stone_price->deleteStonePrice($stone_price_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_stone'])) {
				$url .= '&filter_stone=' . $this->request->get['filter_stone'];
			}
			
			if (isset($this->request->get['filter_shape'])) {
				$url .= '&filter_shape=' . $this->request->get['filter_shape'];
			}
			
			if (isset($this->request->get['filter_crt_from'])) {
				$url .= '&filter_crt_from=' . $this->request->get['filter_crt_from'];
			}
			
			if (isset($this->request->get['filter_crt_to'])) {
				$url .= '&filter_crt_to=' . $this->request->get['filter_crt_to'];
			}
			
			if (isset($this->request->get['filter_weight'])) {
				$url .= '&filter_weight=' . $this->request->get['filter_weight'];
			}
			
			if (isset($this->request->get['filter_clarity'])) {
				$url .= '&filter_clarity=' . $this->request->get['filter_clarity'];
			}
			
			if (isset($this->request->get['filter_color'])) {
				$url .= '&filter_color=' . $this->request->get['filter_color'];
			}
			
			if (isset($this->request->get['filter_lab'])) {
				$url .= '&filter_lab=' . $this->request->get['filter_lab'];
			}
			
			if (isset($this->request->get['filter_cut'])) {
				$url .= '&filter_cut=' . $this->request->get['filter_cut'];
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

			$this->response->redirect($this->url->link('masters/stone_price', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function getList() {
		$this->load->language('masters/stone_price');
		
		if (isset($this->request->get['filter_stone'])) {
			$filter_stone = $this->request->get['filter_stone'];
		} else {
			$filter_stone = '';
		}
		
		if (isset($this->request->get['filter_shape'])) {
			$filter_shape = $this->request->get['filter_shape'];
		} else {
			$filter_shape = '';
		}
		
		if (isset($this->request->get['filter_crt_from'])) {
			$filter_crt_from = $this->request->get['filter_crt_from'];
		} else {
			$filter_crt_from = '';
		}
		
		if (isset($this->request->get['filter_crt_to'])) {
			$filter_crt_to = $this->request->get['filter_crt_to'];
		} else {
			$filter_crt_to = '';
		}
		
		if (isset($this->request->get['filter_weight'])) {
			$filter_weight = $this->request->get['filter_weight'];
		} else {
			$filter_weight = '';
		}
		
		if (isset($this->request->get['filter_clarity'])) {
			$filter_clarity = $this->request->get['filter_clarity'];
		} else {
			$filter_clarity = '';
		}
		
		if (isset($this->request->get['filter_color'])) {
			$filter_color = $this->request->get['filter_color'];
		} else {
			$filter_color = '';
		}
		
		if (isset($this->request->get['filter_lab'])) {
			$filter_lab = $this->request->get['filter_lab'];
		} else {
			$filter_lab = '';
		}
		
		if (isset($this->request->get['filter_cut'])) {
			$filter_cut = $this->request->get['filter_cut'];
		} else {
			$filter_cut = '';
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

		if (isset($this->request->get['filter_stone'])) {
			$url .= '&filter_stone=' . $this->request->get['filter_stone'];
		}
		
		if (isset($this->request->get['filter_shape'])) {
			$url .= '&filter_shape=' . $this->request->get['filter_shape'];
		}
		
		if (isset($this->request->get['filter_crt_from'])) {
			$url .= '&filter_crt_from=' . $this->request->get['filter_crt_from'];
		}
		
		if (isset($this->request->get['filter_crt_to'])) {
			$url .= '&filter_crt_to=' . $this->request->get['filter_crt_to'];
		}
		
		if (isset($this->request->get['filter_weight'])) {
			$url .= '&filter_weight=' . $this->request->get['filter_weight'];
		}
		
		if (isset($this->request->get['filter_clarity'])) {
			$url .= '&filter_clarity=' . $this->request->get['filter_clarity'];
		}
		
		if (isset($this->request->get['filter_color'])) {
			$url .= '&filter_color=' . $this->request->get['filter_color'];
		}
		
		if (isset($this->request->get['filter_lab'])) {
			$url .= '&filter_lab=' . $this->request->get['filter_lab'];
		}
		
		if (isset($this->request->get['filter_cut'])) {
			$url .= '&filter_cut=' . $this->request->get['filter_cut'];
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
			'href' => $this->url->link('masters/stone_price', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['export'] = $this->url->link('masters/stone_price/export', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL');
		
		$data['add'] = $this->url->link('masters/stone_price/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('masters/stone_price/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['user_token'] = $this->session->data['user_token'];
		
		$data['stone_prices'] = array();

		$filter_data = array(
			'filter_stone'	  => $filter_stone,
			'filter_shape'	  => $filter_shape,
			'filter_crt_from' => $filter_crt_from,
			'filter_crt_to'	  => $filter_crt_to,
			'filter_weight'	  => $filter_weight,
			'filter_clarity'  => $filter_clarity,
			'filter_color'	  => $filter_color,
			'filter_lab'	  => $filter_lab,
			'filter_cut'	  => $filter_cut,
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$stone_price_total = $this->model_masters_stone_price->getTotalStonePrices();

		$results = $this->model_masters_stone_price->getStonePrices($filter_data);

		foreach ($results as $result) {
			$data['stone_prices'][] = array(
				'stone_price_id'  	 => $result['stone_price_id'],
				'stone'       		=> $result['stone'],
				'shape'       		=> $result['shape'],
				'crt_from'       => $result['crt_from'],
				'crt_to'       => $result['crt_to'],
				'weight'       => $result['weight'],
				'clarity'       => $result['clarity'],
				'color'       => $result['color'],
				'lab'       => $result['lab'],
				'cut'       => $result['cut'],
				'polish'       => $result['polish'],
				'symmetry'       => $result['symmetry'],
				'fluorescence'       => $result['fluorescence'],
				'intensity'       => $result['intensity'],
				'carat_price'   	=> $result['carat_price'],
				'total_price'   	=> $result['total_price'],
				'sprice'   	=> $result['sprice'],
				'mprice'   	=> $result['mprice'],
				'edit'       => $this->url->link('masters/stone_price/edit', 'user_token=' . $this->session->data['user_token'] . '&stone_price_id=' . $result['stone_price_id'] . $url, true)
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

		if (isset($this->request->get['filter_stone'])) {
			$url .= '&filter_stone=' . $this->request->get['filter_stone'];
		}
		
		if (isset($this->request->get['filter_shape'])) {
			$url .= '&filter_shape=' . $this->request->get['filter_shape'];
		}
		
		if (isset($this->request->get['filter_crt_from'])) {
			$url .= '&filter_crt_from=' . $this->request->get['filter_crt_from'];
		}
		
		if (isset($this->request->get['filter_crt_to'])) {
			$url .= '&filter_crt_to=' . $this->request->get['filter_crt_to'];
		}
		
		if (isset($this->request->get['filter_weight'])) {
			$url .= '&filter_weight=' . $this->request->get['filter_weight'];
		}
		
		if (isset($this->request->get['filter_clarity'])) {
			$url .= '&filter_clarity=' . $this->request->get['filter_clarity'];
		}
		
		if (isset($this->request->get['filter_color'])) {
			$url .= '&filter_color=' . $this->request->get['filter_color'];
		}
		
		if (isset($this->request->get['filter_lab'])) {
			$url .= '&filter_lab=' . $this->request->get['filter_lab'];
		}
		
		if (isset($this->request->get['filter_cut'])) {
			$url .= '&filter_cut=' . $this->request->get['filter_cut'];
		}
		
		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_stone'] = $this->url->link('masters/stone_price', 'user_token=' . $this->session->data['user_token'] . '&sort=stone' . $url, true);
		$data['sort_shape'] = $this->url->link('masters/stone_price', 'user_token=' . $this->session->data['user_token'] . '&sort=shape' . $url, true);
		$data['sort_crt_from'] = $this->url->link('masters/stone_price', 'user_token=' . $this->session->data['user_token'] . '&sort=crt_from' . $url, true);
		$data['sort_crt_to'] = $this->url->link('masters/stone_price', 'user_token=' . $this->session->data['user_token'] . '&sort=crt_to' . $url, true);
		$data['sort_weight'] = $this->url->link('masters/stone_price', 'user_token=' . $this->session->data['user_token'] . '&sort=weight' . $url, true);
		$data['sort_clarity'] = $this->url->link('masters/stone_price', 'user_token=' . $this->session->data['user_token'] . '&sort=clarity' . $url, true);
		$data['sort_color'] = $this->url->link('masters/stone_price', 'user_token=' . $this->session->data['user_token'] . '&sort=color' . $url, true);
		$data['sort_lab'] = $this->url->link('masters/stone_price', 'user_token=' . $this->session->data['user_token'] . '&sort=lab' . $url, true);
		$data['sort_cut'] = $this->url->link('masters/stone_price', 'user_token=' . $this->session->data['user_token'] . '&sort=cut' . $url, true);
		$data['sort_polish'] = $this->url->link('masters/stone_price', 'user_token=' . $this->session->data['user_token'] . '&sort=polish' . $url, true);
		$data['sort_symmetry'] = $this->url->link('masters/stone_price', 'user_token=' . $this->session->data['user_token'] . '&sort=symmetry' . $url, true);
		$data['sort_fluorescence'] = $this->url->link('masters/stone_price', 'user_token=' . $this->session->data['user_token'] . '&sort=fluorescence' . $url, true);
		$data['sort_intensity'] = $this->url->link('masters/stone_price', 'user_token=' . $this->session->data['user_token'] . '&sort=intensity' . $url, true);
		$data['sort_carat_price'] = $this->url->link('masters/stone_price', 'user_token=' . $this->session->data['user_token'] . '&sort=carat_price' . $url, true);
		$data['sort_total_price'] = $this->url->link('masters/stone_price', 'user_token=' . $this->session->data['user_token'] . '&sort=total_price' . $url, true);
		$data['sort_sprice'] = $this->url->link('masters/stone_price', 'user_token=' . $this->session->data['user_token'] . '&sort=sprice' . $url, true);
		$data['sort_mprice'] = $this->url->link('masters/stone_price', 'user_token=' . $this->session->data['user_token'] . '&sort=mprice' . $url, true);
		$data['sort_status'] = $this->url->link('masters/stone_price', 'user_token=' . $this->session->data['user_token'] . '&sort=status' . $url, true);

		$url = '';

		if (isset($this->request->get['filter_stone'])) {
			$url .= '&filter_stone=' . $this->request->get['filter_stone'];
		}
		
		if (isset($this->request->get['filter_shape'])) {
			$url .= '&filter_shape=' . $this->request->get['filter_shape'];
		}
		
		if (isset($this->request->get['filter_crt_from'])) {
			$url .= '&filter_crt_from=' . $this->request->get['filter_crt_from'];
		}
		
		if (isset($this->request->get['filter_crt_to'])) {
			$url .= '&filter_crt_to=' . $this->request->get['filter_crt_to'];
		}
		
		if (isset($this->request->get['filter_weight'])) {
			$url .= '&filter_weight=' . $this->request->get['filter_weight'];
		}
		
		if (isset($this->request->get['filter_clarity'])) {
			$url .= '&filter_clarity=' . $this->request->get['filter_clarity'];
		}
		
		if (isset($this->request->get['filter_color'])) {
			$url .= '&filter_color=' . $this->request->get['filter_color'];
		}
		
		if (isset($this->request->get['filter_lab'])) {
			$url .= '&filter_lab=' . $this->request->get['filter_lab'];
		}
		
		if (isset($this->request->get['filter_cut'])) {
			$url .= '&filter_cut=' . $this->request->get['filter_cut'];
		}
		
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $stone_price_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('masters/stone_price', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($stone_price_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($stone_price_total - $this->config->get('config_limit_admin'))) ? $stone_price_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $stone_price_total, ceil($stone_price_total / $this->config->get('config_limit_admin')));

		$data['filter_stone'] = $filter_stone;
		$data['filter_shape'] = $filter_shape;
		$data['filter_crt_from'] = $filter_crt_from;
		$data['filter_crt_to'] = $filter_crt_to;
		$data['filter_weight'] = $filter_weight;
		$data['filter_clarity'] = $filter_clarity;
		$data['filter_color'] = $filter_color;
		$data['filter_lab'] = $filter_lab;
		$data['filter_cut'] = $filter_cut;
		
		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('masters/stone_price_list', $data));
	}

	protected function getForm() {
		$this->load->language('masters/stone_price');

		$data['text_form'] = !isset($this->request->get['stone_price_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->get['stone_price_id'])) {
			$data['stone_price_id'] = $this->request->get['stone_price_id'];
		} else {
			$data['stone_price_id'] = 0;
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['diamond_type'])) {
			$data['error_diamond_type'] = $this->error['diamond_type'];
		} else {
			$data['error_diamond_type'] = '';
		}

		if (isset($this->error['shape'])) {
			$data['error_shape'] = $this->error['shape'];
		} else {
			$data['error_shape'] = '';
		}

		if (isset($this->error['carat_from'])) {
			$data['error_carat_from'] = $this->error['carat_from'];
		} else {
			$data['error_carat_from'] = '';
		}

		if (isset($this->error['carat_to'])) {
			$data['error_carat_to'] = $this->error['carat_to'];
		} else {
			$data['error_carat_to'] = '';
		}

		if (isset($this->error['clarity'])) {
			$data['error_clarity'] = $this->error['clarity'];
		} else {
			$data['error_clarity'] = '';
		}

		if (isset($this->error['color'])) {
			$data['error_color'] = $this->error['color'];
		} else {
			$data['error_color'] = '';
		}

		if (isset($this->error['lab'])) {
			$data['error_lab'] = $this->error['lab'];
		} else {
			$data['error_lab'] = '';
		}

		if (isset($this->error['cut'])) {
			$data['error_cut'] = $this->error['cut'];
		} else {
			$data['error_cut'] = '';
		}

		if (isset($this->error['price'])) {
			$data['error_price'] = $this->error['price'];
		} else {
			$data['error_price'] = '';
		}

		$url = '';

		if (isset($this->request->get['filter_stone'])) {
			$url .= '&filter_stone=' . $this->request->get['filter_stone'];
		}
		
		if (isset($this->request->get['filter_shape'])) {
			$url .= '&filter_shape=' . $this->request->get['filter_shape'];
		}
		
		if (isset($this->request->get['filter_crt_from'])) {
			$url .= '&filter_crt_from=' . $this->request->get['filter_crt_from'];
		}
		
		if (isset($this->request->get['filter_crt_to'])) {
			$url .= '&filter_crt_to=' . $this->request->get['filter_crt_to'];
		}
		
		if (isset($this->request->get['filter_weight'])) {
			$url .= '&filter_weight=' . $this->request->get['filter_weight'];
		}
		
		if (isset($this->request->get['filter_clarity'])) {
			$url .= '&filter_clarity=' . $this->request->get['filter_clarity'];
		}
		
		if (isset($this->request->get['filter_color'])) {
			$url .= '&filter_color=' . $this->request->get['filter_color'];
		}
		
		if (isset($this->request->get['filter_lab'])) {
			$url .= '&filter_lab=' . $this->request->get['filter_lab'];
		}
		
		if (isset($this->request->get['filter_cut'])) {
			$url .= '&filter_cut=' . $this->request->get['filter_cut'];
		}
		
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
			'href' => $this->url->link('masters/stone_price', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['stone_price_id'])) {
			$data['action'] = $this->url->link('masters/stone_price/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('masters/stone_price/edit', 'user_token=' . $this->session->data['user_token'] . '&stone_price_id=' . $this->request->get['stone_price_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('masters/stone_price', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['stone_price_id']) && (!$this->request->server['REQUEST_METHOD'] != 'POST')) {
			$stone_price_info = $this->model_masters_stone_price->getStonePrice($this->request->get['stone_price_id']);
		}

		if (isset($this->request->post['stone'])) {
			$data['stone'] = $this->request->post['stone'];
		} elseif (!empty($stone_price_info)) {
			$data['stone'] = $stone_price_info['stone'];
		} else {
			$data['stone'] = '';
		}

		if (isset($this->request->post['shape'])) {
			$data['shape'] = $this->request->post['shape'];
		} elseif (!empty($stone_price_info)) {
			$data['shape'] = $stone_price_info['shape'];
		} else {
			$data['shape'] = '';
		}

		if (isset($this->request->post['crt_from'])) {
			$data['crt_from'] = $this->request->post['crt_from'];
		} elseif (!empty($stone_price_info)) {
			$data['crt_from'] = $stone_price_info['crt_from'];
		} else {
			$data['crt_from'] = '';
		}

		if (isset($this->request->post['crt_to'])) {
			$data['crt_to'] = $this->request->post['crt_to'];
		} elseif (!empty($stone_price_info)) {
			$data['crt_to'] = $stone_price_info['crt_to'];
		} else {
			$data['crt_to'] = '';
		}

		if (isset($this->request->post['clarity'])) {
			$data['clarity'] = $this->request->post['clarity'];
		} elseif (!empty($stone_price_info)) {
			$data['clarity'] = $stone_price_info['clarity'];
		} else {
			$data['clarity'] = '';
		}

		if (isset($this->request->post['color'])) {
			$data['color'] = $this->request->post['color'];
		} elseif (!empty($stone_price_info)) {
			$data['color'] = $stone_price_info['color'];
		} else {
			$data['color'] = '';
		}

		if (isset($this->request->post['lab'])) {
			$data['lab'] = $this->request->post['lab'];
		} elseif (!empty($stone_price_info)) {
			$data['lab'] = $stone_price_info['lab'];
		} else {
			$data['lab'] = '';
		}

		if (isset($this->request->post['cut'])) {
			$data['cut'] = $this->request->post['cut'];
		} elseif (!empty($stone_price_info)) {
			$data['cut'] = $stone_price_info['cut'];
		} else {
			$data['cut'] = '';
		}

		if (isset($this->request->post['price'])) {
			$data['price'] = $this->request->post['price'];
		} elseif (!empty($stone_price_info)) {
			$data['price'] = $stone_price_info['price'];
		} else {
			$data['price'] = '';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('masters/stone_price_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'masters/stone_price')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['stone']) < 2) || (utf8_strlen($this->request->post['stone']) > 20)) {
			$this->error['stone'] = $this->language->get('error_stone');
		}

		if ((utf8_strlen($this->request->post['shape']) < 2) || (utf8_strlen($this->request->post['shape']) > 20)) {
			$this->error['shape'] = $this->language->get('error_shape');
		}

		if ((utf8_strlen($this->request->post['crt_from']) < 2) || (utf8_strlen($this->request->post['crt_from']) > 20)) {
			$this->error['crt_from'] = $this->language->get('error_crt_from');
		}

		if ((utf8_strlen($this->request->post['crt_to']) < 2) || (utf8_strlen($this->request->post['crt_to']) > 20)) {
			$this->error['crt_to'] = $this->language->get('error_crt_to');
		}

		if ((utf8_strlen($this->request->post['clarity']) < 2) || (utf8_strlen($this->request->post['clarity']) > 20)) {
			$this->error['clarity'] = $this->language->get('error_clarity');
		}

		if ((utf8_strlen($this->request->post['color']) < 1) || (utf8_strlen($this->request->post['color']) > 20)) {
			$this->error['color'] = $this->language->get('error_color');
		}

		if ((utf8_strlen($this->request->post['lab']) < 2) || (utf8_strlen($this->request->post['lab']) > 20)) {
			$this->error['lab'] = $this->language->get('error_lab');
		}

		if ((utf8_strlen($this->request->post['cut']) < 2) || (utf8_strlen($this->request->post['cut']) > 20)) {
			$this->error['cut'] = $this->language->get('error_cut');
		}

		if ((utf8_strlen($this->request->post['carat_price']) < 1) || (utf8_strlen($this->request->post['carat_price']) > 20)) {
			$this->error['carat_price'] = $this->language->get('error_carat_price');
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'masters/stone_price')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
	
	public function import() {

		$this->load->language('masters/stone_price');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('masters/stone_price');

		//&& $this->validateImport()
		if (($this->request->server['REQUEST_METHOD'] == 'POST') ) {

			$this->model_masters_stone_price->importStonePrice($this->request->files);
			
			$this->session->data['success'] = $this->language->get('text_success');
			
			$url = '';
			
			if (isset($this->request->get['filter_stone'])) {
			$url .= '&filter_stone=' . $this->request->get['filter_stone'];
			}
			
			if (isset($this->request->get['filter_shape'])) {
				$url .= '&filter_shape=' . $this->request->get['filter_shape'];
			}
			
			if (isset($this->request->get['filter_crt_from'])) {
				$url .= '&filter_crt_from=' . $this->request->get['filter_crt_from'];
			}
			
			if (isset($this->request->get['filter_crt_to'])) {
				$url .= '&filter_crt_to=' . $this->request->get['filter_crt_to'];
			}
			
			if (isset($this->request->get['filter_weight'])) {
				$url .= '&filter_weight=' . $this->request->get['filter_weight'];
			}
			
			if (isset($this->request->get['filter_clarity'])) {
				$url .= '&filter_clarity=' . $this->request->get['filter_clarity'];
			}
			
			if (isset($this->request->get['filter_color'])) {
				$url .= '&filter_color=' . $this->request->get['filter_color'];
			}
			
			if (isset($this->request->get['filter_lab'])) {
				$url .= '&filter_lab=' . $this->request->get['filter_lab'];
			}
			
			if (isset($this->request->get['filter_cut'])) {
				$url .= '&filter_cut=' . $this->request->get['filter_cut'];
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

			$this->response->redirect($this->url->link('masters/stone_price', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		if (isset($this->error['import_file'])) {
			$this->error['warning'] = $this->error['import_file'];
		}

		$this->getList();
	}
	
	public function export()
    {
        $this->load->model('masters/stone_price');
        
        $data = array(
            'sort' => 'stone',
            'order' => 'ASC'
        );
        
        $stone_price = "Stone Price ID,Stone Type,Shape,Carat From,Carat To,Weight,Clarity,Color,Lab,Cut,Polish,Symmetry,Fluorescence,Intensity,Carat Price,Total Price,Single Stone Price,Multistone Price\n";
        $results     = $this->model_masters_stone_price->getStonePriceExport($data);		
		
			if($results){
				foreach ($results as $result) {
					
					$stone_price .= $this->db->escape($result['stone_price_id']) . "," . $this->db->escape($result['stone']) . "," . $this->db->escape($result['shape']) . "," . $this->db->escape($result['crt_from']) . "," . $this->db->escape($result['crt_to']) . "," . $this->db->escape($result['clarity']) . "," . $this->db->escape($result['color']) . "," . $this->db->escape($result['lab']) . "," . $this->db->escape($result['cut']) . "," . $this->db->escape($result['polish']) . "," . $this->db->escape($result['symmetry']) . "," . $this->db->escape($result['fluorescence']) . "," . $this->db->escape($result['intensity']) . "," . $this->db->escape($result['carat_price']) . "," . $this->db->escape($result['total_price']) . "," . $this->db->escape($result['sprice']) . "," . $this->db->escape($result['mprice']) . "\n";
				}
			}
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Length: " . strlen($stone_price));
			header("Content-type: text/x-csv");
			header("Content-Disposition: attachment; filename=stone_price.csv");
			echo $stone_price;
			exit;
    }
}