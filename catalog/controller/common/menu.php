<?php
class ControllerCommonMenu extends Controller {
	public function index() {
		$this->load->language('common/menu');

		// Menu
		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$data['categories'] = array();

		$categories = $this->model_catalog_category->getCategories(0);

		foreach ($categories as $category) {
			if ($category['top']) {
				// Level 2

				$style_data = array();
				$filters = $this->model_catalog_category->getCategoryFilters($category['category_id']);
				foreach($filters as $filter_group){
					if($filter_group['filter_group_id'] == '5'){
						foreach($filter_group['filter'] as $filter){

							$style_filter_data = array(
								'filter_category_id' => $category['category_id'],
								'filter_filter'  => $filter['filter_id']
							);

							$style_data[] = array(
								'style_id' => $filter['filter_id'],
								'name'  => $filter['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($style_filter_data) . ')' : ''),
								'href'  => $this->url->link('product/category', 'path=' . $category['category_id']).'&filter='.$filter['filter_id']
							);
						}
					}
				}	

				// Level 1
				$data['categories'][] = array(
					'category_id'     => $category['category_id'],
					'name'     => $category['name'],
					'children' => $style_data,
					'column'   => $category['column'] ? $category['column'] : 1,
					'href'     => $this->url->link('product/category', 'path=' . $category['category_id'])
				);
			}
		}

		return $this->load->view('common/menu', $data);
	}
}
