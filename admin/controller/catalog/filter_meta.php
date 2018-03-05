<?php 
class ControllerCatalogFilterMeta extends Controller {
	private $error = array(); 
     
  	public function index() {
		$this->load->language('catalog/filter_meta');
    	
		$this->document->setTitle($this->language->get('heading_title')); 
		
		$this->load->model('catalog/filter_meta');
		
		$this->getList();
  	}
  
  	public function insert() {
    	$this->load->language('catalog/filter_meta');

    	$this->document->setTitle($this->language->get('heading_title')); 
		
		$this->load->model('catalog/filter_meta');
		
    	if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_filter_meta->addInformationFilter($this->request->post);
	  		
			$this->session->data['success'] = $this->language->get('text_success');
	  
			$url = '';
			
			if (isset($this->request->get['filter_title'])) {
				$url .= '&filter_title=' . $this->request->get['filter_title'];
			}
			
			if (isset($this->request->get['filter_category'])) {
				$url .= '&filter_category=' . $this->request->get['filter_category'];
			}
			
			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
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

			$this->response->redirect($this->url->link('catalog/filter_meta', 'user_token=' . $this->session->data['user_token'] . $url, true));
    	}
	
    	$this->getForm();
  	}
	
	public function import() {
        $this->language->load('catalog/filter_meta');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('catalog/filter_meta');


        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateImport()) {


            $data['emptycount'] = $this->model_catalog_filter_meta->importMannualUpload($this->request->files);

            $data['emptycount'] = explode('-', $data['emptycount']);


            $data['error'] = $data['emptycount'][0];
            $data['success'] = $data['emptycount'][1];

            if ($data['error']) {
                $this->session->data['text_counter'] = sprintf($this->language->get('text_counter'), $data['error']);
            }

            if ($data['success']) {
                $this->session->data['success'] = sprintf($this->language->get('text_success'), $data['success']);
            }

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

            $this->response->redirect($this->url->link('catalog/filter_meta', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        if (isset($this->error['import_file'])) {
            $this->error['warning'] = $this->error['import_file'];
        }

        $this->getList();
    }
	
	public function export() {
        if (!file_exists(DIR_DOWNLOAD)) {
            mkdir(DIR_DOWNLOAD);
        }

        $fields = array('Seo Keyword','Category ID','Title','Meta Title','Meta Tag Description','Meta Tag Keywords','Short Description','Description Top','Description','Stores ID','Language ID','Status','Sort Order');

        $filename = DIR_DOWNLOAD . 'information_filter_' . date('dmY') . '.csv';

        $fp = fopen($filename, 'w');

        fputcsv($fp, $fields, ',', '"');
		
		
		$query1 = $this->db->query("SELECT * from " . DB_PREFIX . "information_filter f LEFT JOIN " . DB_PREFIX . "information_filter_description d ON f.information_filter_id=d.information_filter_id WHERE 1 ORDER BY f.information_filter_id");
		
		if($query1->num_rows > 0)
		{
			foreach ($query1->rows as $result) {
				
				$get_stores = $this->db->query("SELECT * from " . DB_PREFIX . "information_filter_to_store WHERE information_filter_id='".$result['information_filter_id']."'");
				
				$store_arr = array();
				$store_str = "";
				foreach($get_stores->rows as $stores)
				{
					$store_arr[] = $stores['store_id'];
				}
				if(!empty($store_arr))
				{
					$store_str = implode(",",$store_arr);
				}
				
				$output_arr = array($result['keyword'],$result['category_id'],$result['title'],$result['meta_title'],$result['meta_description'],$result['meta_keyword'],$result['short_description'],$result['description_top'],html_entity_decode($result['description']),$store_str,$result['language_id'],$result['status'],$result['sort_order']);

				fputcsv($fp, $output_arr, ',', '"');

				unset($return);
			}
		}		

        header('Content-type: text/csv');
        header('Content-disposition: attachment; filename=information_filter_' . date('dmY') . '.csv');
        readfile(str_replace("\\", "/", DIR_DOWNLOAD) . 'information_filter_' . date('dmY') . '.csv');
        fclose($fp);
    }

  	public function update() {
    	$this->load->language('catalog/filter_meta');

    	$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('catalog/filter_meta');
	
    	if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_filter_meta->editInformationFilter($this->request->get['information_filter_id'], $this->request->post);
			
			$this->session->data['success'] = $this->language->get('text_success');
			
			$url = '';
			
			if (isset($this->request->get['filter_title'])) {
				$url .= '&filter_title=' . $this->request->get['filter_title'];
			}
			
			if (isset($this->request->get['filter_category'])) {
				$url .= '&filter_category=' . $this->request->get['filter_category'];
			}
			
			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
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
			
			$this->response->redirect($this->url->link('catalog/filter_meta', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

    	$this->getForm();
  	}

  	public function delete() {
    	$this->load->language('catalog/filter_meta');

    	$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('catalog/filter_meta');
		
		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $information_filter_id) {
				$this->model_catalog_filter_meta->deleteInformationFilter($information_filter_id);
	  		}

			$this->session->data['success'] = $this->language->get('text_success');
			
			$url = '';
			
			if (isset($this->request->get['filter_title'])) {
				$url .= '&filter_title=' . $this->request->get['filter_title'];
			}
			
			if (isset($this->request->get['filter_category'])) {
				$url .= '&filter_category=' . $this->request->get['filter_category'];
			}
			
			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
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
			
			$this->response->redirect($this->url->link('catalog/filter_meta', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

    	$this->getList();
  	}
	
  	private function getList() {				
		if (isset($this->request->get['filter_title'])) {
			$filter_title = $this->request->get['filter_title'];
		} else {
			$filter_title = null;
		}
		
		if (isset($this->request->get['filter_category'])) {
			$filter_category = $this->request->get['filter_category'];
		} else {
			$filter_category = null;
		}
	
		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = null;
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'fqd.title';
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
						
		$url = '';
						
		if (isset($this->request->get['filter_title'])) {
			$url .= '&filter_title=' . $this->request->get['filter_title'];
		}
		
		if (isset($this->request->get['filter_category'])) {
			$url .= '&filter_category=' . $this->request->get['filter_category'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
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

  		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], 'SSL'),
      		'separator' => false
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('catalog/filter_meta', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL'),       		
      		'separator' => ' :: '
   		);
		
		$data['insert'] = $this->url->link('catalog/filter_meta/insert', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL');
		$data['delete'] = $this->url->link('catalog/filter_meta/delete', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL');
    	
		$data['import'] = $this->url->link('catalog/filter_meta/import', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL');
        $data['export'] = $this->url->link('catalog/filter_meta/export', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL');
        $data['delete'] = $this->url->link('catalog/filter_meta/delete', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL');
		
		$data['filter_meta'] = array();

		$sortdata = array(
			'filter_title'	  => $filter_title, 
			'filter_category' => $filter_category, 
			'filter_status'   => $filter_status,
			'sort'            => $sort,
			'order'           => $order,
			'start'           => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'           => $this->config->get('config_limit_admin')
		);
		
		$qa_total = $this->model_catalog_filter_meta->geTotaltInformationFilters($sortdata);
			
		$results = $this->model_catalog_filter_meta->getInformationFilters($sortdata);
		
		$data['total'] = $qa_total;
				    	
		foreach ($results as $result) {
			$action = array();
			
			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('catalog/filter_meta/update', 'user_token=' . $this->session->data['user_token'] . '&information_filter_id=' . $result['information_filter_id'] . $url, 'SSL')
			);

      		$data['filter_meta'][] = array(
				'information_filter_id'		=> $result['information_filter_id'],
				'title'			=> $result['title'],				
				'category'		=> $result['category'],				
				'sort_order'	=> $result['sort_order'],
				'status'		=> ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'selected'		=> isset($this->request->post['selected']) && in_array($result['information_filter_id'], $this->request->post['selected']),
				'action'		=> $action
			);
    	}
		
 		$data['user_token'] = $this->session->data['user_token'];
		
		if (isset($this->session->data['text_counter'])) {
            $data['text_counter'] = $this->session->data['text_counter'];

            unset($this->session->data['text_counter']);
        } else {
            $data['text_counter'] = '';
        }
		
 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
		
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$url = '';

		if (isset($this->request->get['filter_title'])) {
			$url .= '&filter_title=' . $this->request->get['filter_title'];
		}
		
		if (isset($this->request->get['filter_category'])) {
			$url .= '&filter_category=' . $this->request->get['filter_category'];
		}
		
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
								
		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
					
		$data['sort_title'] = $this->url->link('catalog/filter_meta', 'user_token=' . $this->session->data['user_token'] . '&sort=fqd.title' . $url, 'SSL');		
		$data['sort_category'] = $this->url->link('catalog/filter_meta', 'user_token=' . $this->session->data['user_token'] . '&sort=category' . $url, 'SSL');		
		$data['sort_status'] = $this->url->link('catalog/filter_meta', 'user_token=' . $this->session->data['user_token'] . '&sort=fq.status' . $url, 'SSL');
		$data['sort_order'] = $this->url->link('catalog/filter_meta', 'user_token=' . $this->session->data['user_token'] . '&sort=fq.sort_order' . $url, 'SSL');
		
		$url = '';

		if (isset($this->request->get['filter_title'])) {
			$url .= '&filter_title=' . $this->request->get['filter_title'];
		}
		
		if (isset($this->request->get['filter_category'])) {
			$url .= '&filter_category=' . $this->request->get['filter_category'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}
												
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
				
		$pagination = new Pagination();
		$pagination->total = $qa_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('catalog/filter_meta', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', 'SSL');
			
		$data['pagination'] = $pagination->render();
	
		$data['filter_title'] = $filter_title;
		$data['filter_category'] = $filter_category;
		$data['filter_status'] = $filter_status;
		
		$data['sort'] = $sort;
		$data['order'] = $order;
				
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/filter_meta_list', $data));
  	}

  	private function getForm() {
	
 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

 		if (isset($this->error['title'])) {
			$data['error_title'] = $this->error['title'];
		} else {
			$data['error_title'] = array();
		}	

		if (isset($this->error['heading_title'])) {
			$data['error_heading_title'] = $this->error['heading_title'];
		} else {
			$data['error_heading_title'] = array();
		}			
   
   		if (isset($this->error['description'])) {
			$data['error_description'] = $this->error['description'];
		} else {
			$data['error_description'] = array();
		}

		$url = '';

		if (isset($this->request->get['filter_title'])) {
			$url .= '&filter_title=' . $this->request->get['filter_title'];
		}
		
		if (isset($this->request->get['filter_category'])) {
			$url .= '&filter_category=' . $this->request->get['filter_category'];
		}
	
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
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

  		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], 'SSL'),
			'separator' => false
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('catalog/filter_meta', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
									
		if (!isset($this->request->get['information_filter_id'])) {
			$data['action'] = $this->url->link('catalog/filter_meta/insert', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL');
		} else {
			$data['action'] = $this->url->link('catalog/filter_meta/update', 'user_token=' . $this->session->data['user_token'] . '&information_filter_id=' . $this->request->get['information_filter_id'] . $url, 'SSL');
		}
		
		$data['cancel'] = $this->url->link('catalog/filter_meta', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL');

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->get['information_filter_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
      		$information_filter_info = $this->model_catalog_filter_meta->getInformationFilter($this->request->get['information_filter_id']);
    	}

		$this->load->model('localisation/language');
		
		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		if (isset($this->request->post['information_filter_description'])) {
			$data['information_filter_description'] = $this->request->post['information_filter_description'];
		} elseif (isset($information_filter_info)) {
			$data['information_filter_description'] = $this->model_catalog_filter_meta->getInformationFilterDescriptions($this->request->get['information_filter_id']);
		} else {
			$data['information_filter_description'] = array();
		}


		if (isset($this->request->post['path'])) {
			$data['path'] = $this->request->post['path'];
		} elseif (!empty($information_filter_info)) {
			$data['path'] = $information_filter_info['path'];
		} else {
			$data['path'] = '';
		}

		if (isset($this->request->post['category_id'])) {
			$data['category_id'] = $this->request->post['category_id'];
		} elseif (!empty($information_filter_info)) {
			$data['category_id'] = $information_filter_info['category_id'];
		} else {
			$data['category_id'] = 0;
		}


		$this->load->model('catalog/filter');
		
		if (isset($this->request->post['keyword'])) {
			$filters = $this->request->post['keyword'];
		} elseif (!empty($information_filter_info['keyword'])) {		
			$filters = explode("_",$information_filter_info['keyword']);
		} else {
			$filters = array();
		}
	
		$data['keywords'] = array();

		foreach ($filters as $filter) {
			$filterid = explode(".",$filter);
			if(isset($filterid[1]))
			{
				$filter_info = $this->model_catalog_filter->getFilter($filterid[1]);
			}
			
			if (isset($filter_info)) {
				$data['keywords'][] = array(
					'filter_id' => $filter_info['filter_id'],
					'filter_group_id' => $filter_info['filter_group_id'],
					'name'      => $filter_info['group'] . ' &gt; ' . $filter_info['name']
				);
			}
		}	

		$this->load->model('catalog/filter');

		if (isset($this->request->post['category_filter'])) {
			$filters = $this->request->post['category_filter'];
		} elseif (!empty($information_filter_info)) {
			$filters = explode(",",$information_filter_info['keyword']);
		} else {
			$filters = array();
		}

		$data['category_filters'] = array();

		foreach ($filters as $filter_id) {
			$filter_info = $this->model_catalog_filter->getFilter($filter_id);

			if ($filter_info) {
				$data['category_filters'][] = array(
					'filter_id' => $filter_info['filter_id'],
					'name'      => $filter_info['group'] . ' &gt; ' . $filter_info['name']
				);
			}
		}



		$this->load->model('setting/store');
		
		$data['stores'] = array();
		
		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		);
		
		$stores = $this->model_setting_store->getStores();
		
		foreach ($stores as $store) {
			$data['stores'][] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			);
		}
		
		if (isset($this->request->post['information_store'])) {
			$data['information_store'] = $this->request->post['information_store'];
		} elseif (isset($this->request->get['information_filter_id'])) {
			$data['information_store'] = $this->model_catalog_filter_meta->getInformationFilterStores($this->request->get['information_filter_id']);
		} else {
			$data['information_store'] = array(0);
		}	
		
		if (isset($this->request->post['sort_order'])) {
      		$data['sort_order'] = $this->request->post['sort_order'];
    	} elseif (isset($information_filter_info)) {
      		$data['sort_order'] = $information_filter_info['sort_order'];
    	} else {
			$data['sort_order'] = 1;
		}		
		
    	if (isset($this->request->post['status'])) {
      		$data['status'] = $this->request->post['status'];
    	} else if (isset($information_filter_info)) {
			$data['status'] = $information_filter_info['status'];
		} else {
      		$data['status'] = 1;
    	}
		
		if (isset($this->request->post['nofollow'])) {
			$data['nofollow'] = $this->request->post['nofollow'];
		} elseif (!empty($information_filter_info)) {
			$data['nofollow'] = $information_filter_info['nofollow'];
		} else {
			$data['nofollow'] = 0;
		}

		if (isset($this->request->post['noindex'])) {
			$data['noindex'] = $this->request->post['noindex'];
		} elseif (!empty($information_filter_info)) {
			$data['noindex'] = $information_filter_info['noindex'];
		} else {
			$data['noindex'] = 0;
		}		
				
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/filter_meta_form', $data));
  	} 
	
  	private function validateForm() { 
    	if (!$this->user->hasPermission('modify', 'catalog/filter_meta')) {
      		$this->error['warning'] = $this->language->get('error_permission');
    	}

    	foreach ($this->request->post['information_filter_description'] as $language_id => $value) {
      		if ((strlen(utf8_decode($value['title'])) < 1) || (strlen(utf8_decode($value['title'])) > 255)) {
        		$this->error['title'][$language_id] = $this->language->get('error_title');
      		}
    	}
		
		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}
					
    	if (!$this->error) {
			return true;
    	} else {
      		return false;
    	}
  	}
	
	protected function validateImport() {

        $ext = pathinfo($this->request->files['import_file']['name'], PATHINFO_EXTENSION);

        if (empty($this->request->files['import_file']['name']) || $ext != 'csv') {

            $this->error['import_file'] = $this->language->get('error_import_file');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }
	
  	private function validateDelete() {
    	if (!$this->user->hasPermission('modify', 'catalog/filter_meta')) {
      		$this->error['warning'] = $this->language->get('error_permission');  
    	}
		
		if (!$this->error) {
	  		return true;
		} else {
	  		return false;
		}
  	}
		
	
}
?>