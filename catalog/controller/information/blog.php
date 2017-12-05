<?php
class ControllerInformationBlog extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('information/blog');
		
		$this->load->model('catalog/information');

		$this->load->model('tool/image');
		
		$this->document->setTitle($this->language->get('heading_title'));
		
		if (isset($this->request->get['article_id'])) {
			
			$article_id = $this->request->get['article_id'];
			
			$data['article_id'] = $article_id;
			$data['blog'] = $this->model_catalog_information->getBlog($article_id);
			
			if ($data['blog']['image']) {
				$data['blog_image'] = 'image/'.$data['blog']['image'];
			} else {
				$data['blog_image'] = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
			}
				
		} else {
			
			if (isset($this->request->get['sort'])) {
				$sort = $this->request->get['sort'];
			} else {
				$sort = 'b.sort_order';
			}

			if (isset($this->request->get['order'])) {
				$order = $this->request->get['order'];
			} else {
				$order = 'ASC';
			}

			if (isset($this->request->get['page'])) {
				$page = $this->request->get['page'];
			} else {
				$page = 1;
			}

			if (isset($this->request->get['limit'])) {
				$limit = (int)$this->request->get['limit'];
			} else {
				$limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
			}
			
			$data['blogs'] = array();
			
			$filter_data = array(
					'sort'               => $sort,
					'order'              => $order,
					'start'              => ($page - 1) * $limit,
					'limit'              => $limit
				);

			$blog_total = $this->model_catalog_information->getTotalBlogs($filter_data);

			$results = $this->model_catalog_information->getBlogs($filter_data);
				
			foreach ($results as $result) {
				if ($result['image']) {
					$image = 'image/'.$result['image'];
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
				}

				$data['blogs'][] = array(
					'article_id'  => $result['article_id'],
					'thumb'       => $image,
					'name'        => $result['name'],
					'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'intro_text'  => $result['intro_text'],
					'author'      => $result['author'],
					'tag'      	  => $result['tag'],
					'date_modified' => date("d F Y",strtotime($result['date_modified'])),
					'href'        => 'index.php?route=information/blog&article_id='.$result['article_id']
				);
			}
			
			$url = '';

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$pagination = new Pagination();
			$pagination->total = $blog_total;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->url = $this->url->link('information/information', $url . '&page={page}');

			$data['pagination'] = $pagination->render();

			$data['results'] = sprintf($this->language->get('text_pagination'), ($blog_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($blog_total - $limit)) ? $blog_total : ((($page - 1) * $limit) + $limit), $blog_total, ceil($blog_total / $limit));

			$data['sort'] = $sort;
			$data['order'] = $order;
			$data['limit'] = $limit;
			
		}
		
		
		
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('information/blog', $data));
	}

	
}
