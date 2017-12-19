<?php
class ControllerInformationVisitShowroom extends Controller {
	private $error = array();

	public function index() {
		

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('information/visit_showroom', $data));
	}
	
	public function popup() {
		
		$this->load->language('information/visit_showroom');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['heading_title'] = $this->language->get('heading_title');
		
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['action'] = $this->url->link('information/visit_showroom', '', 'SSL');

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}	

		$this->response->setOutput($this->load->view('information/visit_showroom_popup', $data));		
	}
	
	public function confirm() {

		$this->load->language('information/visit_showroom');
		
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
			$mail->setReplyTo($this->request->post['appointment_email']);
			$mail->setSender(html_entity_decode('Admin', ENT_QUOTES, 'UTF-8'));
			$mail->setSubject('New Appointment Request'. $this->request->post['appointment_firstname']);
			$mail->setText($this->request->post['appointment_message']);
			$mail->send();
			
			$appointment_data = array();
			$appointment_data['name'] = $this->request->post['appointment_firstname'];
			$appointment_data['lname'] = $this->request->post['appointment_lastname'];
			$appointment_data['email'] = $this->request->post['appointment_email'];
			$appointment_data['name'] = $this->request->post['appointment_phone'];
			$appointment_data['phone'] = $this->request->post['appointment_date'];
			$appointment_data['text'] = $this->request->post['appointment_message'];
			$appointment_data['enquiry_type_id'] = '1';
			$appointment_info = $this->model_catalog_information->addEnquiry($appointment_data);
			
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
