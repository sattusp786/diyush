<?php
class ControllerExtensionModuleFeatured extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/featured');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');

		$data['products'] = array();

		if (!$setting['limit']) {
			$setting['limit'] = 4;
		}

		if (!empty($setting['product'])) {
			$products = array_slice($setting['product'], 0, (int)$setting['limit']);

			foreach ($products as $product_id) {
				$product_info = $this->model_catalog_product->getProduct($product_id);

				if ($product_info) {
					if ($product_info['image']) {
						$image = $this->model_tool_image->resize($product_info['image'], $setting['width'], $setting['height']);
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
					}

					if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$price = false;
					}

					if ((float)$product_info['special']) {
						$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$special = false;
					}

					if ($this->config->get('config_tax')) {
						$tax = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']);
					} else {
						$tax = false;
					}

					if ($this->config->get('config_review_status')) {
						$rating = $product_info['rating'];
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
			 

              
						'product_id'  => $product_info['product_id'],
						'thumb'       => $image,
						'name'        => $product_info['name'],
						'description' => utf8_substr(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
						'price'       => $price,
						'special'     => $special,
						'tax'         => $tax,
						'rating'      => $rating,
						'href'        => $this->url->link('product/product', 'product_id=' . $product_info['product_id'])
					);
				}
			}
		}

		if ($data['products']) {
			return $this->load->view('extension/module/featured', $data);
		}
	}
}