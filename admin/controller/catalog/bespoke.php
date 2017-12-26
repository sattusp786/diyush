<?php
class ControllerCatalogBespoke extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('catalog/bespoke');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/bespoke');

		$this->getList();
	}

	public function add() {
		$this->load->language('catalog/bespoke');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/bespoke');

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$this->model_catalog_bespoke->addBespoke($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_lname'])) {
				$url .= '&filter_lname=' . urlencode(html_entity_decode($this->request->get['filter_lname'], ENT_QUOTES, 'UTF-8'));
			}
			
			if (isset($this->request->get['filter_subject'])) {
				$url .= '&filter_subject=' . urlencode(html_entity_decode($this->request->get['filter_subject'], ENT_QUOTES, 'UTF-8'));
			}
			
			if (isset($this->request->get['filter_text'])) {
				$url .= '&filter_text=' . urlencode(html_entity_decode($this->request->get['filter_text'], ENT_QUOTES, 'UTF-8'));
			}
			
			if (isset($this->request->get['filter_email'])) {
				$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
			}
			
			if (isset($this->request->get['filter_phone'])) {
				$url .= '&filter_phone=' . urlencode(html_entity_decode($this->request->get['filter_phone'], ENT_QUOTES, 'UTF-8'));
			}
			
			if (isset($this->request->get['filter_address'])) {
				$url .= '&filter_address=' . urlencode(html_entity_decode($this->request->get['filter_address'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
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

			$this->response->redirect($this->url->link('catalog/bespoke', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('catalog/bespoke');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/bespoke');

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$this->model_catalog_bespoke->editBespoke($this->request->get['bespoke_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_lname'])) {
				$url .= '&filter_lname=' . urlencode(html_entity_decode($this->request->get['filter_lname'], ENT_QUOTES, 'UTF-8'));
			}
			
			if (isset($this->request->get['filter_subject'])) {
				$url .= '&filter_subject=' . urlencode(html_entity_decode($this->request->get['filter_subject'], ENT_QUOTES, 'UTF-8'));
			}
			
			if (isset($this->request->get['filter_text'])) {
				$url .= '&filter_text=' . urlencode(html_entity_decode($this->request->get['filter_text'], ENT_QUOTES, 'UTF-8'));
			}
			
			if (isset($this->request->get['filter_email'])) {
				$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
			}
			
			if (isset($this->request->get['filter_phone'])) {
				$url .= '&filter_phone=' . urlencode(html_entity_decode($this->request->get['filter_phone'], ENT_QUOTES, 'UTF-8'));
			}
			
			if (isset($this->request->get['filter_address'])) {
				$url .= '&filter_address=' . urlencode(html_entity_decode($this->request->get['filter_address'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
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

			$this->response->redirect($this->url->link('catalog/bespoke', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('catalog/bespoke');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/bespoke');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $bespoke_id) {
				$this->model_catalog_bespoke->deleteBespoke($bespoke_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_lname'])) {
				$url .= '&filter_lname=' . urlencode(html_entity_decode($this->request->get['filter_lname'], ENT_QUOTES, 'UTF-8'));
			}
			
			if (isset($this->request->get['filter_subject'])) {
				$url .= '&filter_subject=' . urlencode(html_entity_decode($this->request->get['filter_subject'], ENT_QUOTES, 'UTF-8'));
			}
			
			if (isset($this->request->get['filter_text'])) {
				$url .= '&filter_text=' . urlencode(html_entity_decode($this->request->get['filter_text'], ENT_QUOTES, 'UTF-8'));
			}
			
			if (isset($this->request->get['filter_email'])) {
				$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
			}
			
			if (isset($this->request->get['filter_phone'])) {
				$url .= '&filter_phone=' . urlencode(html_entity_decode($this->request->get['filter_phone'], ENT_QUOTES, 'UTF-8'));
			}
			
			if (isset($this->request->get['filter_address'])) {
				$url .= '&filter_address=' . urlencode(html_entity_decode($this->request->get['filter_address'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
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

			$this->response->redirect($this->url->link('catalog/bespoke', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function getList() {
		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = '';
		}

		if (isset($this->request->get['filter_lname'])) {
			$filter_lname = $this->request->get['filter_lname'];
		} else {
			$filter_lname = '';
		}
		
		if (isset($this->request->get['filter_subject'])) {
			$filter_subject = $this->request->get['filter_subject'];
		} else {
			$filter_subject = '';
		}
		
		if (isset($this->request->get['filter_text'])) {
			$filter_text = $this->request->get['filter_text'];
		} else {
			$filter_text = '';
		}
		
		if (isset($this->request->get['filter_email'])) {
			$filter_email = $this->request->get['filter_email'];
		} else {
			$filter_email = '';
		}
		
		if (isset($this->request->get['filter_phone'])) {
			$filter_phone = $this->request->get['filter_phone'];
		} else {
			$filter_phone = '';
		}
		
		if (isset($this->request->get['filter_address'])) {
			$filter_address = $this->request->get['filter_address'];
		} else {
			$filter_address = '';
		}

		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = '';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'DESC';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'r.date_added';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_lname'])) {
			$url .= '&filter_lname=' . urlencode(html_entity_decode($this->request->get['filter_lname'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_subject'])) {
			$url .= '&filter_subject=' . urlencode(html_entity_decode($this->request->get['filter_subject'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_text'])) {
			$url .= '&filter_text=' . urlencode(html_entity_decode($this->request->get['filter_text'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_email'])) {
			$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_phone'])) {
			$url .= '&filter_phone=' . urlencode(html_entity_decode($this->request->get['filter_phone'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_address'])) {
			$url .= '&filter_address=' . urlencode(html_entity_decode($this->request->get['filter_address'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
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
			'href' => $this->url->link('catalog/bespoke', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('catalog/bespoke/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('catalog/bespoke/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['bespokes'] = array();

		$filter_data = array(
			'filter_name'    => $filter_name,
			'filter_lname'     => $filter_lname,
			'filter_subject'     => $filter_subject,
			'filter_text'     => $filter_text,
			'filter_email'     => $filter_email,
			'filter_phone'     => $filter_phone,
			'filter_address'     => $filter_address,
			'filter_status'     => $filter_status,
			'sort'              => $sort,
			'order'             => $order,
			'start'             => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'             => $this->config->get('config_limit_admin')
		);

		$bespoke_total = $this->model_catalog_bespoke->getTotalBespokes($filter_data);

		$results = $this->model_catalog_bespoke->getBespokes($filter_data);

		foreach ($results as $result) {
			$data['bespokes'][] = array(
				'bespoke_id'  => $result['bespoke_id'],
				'name'       => $result['name'],
				'lname'     => $result['lname'],
				'subject'     => $result['subject'],
				'text'     => $result['text'],
				'email'     => $result['email'],
				'phone'     => $result['phone'],
				'address'     => $result['address'],
				'status'     => ($result['status']) ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'edit'       => $this->url->link('catalog/bespoke/edit', 'user_token=' . $this->session->data['user_token'] . '&bespoke_id=' . $result['bespoke_id'] . $url, true)
			);
		}

		$data['user_token'] = $this->session->data['user_token'];

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

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_lname'])) {
			$url .= '&filter_lname=' . urlencode(html_entity_decode($this->request->get['filter_lname'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_subject'])) {
			$url .= '&filter_subject=' . urlencode(html_entity_decode($this->request->get['filter_subject'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_text'])) {
			$url .= '&filter_text=' . urlencode(html_entity_decode($this->request->get['filter_text'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_email'])) {
			$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_phone'])) {
			$url .= '&filter_phone=' . urlencode(html_entity_decode($this->request->get['filter_phone'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_address'])) {
			$url .= '&filter_address=' . urlencode(html_entity_decode($this->request->get['filter_address'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link('catalog/bespoke', 'user_token=' . $this->session->data['user_token'] . '&sort=e.name' . $url, true);
		$data['sort_lname'] = $this->url->link('catalog/bespoke', 'user_token=' . $this->session->data['user_token'] . '&sort=e.lname' . $url, true);
		$data['sort_subject'] = $this->url->link('catalog/bespoke', 'user_token=' . $this->session->data['user_token'] . '&sort=e.subject' . $url, true);
		$data['sort_text'] = $this->url->link('catalog/bespoke', 'user_token=' . $this->session->data['user_token'] . '&sort=e.text' . $url, true);
		$data['sort_email'] = $this->url->link('catalog/bespoke', 'user_token=' . $this->session->data['user_token'] . '&sort=e.email' . $url, true);
		$data['sort_phone'] = $this->url->link('catalog/bespoke', 'user_token=' . $this->session->data['user_token'] . '&sort=e.phone' . $url, true);
		$data['sort_status'] = $this->url->link('catalog/bespoke', 'user_token=' . $this->session->data['user_token'] . '&sort=r.status' . $url, true);

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_lname'])) {
			$url .= '&filter_lname=' . urlencode(html_entity_decode($this->request->get['filter_lname'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_subject'])) {
			$url .= '&filter_subject=' . urlencode(html_entity_decode($this->request->get['filter_subject'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_text'])) {
			$url .= '&filter_text=' . urlencode(html_entity_decode($this->request->get['filter_text'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_email'])) {
			$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_phone'])) {
			$url .= '&filter_phone=' . urlencode(html_entity_decode($this->request->get['filter_phone'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_address'])) {
			$url .= '&filter_address=' . urlencode(html_entity_decode($this->request->get['filter_address'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $bespoke_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('catalog/bespoke', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($bespoke_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($bespoke_total - $this->config->get('config_limit_admin'))) ? $bespoke_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $bespoke_total, ceil($bespoke_total / $this->config->get('config_limit_admin')));

		$data['filter_name'] = $filter_name;
		$data['filter_lname'] = $filter_lname;
		$data['filter_subject'] = $filter_subject;
		$data['filter_text'] = $filter_text;
		$data['filter_email'] = $filter_email;
		$data['filter_phone'] = $filter_phone;
		$data['filter_address'] = $filter_address;
		$data['filter_status'] = $filter_status;

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/bespoke_list', $data));
	}

	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['bespoke_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

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

		if (isset($this->error['lname'])) {
			$data['error_lname'] = $this->error['lname'];
		} else {
			$data['error_lname'] = '';
		}

		if (isset($this->error['text'])) {
			$data['error_text'] = $this->error['text'];
		} else {
			$data['error_text'] = '';
		}

		if (isset($this->error['subject'])) {
			$data['error_subject'] = $this->error['subject'];
		} else {
			$data['error_subject'] = '';
		}

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_lname'])) {
			$url .= '&filter_lname=' . urlencode(html_entity_decode($this->request->get['filter_lname'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_subject'])) {
			$url .= '&filter_subject=' . urlencode(html_entity_decode($this->request->get['filter_subject'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_text'])) {
			$url .= '&filter_text=' . urlencode(html_entity_decode($this->request->get['filter_text'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_email'])) {
			$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_phone'])) {
			$url .= '&filter_phone=' . urlencode(html_entity_decode($this->request->get['filter_phone'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_address'])) {
			$url .= '&filter_address=' . urlencode(html_entity_decode($this->request->get['filter_address'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
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
			'href' => $this->url->link('catalog/bespoke', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['bespoke_id'])) {
			$data['action'] = $this->url->link('catalog/bespoke/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('catalog/bespoke/edit', 'user_token=' . $this->session->data['user_token'] . '&bespoke_id=' . $this->request->get['bespoke_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('catalog/bespoke', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['bespoke_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$bespoke_info = $this->model_catalog_bespoke->getBespoke($this->request->get['bespoke_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];
		
		$this->load->model('catalog/product');

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($bespoke_info)) {
			$data['name'] = $bespoke_info['name'];
		} else {
			$data['name'] = '';
		}

		if (isset($this->request->post['lname'])) {
			$data['lname'] = $this->request->post['lname'];
		} elseif (!empty($bespoke_info)) {
			$data['lname'] = $bespoke_info['lname'];
		} else {
			$data['lname'] = '';
		}

		if (isset($this->request->post['subject'])) {
			$data['subject'] = $this->request->post['subject'];
		} elseif (!empty($bespoke_info)) {
			$data['subject'] = $bespoke_info['subject'];
		} else {
			$data['subject'] = '';
		}

		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} elseif (!empty($bespoke_info)) {
			$data['email'] = $bespoke_info['email'];
		} else {
			$data['email'] = '';
		}

		if (isset($this->request->post['phone'])) {
			$data['phone'] = $this->request->post['phone'];
		} elseif (!empty($bespoke_info)) {
			$data['phone'] = $bespoke_info['phone'];
		} else {
			$data['phone'] = '';
		}
		
		if (isset($this->request->post['address'])) {
			$data['address'] = $this->request->post['address'];
		} elseif (!empty($bespoke_info)) {
			$data['address'] = $bespoke_info['address'];
		} else {
			$data['address'] = '';
		}
		
		if (isset($this->request->post['ip'])) {
			$data['ip'] = $this->request->post['ip'];
		} elseif (!empty($bespoke_info)) {
			$data['ip'] = $bespoke_info['ip'];
		} else {
			$data['ip'] = '';
		}

		if (isset($this->request->post['date_added'])) {
			$data['date_added'] = $this->request->post['date_added'];
		} elseif (!empty($bespoke_info)) {
			$data['date_added'] = ($bespoke_info['date_added'] != '0000-00-00 00:00' ? $bespoke_info['date_added'] : '');
		} else {
			$data['date_added'] = '';
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($bespoke_info)) {
			$data['status'] = $bespoke_info['status'];
		} else {
			$data['status'] = '';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/bespoke_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'catalog/bespoke')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['name']) {
			$this->error['name'] = $this->language->get('name');
		}

		if ((utf8_strlen($this->request->post['lname']) < 3) || (utf8_strlen($this->request->post['lname']) > 64)) {
			$this->error['lname'] = $this->language->get('error_lname');
		}

		if (utf8_strlen($this->request->post['text']) < 1) {
			$this->error['text'] = $this->language->get('error_text');
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'catalog/bespoke')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}