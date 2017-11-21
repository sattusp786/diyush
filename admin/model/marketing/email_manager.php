<?php

class ModelMarketingEmailManager extends Model {

    public function addEmailManager($data) {
	
		$this->db->query("INSERT INTO " . DB_PREFIX . "email_manager SET sort_order = '" . (int) $data['sort_order'] . "',code = '" . $data['code'] . "',  status = '" . (int) $data['status'] . "'");

		$email_manager_id = $this->db->getLastId();

		foreach ($data['email_manager_description'] as $language_id => $value) {
			
		    $this->db->query("INSERT INTO " . DB_PREFIX . "email_manager_description SET email_manager_id = '" . (int) $email_manager_id . "', language_id = '" . (int) $language_id . "', short_description = '" . $this->db->escape($value['short_description']) . "',subject = '" . $this->db->escape($value['subject']) . "', content = '" . $this->db->escape($value['content']) . "'");
		}

		if (isset($data['email_manager_store'])) {
		    foreach ($data['email_manager_store'] as $store_id) {
				
			$this->db->query("INSERT INTO " . DB_PREFIX . "email_manager_to_store SET email_manager_id = '" . (int) $email_manager_id . "', store_id = '" . (int) $store_id . "'");
		    }
		}

		$this->cache->delete('email_manager');
    }

    public function editEmailManager($email_manager_id, $data) {

		$this->db->query("UPDATE " . DB_PREFIX . "email_manager SET sort_order = '" . (int) $data['sort_order'] . "', status = '" . (int) $data['status'] . "' WHERE email_manager_id = '" . (int) $email_manager_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "email_manager_description WHERE email_manager_id = '" . (int) $email_manager_id . "'");

		foreach ($data['email_manager_description'] as $language_id => $value) {

		    $this->db->query("INSERT INTO " . DB_PREFIX . "email_manager_description SET email_manager_id='" . (int) $email_manager_id . "',language_id = '" . (int) $language_id . "', short_description = '" . $this->db->escape($value['short_description']) . "',subject = '" . $this->db->escape($value['subject']) . "', content = '" . $this->db->escape($value['content']) . "'");
		}
		

		$this->db->query("DELETE FROM " . DB_PREFIX . "email_manager_to_store WHERE email_manager_id = '" . (int) $email_manager_id . "'");

		if (isset($data['email_manager_store'])) {
		    foreach ($data['email_manager_store'] as $store_id) {
				
			$this->db->query("INSERT INTO " . DB_PREFIX . "email_manager_to_store SET email_manager_id = '" . (int) $email_manager_id . "', store_id = '" . (int) $store_id . "'");
		    }
		}

		$this->cache->delete('email_manager');
    }

    public function deleteEmailManager($email_manager_id) {

        $data = $this->getEmailManager($email_manager_id);
            	
		$this->db->query("DELETE FROM " . DB_PREFIX . "email_manager WHERE email_manager_id = '" . (int) $email_manager_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "email_manager_description WHERE email_manager_id = '" . (int) $email_manager_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "email_manager_to_store WHERE email_manager_id = '" . (int) $email_manager_id . "'");
		$this->cache->delete('email_manager');
    }


    public function getEmailManager($email_manager_id) {

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "email_manager
	em LEFT JOIN " . DB_PREFIX . "email_manager_description emd ON (em.email_manager_id = emd.email_manager_id) WHERE emd.language_id = '" . (int) $this->config->get('config_language_id') . "' AND em.email_manager_id='" . (int) $email_manager_id . "'");

		return $query->row;
	}

    public function getEmailManagers($data = array()) {

		$sql = "SELECT * FROM " . DB_PREFIX . "email_manager
			em LEFT JOIN " . DB_PREFIX . "email_manager_description emd ON (em.email_manager_id = emd.email_manager_id) WHERE emd.language_id = '" . (int) $this->config->get('config_language_id') . "'";

		
		$sort_data = array(
			'emd.subject',
		    'emd.short_description',
			'em.code',
		    'em.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
		    $sql .= " ORDER BY " . $data['sort'];
		} else {
		    $sql .= " ORDER BY em.sort_order ";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
		    $sql .= " DESC";
		} else {
		    $sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
		
		$query = $this->db->query($sql);

		return $query->rows;
    }

    public function getEmailManagerDescriptions($email_manager_id) {
		$email_manager_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "email_manager_description WHERE email_manager_id = '" . (int) $email_manager_id . "'");

		foreach ($query->rows as $result) {
		    $email_manager_description_data[$result['language_id']] = array(
			'short_description' => $result['short_description'],
			'subject' => $result['subject'],
			'content' => $result['content']
		    );
		}

		return $email_manager_description_data;
    }

    public function getEmailManagerStores($email_manager_id) {
		$email_manager_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "email_manager_to_store WHERE email_manager_id = '" . (int) $email_manager_id . "'");

		foreach ($query->rows as $result) {
		    $email_manager_store_data[] = $result['store_id'];
		}

		return $email_manager_store_data;
    }

    public function getTotalEmailManager() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "email_manager");

		return $query->row['total'];
    }

    public function getEmailContent($id) {
		$sql = "SELECT * FROM " . DB_PREFIX . "email_manager em LEFT JOIN " . DB_PREFIX . "email_manager_description emd ON em.email_manager_id=emd.email_manager_id WHERE em.code = '" . $this->db->escape($id) . "' AND emd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
        $query = $this->db->query($sql);
			
        return $query->row;
    }

    public function sendEmail($data, $template, $sendto='', $sendfrom = array()) {

			
		$template_data = $this->getEmailContent($template);
		if($template_data){
			$subject = html_entity_decode($template_data['subject'], ENT_QUOTES, 'UTF-8');
			$content = html_entity_decode($template_data['content'], ENT_QUOTES, 'UTF-8');

			$smtp_username = $this->config->get('config_smtp_username');
			$smtp_password = $this->config->get('config_smtp_password');

			if(empty($sendto)) {
				$sendto = $this->config->get('config_email');
			}

			if($sendfrom){
				$sender_email = $sendfrom['sender_email'];
				$sender_name = $sendfrom['sender_name'];
			}else{
				$sender_email = $this->config->get('config_email');
				if($sendto == $sender_email){
					$sender_email = 'info@sacet.com';
					$smtp_username = 'info@sacet.com';
					$smtp_password = 'Mailsub@123';
				}
				$sender_name = $this->config->get('config_name');
			}
			
			foreach($data as $key => $value){
				$subject = str_replace("[$key]", $value, $subject);
				$content = str_replace("[$key]", $value, $content);
			}


			$mail	= new Mail();
			$mail->protocol  = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->hostname  = $this->config->get('config_smtp_host');
			$mail->username  = $smtp_username;
			$mail->password  = $smtp_password;
			$mail->port      = $this->config->get('config_smtp_port');
			$mail->timeout   = $this->config->get('config_smtp_timeout');
			$mail->setTo($sendto);
			$mail->setFrom($sender_email);
			$mail->setSender($sender_name);
			$mail->setSubject($subject);
			$mail->setHTML($content);

			//echo $content;
			$mail->send();

			return true;
		}else{
			$this->db->query('INSERT INTO ' . DB_PREFIX . 'message_log(title, text, date_added) values("Email FAILED", "' . $this->db->escape($template. ":". $sendto) . '" ,NOW())');
		}
		return false;
    }

     
}

?>