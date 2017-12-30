<?php
class ControllerInformationBespoke extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('information/bespoke');
		
		$this->load->model('catalog/information');

		$this->load->model('tool/image');
		
		$this->document->setTitle($this->language->get('heading_title'));
		
		
			
			if (isset($this->request->get['sort'])) {
				$sort = $this->request->get['sort'];
			} else {
				$sort = 'sort_order';
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

			if (isset($this->request->get['limit'])) {
				$limit = (int)$this->request->get['limit'];
			} else {
				$limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
			}
			
			$data['bespokes'] = array();
			
			$filter_data = array(
					'sort'               => $sort,
					'order'              => $order,
					'start'              => ($page - 1) * $limit,
					'limit'              => $limit
				);

			$bespoke_total = $this->model_catalog_information->getTotalBespokes($filter_data);

			$results = $this->model_catalog_information->getBespokes($filter_data);
				
			foreach ($results as $result) {
				if ($result['image']) {
					$image = 'image/'.$result['image'];
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
				}

				$data['bespokes'][] = array(
					'bespoke_id'  => $result['bespoke_id'],
					'thumb'       => $image,
					'title'        => $result['title'],
					'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'date_added' => date("d F Y",strtotime($result['date_added']))
					//'href'        => 'index.php?route=information/bespoke&article_id='.$result['article_id']
				);
			}
			
			$url = '';

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$pagination = new Pagination();
			$pagination->total = $bespoke_total;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->url = $this->url->link('information/information', $url . '&page={page}');

			$data['pagination'] = $pagination->render();

			$data['results'] = sprintf($this->language->get('text_pagination'), ($bespoke_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($bespoke_total - $limit)) ? $bespoke_total : ((($page - 1) * $limit) + $limit), $bespoke_total, ceil($bespoke_total / $limit));

			$data['sort'] = $sort;
			$data['order'] = $order;
			$data['limit'] = $limit;
			
		
		
		
		
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('information/bespoke', $data));
	}

	public function confirm() {

		$this->load->language('information/bespoke');
		
		$this->load->model('catalog/information');

		$json = array();

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {

			$mail = new Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($this->config->get('config_email'));
			$mail->setFrom($this->config->get('config_email'));
			$mail->setReplyTo($this->request->post['bespoke_email']);
			$mail->setSender(html_entity_decode('Admin', ENT_QUOTES, 'UTF-8'));
			$mail->setSubject('New Bespoke Enquiry'. $this->request->post['bespoke_name']);
			$mail->setText($this->request->post['bespoke_enquiry']);
			$mail->send();
			
			$bespoke_data = array();
			$bespoke_data['name'] = $this->request->post['bespoke_name'];
			$bespoke_data['email'] = $this->request->post['bespoke_email'];
			$bespoke_data['subject'] = 'Bespoke Design Enquiry';
			$bespoke_data['text'] = $this->request->post['bespoke_enquiry'];
			$bespoke_data['enquiry_type_id'] = '3';
			$bespoke_info = $this->model_catalog_information->addEnquiry($bespoke_data);
			
			$json['success'] =  'Your details submitted successfully!';
			
		}
		
		if (isset($this->error['name'])) {
			$json['error'] = $this->error['name'];
		}

		if (isset($this->error['email'])) {
			$json['error'] = $this->error['email'];
		}

		if (isset($this->error['enquiry'])) {
			$json['error'] = $this->error['enquiry'];
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));			

	}
}
