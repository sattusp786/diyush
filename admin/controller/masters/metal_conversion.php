<?php
class ControllerMastersMetalConversion extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('masters/metal_conversion');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('masters/metal_purity');
		
		$data['puritys'] = array();
		$data['conversions'] = array();

		$results = $this->model_masters_metal_purity->getMetalPuritys();
		if($results){
			foreach($results AS $result){
				$data['puritys'][$result['code']] = $result['name'];
				$data['puritys2'][$result['code']] = $result['name'];
				$data['puritys3'][$result['code']] = $result['name'];
			}
		}
		
		$conversion = $this->model_masters_metal_purity->getMetalConversion();
		if($conversion){
			foreach($conversion AS $result){
				$data['conversions'][$result['code']][$result['code2']] = $result['rate'];							
			}
		}
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('masters/metal_conversion', $data));
		
		
	}
}