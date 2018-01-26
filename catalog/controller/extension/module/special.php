<?php
class ControllerExtensionModuleSpecial extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/special');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');

		$data['products'] = array();

		$filter_data = array(
			'sort'  => 'pd.name',
			'order' => 'ASC',
			'start' => 0,
			'limit' => $setting['limit']
		);

		$results = $this->model_catalog_product->getProductSpecials($filter_data);

		if ($results) {
			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
				} else {
					$tax = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = $result['rating'];
				} else {
					$rating = false;
				}

				
			$this->load->model('extension/module/tagmanager');
			$data['tagmanager'] = $this->model_extension_module_tagmanager->getTagmanger();
			$array = explode("_", basename(__FILE__, '.php'));
			$data['listname'] = ucfirst(end($array));
          	$pprice = 0;
			
			if(isset($result) && isset($result['price'])) {
				$value = $result;
			} elseif (isset($product_info) && isset($product_info['price'])) {
				$value = $product_info;
			} elseif (isset($product) && isset($product['price'])) {
				$value = $product;
			} 
			if (isset($value)) {
				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$pprice = $this->currency->format($this->tax->calculate($value['price'], $value['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'],'',false);
				}
				if ((float)$value['special']) {
					$pprice = $this->currency->format($this->tax->calculate($value['special'], $value['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'],'',false);
				}
				$pid = $this->model_extension_module_tagmanager->tagmangerPmap($value['model'],$value['sku'],$value['product_id']);

				if (isset($value['manufacturer']) && !empty($value['manufacturer'])) {
						$brand = $value['manufacturer'];
				} else {
						$brand = $this->model_extension_module_tagmanager->getProductBrandName($value['product_id']);
				}
				$cat = $this->model_extension_module_tagmanager->getProductCatName($value['product_id']);
				$title = $this->model_extension_module_tagmanager->tagmangerPtitle($value['name'], $brand, $value['model'],$value['product_id']);
			}
			$data['products'][] = array(
				  'pid'      => (isset($pid) ? $pid : ''),
				  'title'    => (isset($title) ? $title : ''),
				  'brand'    => (isset($brand) ? $brand : ''),
				  'category' => (isset($cat) ? $cat : ''),
				  'pprice'   => (isset($pprice) ? number_format((float)$pprice, 2, '.', '') : '0'),
			 

              
					'product_id'  => $result['product_id'],
					'thumb'       => $image,
					'name'        => $result['name'],
					'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,
					'special'     => $special,
					'tax'         => $tax,
					'rating'      => $rating,
					'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'])
				);
			}

			return $this->load->view('extension/module/special', $data);
		}
	}
}