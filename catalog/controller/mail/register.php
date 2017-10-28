<?php
class ControllerMailRegister extends Controller {
	public function index(&$route, &$args, &$output) {
		$this->load->language('mail/register');

		$data['text_welcome'] = sprintf($this->language->get('text_welcome'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
		$data['text_login'] = $this->language->get('text_login');
		$data['text_approval'] = $this->language->get('text_approval');
		$data['text_service'] = $this->language->get('text_service');
		$data['text_thanks'] = $this->language->get('text_thanks');

		$this->load->model('account/customer_group');
			
		if (isset($args[0]['customer_group_id'])) {
			$customer_group_id = $args[0]['customer_group_id'];
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}
					
		$customer_group_info = $this->model_account_customer_group->getCustomerGroup($customer_group_id);
		
		if ($customer_group_info) {
			$data['approval'] = $customer_group_info['approval'];
		} else {
			$data['approval'] = '';
		}
			
		$data['login'] = $this->url->link('account/login', '', true);		
		$data['store'] = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');

		/*
		$mail = new Mail($this->config->get('config_mail_engine'));
		$mail->parameter = $this->config->get('config_mail_parameter');
		$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
		$mail->smtp_username = $this->config->get('config_mail_smtp_username');
		$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
		$mail->smtp_port = $this->config->get('config_mail_smtp_port');
		$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

		$mail->setTo($args[0]['email']);
		$mail->setFrom($this->config->get('config_email'));
		$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
		$mail->setSubject(sprintf($this->language->get('text_subject'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8')));
		$mail->setText($this->load->view('mail/register', $data));
		$mail->send(); 
		*/
		
		$mail = new PHPMailer();
		$mail->IsSMTP();
		$mail->Mailer = $this->config->get('config_mail_engine');
		$mail->Host = $this->config->get('config_mail_smtp_hostname');
		$mail->Port = $this->config->get('config_mail_smtp_port'); // 8025, 587 and 25 can also be used. Use Port 465 for SSL.
		$mail->SMTPAuth = true;
		$mail->SMTPSecure = 'ssl';
		$mail->Username = $this->config->get('config_mail_smtp_username');
		$mail->Password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');

		$mail->From = $this->config->get('config_mail_smtp_username');
		$mail->FromName = "ABELINI";
		$mail->AddAddress($args[0]['email'], " ");
		$mail->IsHTML(true); 
		$mail->Subject = sprintf($this->language->get('text_subject'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
		$mail->Body = $this->load->view('mail/register', $data);
		//$mail->AltBody = $text;
		//$mail->WordWrap = 50; 
		$mail->send();
	}
	
	public function alert(&$route, &$args, &$output) {
		// Send to main admin email if new account email is enabled
		if (in_array('account', (array)$this->config->get('config_mail_alert'))) {
			$this->load->language('mail/register');
			
			$data['text_signup'] = $this->language->get('text_signup');
			$data['text_firstname'] = $this->language->get('text_firstname');
			$data['text_lastname'] = $this->language->get('text_lastname');
			$data['text_customer_group'] = $this->language->get('text_customer_group');
			$data['text_email'] = $this->language->get('text_email');
			$data['text_telephone'] = $this->language->get('text_telephone');
			
			$data['firstname'] = $args[0]['firstname'];
			$data['lastname'] = $args[0]['lastname'];
			
			$this->load->model('account/customer_group');
			
			if (isset($args[0]['customer_group_id'])) {
				$customer_group_id = $args[0]['customer_group_id'];
			} else {
				$customer_group_id = $this->config->get('config_customer_group_id');
			}
			
			$customer_group_info = $this->model_account_customer_group->getCustomerGroup($customer_group_id);
			
			if ($customer_group_info) {
				$data['customer_group'] = $customer_group_info['name'];
			} else {
				$data['customer_group'] = '';
			}
			
			$data['email'] = $args[0]['email'];
			//$data['telephone'] = $args[0]['telephone'];
			$data['telephone'] = '';

			/*
			$mail = new Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($this->config->get('config_email'));
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject(html_entity_decode($this->language->get('text_new_customer'), ENT_QUOTES, 'UTF-8'));
			$mail->setText($this->load->view('mail/register_alert', $data));
			$mail->send();
			*/
			
			$mail = new PHPMailer();
			$mail->IsSMTP();
			$mail->Mailer = $this->config->get('config_mail_engine');
			$mail->Host = $this->config->get('config_mail_smtp_hostname');
			$mail->Port = $this->config->get('config_mail_smtp_port'); // 8025, 587 and 25 can also be used. Use Port 465 for SSL.
			$mail->SMTPAuth = true;
			$mail->SMTPSecure = 'ssl';
			$mail->Username = $this->config->get('config_mail_smtp_username');
			$mail->Password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');

			$mail->From = $this->config->get('config_mail_smtp_username');
			$mail->FromName = "ABELINI";
			$mail->AddAddress($this->config->get('config_email'), " ");
			$mail->IsHTML(true); 
			$mail->Subject = html_entity_decode($this->language->get('text_new_customer'), ENT_QUOTES, 'UTF-8');
			$mail->Body = $this->load->view('mail/register_alert', $data);
			//$mail->AltBody = $message;
			//$mail->WordWrap = 50; 
			$mail->send();
			
			
			// Send to additional alert emails if new account email is enabled
			$emails = explode(',', $this->config->get('config_mail_alert_email'));

			foreach ($emails as $email) {
				if (utf8_strlen($email) > 0 && filter_var($email, FILTER_VALIDATE_EMAIL)) {
					//$mail->setTo($email);
					$mail->AddAddress($email, " ");
					$mail->send();
				}
			}
		}	
	}
}		