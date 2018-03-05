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

				$style_count = 0;
				$style_data = array();
				$filters = $this->model_catalog_category->getCategoryFilters($category['category_id']);
				foreach($filters as $filter_group){
					if($filter_group['filter_group_id'] == '5'){
						foreach($filter_group['filter'] as $filter){

							$style_filter_data = array(
								'filter_category_id' => $category['category_id'],
								'filter_filter'  => $filter['filter_id']
							);

							$total_style = $this->model_catalog_product->getTotalProducts($style_filter_data);
							if($total_style > 0){
								$style_count++;
								$style_data[] = array(
									'style_id' => $filter['filter_id'],
									'name'  => $filter['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($style_filter_data) . ')' : ''),
									'href'  => $this->url->link('product/category', 'path=' . $category['category_id']).'/'.$filter['keyword']
								);
							}
						}
					}
				}

				if($style_count > 0){
					// Level 1
					$data['categories'][] = array(
						'category_id'     => $category['category_id'],
						'name'     	=> $category['name'],
						'image'     => !empty($category['image']) ? HTTP_SERVER.'image/'.$category['image'] : '',
						'image2'    => !empty($category['image2']) ? HTTP_SERVER.'image/'.$category['image2'] : '',
						'image3'    => !empty($category['image3']) ? HTTP_SERVER.'image/'.$category['image3'] : '',
						'children' 	=> $style_data,
						'column'   	=> $category['column'] ? $category['column'] : 1,
						'href'     	=> $this->url->link('product/category', 'path=' . $category['category_id'])
					);
				}
			}
		}

		return $this->load->view('common/menu', $data);
	}
}
