<?php
class ControllerInformationCustomerReview extends Controller {
	private $error = array();

	public function index() {
		
		$this->load->model('catalog/information');
		
		$testimonials = $this->model_catalog_information->getTestimonials('4cc5012400006400050e0a76');	
		$testimonials_count = $this->model_catalog_information->getTestimonialCount('4cc5012400006400050e0a76');	
				
		$data['totals'] = (isset($testimonials_count['numberOfReviews']['total']) ? $testimonials_count['numberOfReviews']['total'] : '1971');
		
		if(isset($testimonials['reviews'])){
			foreach ($testimonials['reviews'] as $key=>$testimonials) {
					$userimage = 'https://user-images.trustpilot.com/default/73x73.png';
					$data['testimonials'][] = array(						
						'name'		 => $testimonials['consumer']['displayName'],
						'city'		 => $testimonials['consumer']['displayLocation'],
						'content'    => $testimonials['text'],
						'title'		 => $testimonials['title'],
						'date'		 => date('d. M', strtotime($testimonials['createdAt'])),
						'star'		 => $testimonials['stars'],
						'href'       => 'https://uk.trustpilot.com/reviews/' . $testimonials['id'],	
						'image'      => $userimage,
						
					);	
			}
		}

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('information/customer_review', $data));
	}
}
