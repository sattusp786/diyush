<?php
class ModelCatalogEmailManager extends Model
{
    public function sendEmail($data, $template, $sendto='', $custom_content='', $sendfrom = array()) {

			
		$template_data = $this->getEmailContent($template);
		if($template_data){
			$subject = html_entity_decode($template_data['subject'], ENT_QUOTES, 'UTF-8');
			
			if($custom_content != '') {
				$content = html_entity_decode($custom_content, ENT_QUOTES, 'UTF-8');
			} else {
				$content = html_entity_decode($template_data['content'], ENT_QUOTES, 'UTF-8');
			}

			$smtp_username = $this->config->get('config_mail_smtp_username');
			$smtp_password = $this->config->get('config_mail_smtp_password');

			if(empty($sendto)) {
				$sendto = $this->config->get('config_email');
			}

			if($sendfrom){
				$sender_email = $sendfrom['sender_email'];
				$sender_name = $sendfrom['sender_name'];
			}else{
				$sender_email = $this->config->get('config_email');
				if($sendto == $sender_email){
					$sender_email = 'info@abelini.com';
					$smtp_username = 'info@abelini.com';
					$smtp_password = 'Abelini$123';
				}
				$sender_name = $this->config->get('config_name');
			}
			
			foreach($data as $key => $value){
				$subject = str_replace("[$key]", $value, $subject);
				$content = str_replace("[$key]", $value, $content);
			}
			

			$mail = new Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($sendto);
			$mail->setFrom($sender_email);
			$mail->setReplyTo($sender_email);
			$mail->setSender($sender_name);
			$mail->setSubject($subject);
			$mail->setHTML($content);
			//$mail->setText($content);
			$mail->send();
			
			/*
			$mail	= new Mail();
			$mail->protocol  = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->hostname  = $this->config->get('config_mail_smtp_hostname');
			$mail->username  = $smtp_username;
			$mail->password  = $smtp_password;
			$mail->port      = $this->config->get('config_mail_smtp_port');
			$mail->timeout   = $this->config->get('config_mail_smtp_timeout');
			$mail->setTo($sendto);
			$mail->setFrom($sender_email);
			$mail->setSender($sender_name);
			$mail->setSubject($subject);
			$mail->setHTML($content);

			//echo $content;
			$mail->send();
			return true;
			*/
			
			
		}else{
			//$this->db->query('INSERT INTO ' . DB_PREFIX . 'message_log(title, text, date_added) values("Email FAILED", "' . $this->db->escape($template. ":". $sendto) . '" ,NOW())');
		}
		return false;
    }


    public function getEmailContent($id) {
		$sql = "SELECT * FROM " . DB_PREFIX . "email_manager em LEFT JOIN " . DB_PREFIX . "email_manager_description emd ON em.email_manager_id=emd.email_manager_id WHERE em.code = '" . $this->db->escape($id) . "' AND emd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
        $query = $this->db->query($sql);
			
        return $query->row;
    }


    public function addEnquiry($data) {
		
		$clientuseripadd = $_SERVER['REMOTE_ADDR'];
		
		if(!isset($data['enquiry_type_id'])){
			$data['enquiry_type_id'] = '0';
		}
		if(!isset($data['name'])){
			$data['name'] = '';
		}
		if(!isset($data['lname'])){
			$data['lname'] = '';
		}
		if(!isset($data['subject'])){
			$data['subject'] = '';
		}
		if(!isset($data['text'])){
			$data['text'] = '';
		}
		if(!isset($data['email'])){
			$data['email'] = '';
		}
		if(!isset($data['phone'])){
			$data['phone'] = '';
		}
		if(!isset($data['address'])){
			$data['address'] = '';
		}
		
		$query = $this->db->query("INSERT INTO " . DB_PREFIX . "enquiry SET enquiry_type_id='".$data['enquiry_type_id']."', name='".$data['name']."', lname='".$data['lname']."', subject='".$data['subject']."', text='".$data['text']."', email='".$data['email']."', phone='".$data['phone']."', address='".$data['address']."', enquiry_status='1', ip='".$clientuseripadd."', date_added=NOW(), date_modified=NOW(), status='1' ");

		return '1';
	}
}

?>