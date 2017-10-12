<?php
class ControllerAccountForgotten extends Controller {
	private $error = array();

	public function index() {
		if ($this->customer->isLogged()) {
			$this->response->redirect($this->url->link('account/account', '', true));
		}

		$this->load->language('account/forgotten');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('account/customer');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_account_customer->editCode($this->request->post['email'], token(40));

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_forgotten'),
			'href' => $this->url->link('account/forgotten', '', true)
		);

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['action'] = $this->url->link('account/forgotten', '', true);

		$data['back'] = $this->url->link('account/login', '', true);

		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} else {
			$data['email'] = '';
		}

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('account/forgotten', $data));
	}

	protected function validate() {
		if (!isset($this->request->post['email'])) {
			$this->error['warning'] = $this->language->get('error_email');
		} elseif (!$this->model_account_customer->getTotalCustomersByEmail($this->request->post['email'])) {
			$this->error['warning'] = $this->language->get('error_email');
		}
		
		// Check if customer has been approved.
		$customer_info = $this->model_account_customer->getCustomerByEmail($this->request->post['email']);

		if ($customer_info && !$customer_info['status']) {
			$this->error['warning'] = $this->language->get('error_approved');
		}

		return !$this->error;
	}
	
	public function confirm() {

		$this->load->language('account/forgotten');
		$this->load->model('account/customer');

		$json = array();


		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

			$json['success'] =  $this->language->get('text_success');

			
			$this->load->language('mail/forgotten');

			$code = token(40);

			$this->model_account_customer->editCode($this->request->post['email'], $code);
			
			$password_link = $this->url->link('account/reset', 'code=' . $code, true);

			$firstname = 'User';
			$lastname = '';
			$customer_info = $this->model_account_customer->getCustomerByEmail($this->request->post['email']);
			if ($customer_info) {
				$firstname = $customer_info['firstname'];
				$lastname = $customer_info['lastname'];
			}
			$contact_link = HTTP_SERVER . 'contact-us';
			
			/*
			$subject = sprintf($this->language->get('text_subject'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$message  = sprintf($this->language->get('text_greeting'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8')) . "\n\n";
			$message .= $this->language->get('text_change') . "\n\n";
			$message .= $this->url->link('account/reset', 'code=' . $code, true) . "\n\n";
			$message .= sprintf($this->language->get('text_ip'), $this->request->server['REMOTE_ADDR']) . "\n\n";
			
			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($this->request->post['email']);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
			$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
			$mail->send();
			*/
			
			/*
			$this->load->language('mail/forgotten');

			$password = substr(sha1(uniqid(mt_rand(), true)), 0, 10);

			$this->model_account_customer->editPassword($this->request->post['email'], $password);

			$subject = sprintf($this->language->get('text_subject'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));

			$message  = sprintf($this->language->get('text_greeting'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8')) . "\n\n";
			$message .= $this->language->get('text_password') . "\n\n";
			$message .= $password;
			
			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($this->request->post['email']);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject($subject);
			$mail->setText($message);
			$mail->send();
			*/
			
			$this->load->model('catalog/email_manager');
			
			$data = array(
					'store_name' => $this->config->get('config_name'),
					'name' => $firstname.' '.$lastname,
					'password_link' => $password_link,
					'contact_link' => $contact_link
				);
				
			$this->model_catalog_email_manager->sendEmail($data, 'forgot-your-password',$this->request->post['email']);
				
			/*
			$mail = new PHPMailer();
			$mail->IsSMTP();
			$mail->Mailer = $this->config->get('config_mail_protocol');
			$mail->Host = $this->config->get('config_mail_smtp_hostname');
			$mail->Port = $this->config->get('config_mail_smtp_port'); // 8025, 587 and 25 can also be used. Use Port 465 for SSL.
			$mail->SMTPAuth = true;
			$mail->SMTPSecure = 'ssl';
			$mail->Username = $this->config->get('config_mail_smtp_username');
			$mail->Password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');

			$mail->From = $this->config->get('config_mail_smtp_username');
			$mail->FromName = "SACET";
			$mail->AddAddress($this->request->post['email'], " ");
			$mail->IsHTML(true); 
			$mail->Subject = html_entity_decode($subject, ENT_QUOTES, 'UTF-8');
			//$mail->Body = $this->load->view('mail/sign_up', $data);
			$mail->Body = $message;
			$mail->AltBody = $message;
			//$mail->WordWrap = 50; 
			$mail->send();
			*/

			//$this->session->data['success'] = $this->language->get('text_success');

			// Add to activity log
			

			if ($customer_info) {
				$this->load->model('account/activity');

				$activity_data = array(
					'customer_id' => $customer_info['customer_id'],
					'name'        => $customer_info['firstname'] . ' ' . $customer_info['lastname']
				);

				$this->model_account_activity->addActivity('forgotten', $activity_data);
			}

//			$this->response->redirect($this->url->link('account/login', '', 'SSL'));

		}elseif(!empty($this->error['warning'])) {
			$json['error'] =  $this->error['warning'];
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));			

	}
}
