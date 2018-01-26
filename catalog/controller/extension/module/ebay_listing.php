<?php
class ControllerExtensionModuleEbayListing extends Controller {
	public function index() {
		if ($this->config->get('ebay_status') == 1) {
			$this->load->language('extension/module/ebay');
			
			$this->load->model('tool/image');
			$this->load->model('extension/openbay/ebay_product');

			$data['heading_title'] = $this->language->get('heading_title');

			$data['products'] = array();

			$products = $this->cache->get('ebay_listing.' . md5(serialize($products)));

			if (!$products) {
				$products = $this->model_extension_openbay_ebay_product->getDisplayProducts();
				
				$this->cache->set('ebay_listing.' . md5(serialize($products)), $products);
			}

			foreach($products['products'] as $product) {
				if (isset($product['pictures'][0])) {
					$image = $this->model_extension_openbay_ebay_product->resize($product['pictures'][0], $this->config->get('ebay_listing_width'), $this->config->get('ebay_listing_height'));
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('ebay_listing_width'), $this->config->get('ebay_listing_height'));
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
			 

              
					'thumb' => $image, 
					'name'  => base64_decode($product['Title']), 
					'price' => $this->currency->format($product['priceGross'], $this->session->data['currency']), 
					'href'  => (string)$product['link']
				);
			}

			$data['tracking_pixel'] = $products['tracking_pixel'];

			return $this->load->view('extension/module/ebay', $data);
		}
	}
}