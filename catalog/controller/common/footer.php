<?php
class ControllerCommonFooter extends Controller {
	public function index() {
		$this->load->language('common/footer');


				$this->load->model('extension/module/tagmanager');
				$data['tagmanager'] = $this->model_extension_module_tagmanager->getTagmanger();

			

		$this->load->model('catalog/information');


				$data['blog'] = array(
					'name' => $this->config->get('easy_blog_home_page_name'),
					'href'  => $this->url->link('extension/extension/blog/blog')
				);
            

				$data['blog'] = array(
					'name' => $this->config->get('easy_blog_home_page_name'),
					'href'  => $this->url->link('extension/extension/blog/blog')
				);
            
		$data['informations'] = array();

		foreach ($this->model_catalog_information->getInformations() as $result) {
			if ($result['bottom']) {
				$data['informations'][] = array(
					'title' => $result['title'],
					'href'  => $this->url->link('information/information', 'information_id=' . $result['information_id'])
				);
			}
		}

		$data['contact'] = $this->url->link('information/contact');
		$data['return'] = $this->url->link('account/return/add', '', true);
		$data['sitemap'] = $this->url->link('information/sitemap');
		$data['tracking'] = $this->url->link('information/tracking');
		$data['manufacturer'] = $this->url->link('product/manufacturer');
		$data['voucher'] = $this->url->link('account/voucher', '', true);
		$data['affiliate'] = $this->url->link('affiliate/login', '', true);
		$data['special'] = $this->url->link('product/special');
		$data['account'] = $this->url->link('account/account', '', true);
		$data['order'] = $this->url->link('account/order', '', true);
		$data['wishlist'] = $this->url->link('account/wishlist', '', true);
		$data['newsletter'] = $this->url->link('account/newsletter', '', true);

		$data['config_telephone'] = $this->config->get('config_telephone');
		$data['config_address'] = $this->config->get('config_address');
		$data['config_email'] = $this->config->get('config_email');
		
		$data['about_company']		= HTTP_SERVER.'about_company';
		$data['what_we_do']			= HTTP_SERVER.'what_we_do';
		$data['what_we_think']		= HTTP_SERVER.'what_we_think';
		$data['careers']			= HTTP_SERVER.'careers';
		$data['web_development']	= HTTP_SERVER.'web_development';
		$data['graphic_design']		= HTTP_SERVER.'graphic_design';
		$data['copywriting']		= HTTP_SERVER.'copywriting';
		$data['online_marketing']	= HTTP_SERVER.'online_marketing';
		$data['team_members']		= $this->url->link('#', '', true);
		$data['testimonials']		= $this->url->link('#', '', true);
		$data['our_clients']		= $this->url->link('#', '', true);
		$data['careers_with_us']	= HTTP_SERVER.'careers_with_us';
		$data['about']				= HTTP_SERVER.'about_us';
		$data['blog']				= $this->url->link('#', '', true);
		$data['testimonials']		= $this->url->link('#', '', true);
				
		$data['powered'] = sprintf($this->language->get('text_powered'), $this->config->get('config_name'), date('Y', time()));

		// Whos Online
		if ($this->config->get('config_customer_online')) {
			$this->load->model('tool/online');

			if (isset($this->request->server['REMOTE_ADDR'])) {
				$ip = $this->request->server['REMOTE_ADDR'];
			} else {
				$ip = '';
			}

			if (isset($this->request->server['HTTP_HOST']) && isset($this->request->server['REQUEST_URI'])) {
				$url = ($this->request->server['HTTPS'] ? 'https://' : 'http://') . $this->request->server['HTTP_HOST'] . $this->request->server['REQUEST_URI'];
			} else {
				$url = '';
			}

			if (isset($this->request->server['HTTP_REFERER'])) {
				$referer = $this->request->server['HTTP_REFERER'];
			} else {
				$referer = '';
			}

			$this->model_tool_online->addOnline($ip, $this->customer->getId(), $url, $referer);
		}

		$data['scripts'] = $this->document->getScripts('footer');
		
		$data['live_chat'] = $this->load->controller('extension/module/zopim');
		
		return $this->load->view('common/footer', $data);
	}
}
