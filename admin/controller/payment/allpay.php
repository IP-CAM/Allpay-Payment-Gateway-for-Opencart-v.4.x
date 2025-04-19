<?php
namespace Opencart\Admin\Controller\Extension\Allpay\Payment;
class Allpay extends \Opencart\System\Engine\Controller {
	public function index(): void {
		$this->load->language('extension/allpay/payment/allpay');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment')
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/allpay/payment/allpay', 'user_token=' . $this->session->data['user_token'])
		];

		$data['save'] = $this->url->link('extension/allpay/payment/allpay.save', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment');

		$data['payment_allpay_response'] = $this->config->get('payment_allpay_response');

		$data['payment_allpay_approved_status_id'] = $this->config->get('payment_allpay_approved_status_id');
		$data['payment_allpay_failed_status_id'] = $this->config->get('payment_allpay_failed_status_id');
		$data['payment_allpay_installment'] = $this->config->get('payment_allpay_installment');

		$data['payment_allpay_api_login'] = $this->config->get('payment_allpay_api_login');
		$data['payment_allpay_api_key'] = $this->config->get('payment_allpay_api_key');

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$data['payment_allpay_geo_zone_id'] = $this->config->get('payment_allpay_geo_zone_id');

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$data['payment_allpay_status'] = $this->config->get('payment_allpay_status');
		$data['payment_allpay_sort_order'] = $this->config->get('payment_allpay_sort_order');

		//$data['report'] = $this->getReport();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/allpay/payment/allpay', $data));
	}

	public function save(): void {
		$this->load->language('extension/allpay/payment/allpay');

		$json = [];

		if (!$this->user->hasPermission('modify', 'extension/allpay/payment/allpay')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('setting/setting');

			$this->model_setting_setting->editSetting('payment_allpay', $this->request->post);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function install(): void {
        /*
		if ($this->user->hasPermission('modify', 'extension/payment')) {
			$this->load->model('extension/allpay/payment/allpay');

			$this->model_extension_oc_payment_example_payment_allpay->install();
		}
        */
	}

	public function uninstall(): void {
        /*
		if ($this->user->hasPermission('modify', 'extension/payment')) {
			$this->load->model('extension/allpay/payment/allpay');

			$this->model_extension_oc_payment_example_payment_allpay->uninstall();
		}
            */
	}
}
