<?php 
class ControllerProductProduct extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('product/product');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$this->load->model('catalog/category');

		if (isset($this->request->get['path'])) {
			$path = '';

			$parts = explode('_', (string)$this->request->get['path']);

			$category_id = (int)array_pop($parts);

			foreach ($parts as $path_id) {
				if (!$path) {
					$path = $path_id;
				} else {
					$path .= '_' . $path_id;
				}

				$category_info = $this->model_catalog_category->getCategory($path_id);

				if ($category_info) {
					$data['breadcrumbs'][] = array(
						'text' => $category_info['name'],
						'href' => $this->url->link('product/category', 'path=' . $path)
					);
				}
			}

			// Set the last category breadcrumb
			$category_info = $this->model_catalog_category->getCategory($category_id);

			if ($category_info) {
				$url = '';

				if (isset($this->request->get['sort'])) {
					$url .= '&sort=' . $this->request->get['sort'];
				}

				if (isset($this->request->get['order'])) {
					$url .= '&order=' . $this->request->get['order'];
				}

				if (isset($this->request->get['page'])) {
					$url .= '&page=' . $this->request->get['page'];
				}

				if (isset($this->request->get['limit'])) {
					$url .= '&limit=' . $this->request->get['limit'];
				}

				$data['breadcrumbs'][] = array(
					'text' => $category_info['name'],
					'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url)
				);
			}
		}

		$this->load->model('catalog/manufacturer');

		if (isset($this->request->get['manufacturer_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_brand'),
				'href' => $this->url->link('product/manufacturer')
			);

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($this->request->get['manufacturer_id']);

			if ($manufacturer_info) {
				$data['breadcrumbs'][] = array(
					'text' => $manufacturer_info['name'],
					'href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . $url)
				);
			}
		}

		if (isset($this->request->get['search']) || isset($this->request->get['tag'])) {
			$url = '';

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_search'),
				'href' => $this->url->link('product/search', $url)
			);
		}

		if (isset($this->request->get['product_id'])) {
			$product_id = (int)$this->request->get['product_id'];
		} else {
			$product_id = 0;
		}

		$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct($product_id);

		if ($product_info) {
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['manufacturer_id'])) {
				$url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
			}

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['breadcrumbs'][] = array(
				'text' => $product_info['name'],
				'href' => $this->url->link('product/product', $url . '&product_id=' . $this->request->get['product_id'])
			);

			$this->document->setTitle($product_info['meta_title']);
			$this->document->setDescription($product_info['meta_description']);
			$this->document->setKeywords($product_info['meta_keyword']);
			$this->document->addLink($this->url->link('product/product', 'product_id=' . $this->request->get['product_id']), 'canonical');
			$this->document->addScript('catalog/view/javascript/jquery/magnific/jquery.magnific-popup.min.js');
			$this->document->addStyle('catalog/view/javascript/jquery/magnific/magnific-popup.css');
			$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js');
			$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js');
			$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
			$this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');

			$data['heading_title'] = $product_info['name'];

			$data['text_minimum'] = sprintf($this->language->get('text_minimum'), $product_info['minimum']);
			$data['text_login'] = sprintf($this->language->get('text_login'), $this->url->link('account/login', '', true), $this->url->link('account/register', '', true));

			$this->load->model('catalog/review');

			$data['tab_review'] = sprintf($this->language->get('tab_review'), $product_info['reviews']);

			$data['ring_size_pdf'] = HTTP_SERVER.'storage/download/ring-size-guide.pdf';
			$data['product_id'] = (int)$this->request->get['product_id'];
			$data['manufacturer'] = $product_info['manufacturer'];
			$data['manufacturers'] = $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $product_info['manufacturer_id']);
			$data['model'] = $product_info['model'];
			$data['sku'] = $product_info['sku'];
			$data['reward'] = $product_info['reward'];
			$data['points'] = $product_info['points'];
			$data['weight'] = round($product_info['weight'],2);
			$data['description'] = html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8');
			$data['no_price_message'] = 'This product combinations is not available and needs special attention.<br/> Please call us on '.$this->config->get('config_telephone').' for further assistance';
			
			$categories_info['delivery_days'] = 0;
			$categories = $this->model_catalog_product->getCategories($product_id);
			$get_category_id = $categories[0]['category_id'];
			$categories_info = $this->model_catalog_category->getCategory($get_category_id);
			
			if($product_info['delivery_days'] > 0){
				$delivery_days = $product_info['delivery_days'];
			} elseif($categories_info['delivery_days'] > 0){
				$delivery_days = $categories_info['delivery_days'];
			} else {
				$delivery_days = '14';
			}
			
			$today_date = date('Y-m-d');
			$data['delivery_date_top'] = date('jS M Y', strtotime($today_date. ' + '.$delivery_days.' days'));
			$data['delivery_date'] = date('l, d/m/Y', strtotime($today_date. ' + '.$delivery_days.' days'));
			
			if ($product_info['quantity'] <= 0) {
				$data['stock'] = $product_info['stock_status'];
			} elseif ($this->config->get('config_stock_display')) {
				$data['stock'] = $product_info['quantity'];
			} else {
				$data['stock'] = $this->language->get('text_instock');
			}

			$this->load->model('tool/image');

			if ($product_info['image']) {
				$data['popup'] = HTTP_SERVER . 'image/' . $product_info['image'];
			} else {
				$data['popup'] = '';
			}

			if ($product_info['image']) {
				$data['thumb'] = HTTP_SERVER . 'image/' . $product_info['image'];
			} else {
				$data['thumb'] = '';
			}
			
			/*
			if ($product_info['image']) {
				$data['popup'] = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height'));
			} else {
				$data['popup'] = '';
			}

			if ($product_info['image']) {
				$data['thumb'] = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height'));
			} else {
				$data['thumb'] = '';
			}
			*/
			
			$data['images'] = array();

			$results = $this->model_catalog_product->getProductImages($this->request->get['product_id']);

			foreach ($results as $result) {
				/*
				$data['images'][] = array(
					'popup' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height')),
					'thumb' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_height'))
				);
				*/
				
				$data['images'][] = array(
					'popup' => HTTP_SERVER . 'image/' . $result['image'],
					'thumb' => HTTP_SERVER . 'image/' . $result['image']
				);
			}

			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$data['price'] = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$data['price'] = false;
			}

			if ((float)$product_info['special']) {
				$data['special'] = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$data['special'] = false;
			}

			if ($this->config->get('config_tax')) {
				$data['tax'] = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']);
			} else {
				$data['tax'] = false;
			}

			$discounts = $this->model_catalog_product->getProductDiscounts($this->request->get['product_id']);


				$this->load->model('extension/module/tagmanager');
				$data['tagmanager'] = $this->model_extension_module_tagmanager->getTagmanger();
				$data['pprice'] = 0;
				$data['name' ] =  $product_info['name'];
                if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                    $data['pprice'] = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'],'',false);
                }

                if ((float)$product_info['special']) {
                    $data['pprice'] = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'],'',false);
                }
				
				$data['pid'] = $this->model_extension_module_tagmanager->tagmangerPmap($product_info['model'],$product_info['sku'],$product_info['product_id']);
				$data['brand'] = $product_info['manufacturer'];
				$data['category'] = $this->model_extension_module_tagmanager->getProductCatName($product_info['product_id']);
				$data['title'] = $this->model_extension_module_tagmanager->tagmangerPtitle($product_info['name'], $product_info['manufacturer'], $product_info['model'],$product_info['product_id']);


 
			
			$data['discounts'] = array();

			foreach ($discounts as $discount) {
				$data['discounts'][] = array(
					'quantity' => $discount['quantity'],
					'price'    => $this->currency->format($this->tax->calculate($discount['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'])
				);
			}
			
			$option_ider = array();
			if(isset($this->request->get['option_ids']) && !empty($this->request->get['option_ids'])){
				$option_str = $this->request->get['option_ids'];
				$option_ider = explode("-",$option_str);
			}
			
			$option_valuer = array();
			if(isset($this->request->get['option_values']) && !empty($this->request->get['option_values'])){
				$option_value_str = $this->request->get['option_values'];
				$option_valuer = explode("-",$option_value_str);
			}

			$data['options'] = array();

			$data['metal_name'] = '';
			$data['carat_value'] = 'no';
			foreach ($this->model_catalog_product->getProductOptions($this->request->get['product_id']) as $option) {
				$product_option_value_data = array();

				foreach ($option['product_option_value'] as $option_value) {
					if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
						if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float)$option_value['price']) {
							$price = $this->currency->format($this->tax->calculate($option_value['price'], $product_info['tax_class_id'], $this->config->get('config_tax') ? 'P' : false), $this->session->data['currency']);
						} else {
							$price = false;
						}
						
						$default = '0';
						if(in_array($option['option_id'],$option_ider)){
							if(in_array($option_value['option_value_id'],$option_valuer)){
								$default = '1';
							}
						} else {
							if($option_value['default'] == '1'){
								$default = '1';
							}
						}
						
						if($option['option_id'] == '14' && $default == '1'){
							$data['metal_name'] = $option_value['name'];
						}

						$product_option_value_data[] = array(
							'product_option_value_id' => $option_value['product_option_value_id'],
							'option_value_id'         => $option_value['option_value_id'],
							'name'                    => $option_value['name'],
							'default'                 => $default,
							'image'                   => $this->model_tool_image->resize($option_value['image'], 50, 50),
							'price'                   => $price,
							'price_prefix'            => $option_value['price_prefix']
						);
					}
				}

				if($option['name'] == 'Carat'){
					$data['carat_value'] = 'yes';
				}
				
				$data['options'][] = array(
					'product_option_id'    => $option['product_option_id'],
					'product_option_value' => $product_option_value_data,
					'option_id'            => $option['option_id'],
					'name'                 => $option['name'],
					'type'                 => $option['type'],
					'value'                => $option['value'],
					'required'             => $option['required']
				);
			}

			if ($product_info['minimum']) {
				$data['minimum'] = $product_info['minimum'];
			} else {
				$data['minimum'] = 1;
			}

			$data['review_status'] = $this->config->get('config_review_status');

			if ($this->config->get('config_review_guest') || $this->customer->isLogged()) {
				$data['review_guest'] = true;
			} else {
				$data['review_guest'] = false;
			}

			if ($this->customer->isLogged()) {
				$data['customer_name'] = $this->customer->getFirstName() . '&nbsp;' . $this->customer->getLastName();
			} else {
				$data['customer_name'] = '';
			}

			$data['reviews'] = sprintf($this->language->get('text_reviews'), (int)$product_info['reviews']);
			$data['rating'] = (int)$product_info['rating'];

			// Captcha
			if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page'))) {
				$data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'));
			} else {
				$data['captcha'] = '';
			}

			$data['square_box'] = array(3,4,5,6,10,11,12,13,15,16,17,20,21,23);
			
			$data['share'] = $this->url->link('product/product', 'product_id=' . (int)$this->request->get['product_id']);

			$data['attribute_groups'] = $this->model_catalog_product->getProductAttributes($this->request->get['product_id']);

			$data['products'] = array();

			$results = $this->model_catalog_product->getProductRelated($this->request->get['product_id']);

			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
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
					$rating = (int)$result['rating'];
				} else {
					$rating = false;
				}

				

    		$this->load->model('extension/module/tagmanager');
			$data['tagmanager'] = $this->model_extension_module_tagmanager->getTagmanger();
			$array = explode("_", basename(__FILE__, '.php'));
			$data['listname'] = ucfirst(end($array));
			
			if(isset($result) && isset($result['price'])) {
				$value = $result;
			} elseif (isset($product_info) && isset($product_info['price'])) {
				$value = $product_info;
			} elseif (isset($product) && isset($product['price'])) {
				$value = $product;
			} 
			if (isset($value)) {
				$pprice = 0.00;
				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$pprice = $this->currency->format($this->tax->calculate($value['price'], $value['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'],'',false);
				}
				if ((float)$value['special']) {
					$pprice = $this->currency->format($this->tax->calculate($value['special'], $value['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'],'',false);
				}
				$pid = $this->model_extension_module_tagmanager->tagmangerPmap($value['model'],$value['model'],$value['product_id']);

 				if (isset($value['manufacturer']) && !empty($value['manufacturer'])) {
						$brand = $value['manufacturer'];
				} else {
						$brand = $this->model_extension_module_tagmanager->getProductBrandName($value['product_id']);
				}
				$cat = (isset($category_info['name']) ? $category_info['name'] : $this->model_extension_module_tagmanager->getProductCatName($value['product_id']));
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
					'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
					'rating'      => $rating,
					'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'])
				);
			}

			$data['tags'] = array();

			if ($product_info['tag']) {
				$tags = explode(',', $product_info['tag']);

				foreach ($tags as $tag) {
					$data['tags'][] = array(
						'tag'  => trim($tag),
						'href' => $this->url->link('product/search', 'tag=' . trim($tag))
					);
				}
			}

			$data['recurrings'] = $this->model_catalog_product->getProfiles($this->request->get['product_id']);

			$this->model_catalog_product->updateViewed($this->request->get['product_id']);
			
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('product/product', $data));
		} else {
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['manufacturer_id'])) {
				$url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
			}

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('product/product', $url . '&product_id=' . $product_id)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}

	public function review() {
		$this->load->language('product/product');

		$this->load->model('catalog/review');

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['reviews'] = array();

		$review_total = $this->model_catalog_review->getTotalReviewsByProductId($this->request->get['product_id']);

		$results = $this->model_catalog_review->getReviewsByProductId($this->request->get['product_id'], ($page - 1) * 10, 10);

		foreach ($results as $result) {
			$data['reviews'][] = array(
				'author'     => $result['author'],
				'text'       => nl2br($result['text']),
				'rating'     => (int)$result['rating'],
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
			);
		}

		$pagination = new Pagination();
		$pagination->total = $review_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->url = $this->url->link('product/product/review', 'product_id=' . $this->request->get['product_id'] . '&page={page}');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($review_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($review_total - 10)) ? $review_total : ((($page - 1) * 10) + 10), $review_total, ceil($review_total / 10));

		$this->response->setOutput($this->load->view('product/review', $data));
	}

	public function write() {
		$this->load->language('product/product');

		$json = array();

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 25)) {
				$json['error'] = $this->language->get('error_name');
			}

			if ((utf8_strlen($this->request->post['text']) < 25) || (utf8_strlen($this->request->post['text']) > 1000)) {
				$json['error'] = $this->language->get('error_text');
			}

			if (empty($this->request->post['rating']) || $this->request->post['rating'] < 0 || $this->request->post['rating'] > 5) {
				$json['error'] = $this->language->get('error_rating');
			}

			// Captcha
			if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page'))) {
				$captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');

				if ($captcha) {
					$json['error'] = $captcha;
				}
			}

			if (!isset($json['error'])) {
				$this->load->model('catalog/review');

				$this->model_catalog_review->addReview($this->request->get['product_id'], $this->request->post);

				$json['success'] = $this->language->get('text_success');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function getRecurringDescription() {
		$this->load->language('product/product');
		$this->load->model('catalog/product');

		if (isset($this->request->post['product_id'])) {
			$product_id = $this->request->post['product_id'];
		} else {
			$product_id = 0;
		}

		if (isset($this->request->post['recurring_id'])) {
			$recurring_id = $this->request->post['recurring_id'];
		} else {
			$recurring_id = 0;
		}

		if (isset($this->request->post['quantity'])) {
			$quantity = $this->request->post['quantity'];
		} else {
			$quantity = 1;
		}

		$product_info = $this->model_catalog_product->getProduct($product_id);
		
		$recurring_info = $this->model_catalog_product->getProfile($product_id, $recurring_id);

		$json = array();

		if ($product_info && $recurring_info) {
			if (!$json) {
				$frequencies = array(
					'day'        => $this->language->get('text_day'),
					'week'       => $this->language->get('text_week'),
					'semi_month' => $this->language->get('text_semi_month'),
					'month'      => $this->language->get('text_month'),
					'year'       => $this->language->get('text_year'),
				);

				if ($recurring_info['trial_status'] == 1) {
					$price = $this->currency->format($this->tax->calculate($recurring_info['trial_price'] * $quantity, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					$trial_text = sprintf($this->language->get('text_trial_description'), $price, $recurring_info['trial_cycle'], $frequencies[$recurring_info['trial_frequency']], $recurring_info['trial_duration']) . ' ';
				} else {
					$trial_text = '';
				}

				$price = $this->currency->format($this->tax->calculate($recurring_info['price'] * $quantity, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);

				if ($recurring_info['duration']) {
					$text = $trial_text . sprintf($this->language->get('text_payment_description'), $price, $recurring_info['cycle'], $frequencies[$recurring_info['frequency']], $recurring_info['duration']);
				} else {
					$text = $trial_text . sprintf($this->language->get('text_payment_cancel'), $price, $recurring_info['cycle'], $frequencies[$recurring_info['frequency']], $recurring_info['duration']);
				}

				$json['success'] = $text;
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	
	public function add() {
		$this->language->load('checkout/cart');
				
		$hold = array();
		$json = array();
				
		if (isset($this->request->get['product_id'])) {
			$product_id = $this->request->get['product_id'];
		} else {
			$product_id = 0;
		}
		
		$this->load->model('catalog/product');
						
		$product_info = $this->model_catalog_product->getProduct($product_id);
		
		if ($product_info) {
			if (isset($this->request->post['quantity'])) {
				$quantity = $this->request->post['quantity'];
			} else {
		       $quantity = 1;								
			}
												
			if (isset($this->request->post['option'])) {
				$option = array_filter($this->request->post['option']);
			} else {
				$option = array();	
			}
			
			if (isset($this->session->data['cart'])) {
				$hold = $this->session->data['cart'];
			}
			
			$this->cart->clear();
			$this->cart->add($product_id, $quantity, $option);

			$json['success'] = '0';

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
							
			foreach ($this->cart->getProducts() as $product) {
				
				// Display prices
				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$unit_price = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));
					
					$price = $this->currency->format($unit_price, $this->session->data['currency']);
					$total = $this->currency->format($unit_price * $product['quantity'], $this->session->data['currency']);
				} else {
					$unit_price = 0;
					$price = false;
					$total = false;
				}
				
				$json['rrp'] = '';
				$json['yousave'] = '';
				$json['price'] = $total;
				$json['no_price'] = $product['no_price'];
				$json['weight'] = round($product['metal_weight'],2);
				if($unit_price > 0 && $product['rrp'] > 0){
					$rrp = $unit_price + ($unit_price * $product['rrp']);
					$json['rrp'] = $this->currency->format($rrp, $this->session->data['currency']);
					$json['yousave'] = $this->currency->format($rrp - $unit_price, $this->session->data['currency']);
				}
				
				$json['stones'] = '';
				$json['side_stones'] = '';
				$json['carat_weight'] = '';
				$sidestone_carat = 0;
				if(!empty($product['option'])){
					foreach($product['option'] as $option){
						if($option['name'] != 'Metal'){
							if($option['name'] == 'Band Width' || $option['name'] == 'Band Thickness' || $option['name'] == 'Length' || $option['name'] == 'Ring Size'){
								$pieces = '';
								$total_carat = 0;
								if(stripos($product['multistone'],'M') !== false){
									if(isset($option['multi_stones']) && !empty($option['multi_stones'])){
										foreach($option['multi_stones'] as $multi){
											$pieces .= '('.$multi['carat'].'ct. x '.$multi['pieces'].')';
											$total_carat += ($multi['carat']*$multi['pieces']);
										}
									}
								}
								if(stripos($product['multistone'],'S') !== false){
									if(isset($option['side_stones']) && !empty($option['side_stones'])){
										$s = 0;
										$total_carats = 0;
										$spieces = '';
										foreach($option['side_stones'] as $sider){
											if($s == '0'){
												$json['side_stones'] .= 'Stone Type : '.$sider['stone'].'<br>Shape : '.$sider['shape'].'<br>Clarity : '.$sider['clarity'].'<br>Colour : '.$sider['color'].'<br>Certificate : '.$sider['lab'].'<br>';
											}
											$spieces .= '('.$sider['carat'].'ct. x '.$sider['pieces'].')';
											$total_carats += ($sider['carat']*$sider['pieces']);
											$s++;
										}
										$sidestone_carat = round($total_carats,2);
										//$json['side_stones'] .= 'Total Weight : Approx '. round($total_carats,2).' ct. wt. '.$spieces.'<br/>';
									}
								}
								$json['stones'] .= $option['name']. ' : '. $option['value'].'<br/>';
								if($total_carat > 0){
									$json['carat_weight'] = 'Approx '. round($total_carat,2).' ct. wt.';
									$json['stones'] .= 'Total Weight : Approx '. round($total_carat,2).' ct. wt. '.($sidestone_carat > 0 ? ' & Side ('.$sidestone_carat.' ct. wt.)' : '').'<br/>';
								}
							} elseif($option['name'] == 'Carat'){
								if(stripos($product['multistone'],'M') !== false){
									$pieces = '';
									if(isset($option['multi_stones']) && !empty($option['multi_stones'])){
										foreach($option['multi_stones'] as $multi){
											$pieces .= '('.$multi['carat'].'ct. x '.$multi['pieces'].')';
										}
									}
									$total_carat = $option['value'];
								} else {
									$total_carat = $option['value'];
									$pieces = '('.$option['value'].'ct. x 1)';
								}
								
								if(stripos($product['multistone'],'S') !== false){
									if(isset($option['side_stones']) && !empty($option['side_stones'])){
										$s = 0;
										$total_carats = 0;
										$spieces = '';
										foreach($option['side_stones'] as $sider){
											if($s == '0'){
												$json['side_stones'] .= 'Stone Type : '.$sider['stone'].'<br>Shape : '.$sider['shape'].'<br>Clarity : '.$sider['clarity'].'<br>Colour : '.$sider['color'].'<br>Certificate : '.$sider['lab'].'<br>';
											}
											$spieces .= '('.$sider['carat'].'ct. x '.$sider['pieces'].')';
											$total_carats += ($sider['carat']*$sider['pieces']);
											$s++;
										}
										$sidestone_carat = $total_carats;
										//$json['side_stones'] .= 'Total Weight : Approx '. round($total_carats,2).' ct. wt. '.$spieces.'<br/>';
									}
								}
								$json['stones'] .= 'Total Weight : Approx '. $total_carat.' ct. wt. '.($sidestone_carat > 0 ? ' & Side ('.$sidestone_carat.' ct. wt.)' : '').'<br/>';
							} else {
								$json['stones'] .= $option['name']. ' : '. $option['value'].'<br/>';
							}
						}
					}
				}
				$json['stones'] = substr($json['stones'],0,-5);
			}
			
			if($json['price'] != ''){
				$json['success'] = '1';
			}
			
			$this->cart->clear();
			if($hold){				
				$this->session->data['cart'] = $hold; 
			}
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function freeringsizer() {
		
		$this->load->model('catalog/email_manager');

		$json = array();

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {

			$title= 'Free Ring Sizer Request Form';
			
				
			$data = array(
				'store_name' => $this->config->get('config_name'),
				'name' => $this->request->post['freeringsizer_firstname'],
				'lname' => $this->request->post['freeringsizer_lastname'],
				'email' => $this->request->post['freeringsizer_email'],
				'phone' => $this->request->post['freeringsizer_phone'],
				'address' => $this->request->post['freeringsizer_address'],
				'subject' => $title,
				'enquiry_type_id' => '4',
				'text' => $this->request->post['freeringsizer_message']
			);

			$this->model_catalog_email_manager->addEnquiry($data);
			$this->model_catalog_email_manager->sendEmail($data, 'free-ring-size-request');
			//$this->model_catalog_email_manager->sendEmail($data,'designer-form-acknowledgement',$this->request->post['email']);
			
			$json['success'] =  'Your details submitted successfully!';
			
		}
		
		if (isset($this->error['name'])) {
			$json['error'] = $this->error['name'];
		}

		if (isset($this->error['email'])) {
			$json['error'] = $this->error['email'];
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));			

	}

	public function enquiry() {
		
		$this->load->model('catalog/email_manager');

		$json = array();

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {

			$title= 'Make an Enquiry Form';
			
				
			$data = array(
				'store_name' => $this->config->get('config_name'),
				'name' => $this->request->post['enquiry_firstname'],
				'lname' => $this->request->post['enquiry_lastname'],
				'email' => $this->request->post['enquiry_email'],
				'phone' => $this->request->post['enquiry_phone'],
				'address' => $this->request->post['enquiry_address'],
				'subject' => $title,
				'enquiry_type_id' => '5',
				'text' => $this->request->post['enquiry_message']
			);

			$this->model_catalog_email_manager->addEnquiry($data);
			$this->model_catalog_email_manager->sendEmail($data, 'make-an-enquiry');
			//$this->model_catalog_email_manager->sendEmail($data,'designer-form-acknowledgement',$this->request->post['email']);
			
			$json['success'] =  'Your details submitted successfully!';
			
		}
		
		if (isset($this->error['name'])) {
			$json['error'] = $this->error['name'];
		}

		if (isset($this->error['email'])) {
			$json['error'] = $this->error['email'];
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));			

	}
}
