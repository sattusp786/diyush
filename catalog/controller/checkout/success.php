<?php
class ControllerCheckoutSuccess extends Controller {
	public function index() {
		$this->load->language('checkout/success');

		if (isset($this->session->data['order_id'])) {


				$this->load->model('checkout/order');
				$this->load->model('extension/module/tagmanager');
				$data['tagmanager'] = $this->model_extension_module_tagmanager->getTagmanger();

				$data['language'] = $this->config->get('config_language');
				$data['currency'] = $this->config->get('config_currency');

				$data['orderCoupon'] = $this->model_extension_module_tagmanager->getOrderCoupon($this->session->data['order_id']);
				$data['orderDetails'] = $this->model_checkout_order->getOrder($this->session->data['order_id']);
				$data['orderProduct'] = $this->model_extension_module_tagmanager->getOrderProduct($this->session->data['order_id'], $data['orderDetails']);
				$data['orderDetails']['coupon'] = (isset($this->session->data['coupon'])) ? $this->session->data['coupon'] : false;

    			$data['currency'] = $this->session->data['currency']; // uncheck to enable multi currency input or disable for store default currency

				if ($data['currency'] != $this->config->get('config_currency')) {
					// Transactio values with conversion
				
                  $data['orderDetails']['shipping_total'] = (isset($this->session->data['shipping_method']['cost']) ? $this->session->data['shipping_method']['cost'] : 0) * $data['orderDetails']['currency_value'] ;
                  $data['orderValue'] = $data['orderDetails']['total'] * $data['orderDetails']['currency_value'];
                  $data['orderValue'] = number_format((float)$data['orderValue'], 2, '.', '');
                  $data['orderTax'] = $this->model_extension_module_tagmanager->getOrderTax($this->session->data['order_id']) * $data['orderDetails']['currency_value'];

    			} else {
				
                  $data['orderDetails']['shipping_total'] = (isset($this->session->data['shipping_method']['cost']) ? $this->session->data['shipping_method']['cost'] : 0) ;
                  $data['orderValue'] = number_format((float)$data['orderDetails']['total'], 2, '.', '');
                  $data['orderTax'] = $this->model_extension_module_tagmanager->getOrderTax($this->session->data['order_id']);
				}
				$data['orderTax'] = number_format($orderTax, 2, '.', '');


			
			$this->cart->clear();

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['guest']);
			unset($this->session->data['comment']);
			unset($this->session->data['order_id']);
			unset($this->session->data['coupon']);
			unset($this->session->data['reward']);
			unset($this->session->data['voucher']);
			unset($this->session->data['vouchers']);
			unset($this->session->data['totals']);
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_basket'),
			'href' => $this->url->link('checkout/cart')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_checkout'),
			'href' => $this->url->link('checkout/checkout', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_success'),
			'href' => $this->url->link('checkout/success')
		);

		if ($this->customer->isLogged()) {
			$data['text_message'] = sprintf($this->language->get('text_customer'), $this->url->link('account/account', '', true), $this->url->link('account/order', '', true), $this->url->link('account/download', '', true), $this->url->link('information/contact'));
		} else {
			$data['text_message'] = sprintf($this->language->get('text_guest'), $this->url->link('information/contact'));
		}

		$data['continue'] = $this->url->link('common/home');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('common/success_order', $data));
	}
}