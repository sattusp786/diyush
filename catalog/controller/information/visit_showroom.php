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

		$data['config_address'] = nl2br($this->config->get('config_address'));
		$data['config_email'] = $this->config->get('config_email');
		$data['config_telephone'] = $this->config->get('config_telephone');
		$data['config_fax'] = $this->config->get('config_fax');
		$data['config_open'] = nl2br($this->config->get('config_open'));
		
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
		$this->load->model('catalog/email_manager');

		$json = array();

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {

			$title= 'Appointment Form';
			
				
			$data = array(
				'store_name' => $this->config->get('config_name'),
				'name' => $this->request->post['appointment_firstname'],
				'lname' => $this->request->post['appointment_lastname'],
				'email' => $this->request->post['appointment_email'],
				'phone' => $this->request->post['appointment_phone'],
				'subject' => $title,
				'enquiry_type_id' => '1',
				'text' => $this->request->post['appointment_message'].'<br/> Appointment Date : '.$this->request->post['appointment_date']
			);

			$this->model_catalog_email_manager->addEnquiry($data);
			$this->model_catalog_email_manager->sendEmail($data, 'appointment-form');
			//$this->model_catalog_email_manager->sendEmail($data,'designer-form-acknowledgement',$this->request->post['email']);
			
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
