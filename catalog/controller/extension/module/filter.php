<?php
class ControllerExtensionModuleFilter extends Controller {
	public function index() {
		if (isset($this->request->get['path'])) {
			$parts = explode('_', (string)$this->request->get['path']);
		} else {
			$parts = array();
		}

		$data['currency_symbol'] = $this->currency->getSymbolLeft($this->session->data['currency']);
		
		$data['carat_from'] = 0.20;
		$data['carat_to'] = 1.00;
		$data['price_from'] = 0;
		$data['price_to'] = 5000;
		
		$category_id = end($parts);

		$this->load->model('catalog/category');

		$category_info = $this->model_catalog_category->getCategory($category_id);

		if ($category_info) {
			$this->load->language('extension/module/filter');

			if (isset($this->request->get['carat_from'])) {
				$data['carat_from'] = $this->request->get['carat_from'];
			}
			if (isset($this->request->get['carat_to'])) {
				$data['carat_to'] = $this->request->get['carat_to'];
			}
			if (isset($this->request->get['price_from'])) {
				$data['price_from'] = $this->request->get['price_from'];
			}
			if (isset($this->request->get['price_to'])) {
				$data['price_to'] = $this->request->get['price_to'];
			}
			
			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}
			
			$styleurl = '';
			if (isset($this->request->get['filter']) && !empty($this->request->get['filter'])) {
				$get_style_filter = $this->model_catalog_category->getStyleFilters($this->request->get['filter']);
				if($get_style_filter != ''){
					$styleurl .= $get_style_filter;
				}
			}

			$data['action'] = str_replace('&amp;', '&', $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url)).'&filter='.$styleurl;

			if (isset($this->request->get['filter'])) {
				$data['filter_category'] = explode(',', $this->request->get['filter']);
			} else {
				$data['filter_category'] = array();
			}

			$this->load->model('catalog/product');

			$data['filter_groups'] = array();

			$filter_groups = $this->model_catalog_category->getCategoryFilters($category_id);

			if ($filter_groups) {
				foreach ($filter_groups as $filter_group) {
					if($filter_group['filter_group_id'] != '5'){
						$childen_data = array();

						foreach ($filter_group['filter'] as $filter) {
							$filter_data = array(
								'filter_carat_from'  => $data['carat_from'],
								'filter_carat_to' 	 => $data['carat_to'],
								'filter_price_from'  => $data['price_from'],
								'filter_price_to' 	 => $data['price_to'],
								'filter_category_id' => $category_id,
								'filter_filter'      => $filter['filter_id']
							);

							$total_count = $this->model_catalog_product->getTotalProducts($filter_data);
							if($total_count > 0) {
								$childen_data[] = array(
									'filter_id' => $filter['filter_id'],
									'filter_class' => str_replace(" ","_",strtolower($filter['name'])),
									'filter_image' => HTTP_SERVER . 'image/' . $filter['image'],
									'name'      => $filter['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : '')
								);
							}
						}

						$data['filter_groups'][] = array(
							'filter_group_id' => $filter_group['filter_group_id'],
							'name'            => $filter_group['name'],
							'filter'          => $childen_data
						);
					}
				}

				return $this->load->view('extension/module/filter', $data);
			}
		}
	}
}