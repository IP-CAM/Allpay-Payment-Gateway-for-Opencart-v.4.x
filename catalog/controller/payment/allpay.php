<?php
namespace Opencart\Catalog\Controller\Extension\Allpay\Payment;
class Allpay extends \Opencart\System\Engine\Controller {
    public function index() {
        $this->load->language('extension/payment/allpay');

        $data['language'] = $this->config->get('config_language');

        return $this->load->view('extension/allpay/payment/allpay', $data);

    }

    public function confirm() {
        if ($this->session->data['payment_method']['code'] == 'allpay.allpay') {
            $this->load->model('checkout/order');
            $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
            $api_login = $this->config->get('payment_allpay_api_login');
            $api_key = $this->config->get('payment_allpay_api_key');
            $installment = $this->config->get('payment_allpay_installment');
            $api_url = 'https://allpay.to/app/?show=getpayment&mode=api5';

            $firstname = $order_info['firstname'];
            if($firstname == '') {
                $firstname = $order_info['payment_firstname'];
            }
            if($firstname == '') {
                $firstname = $order_info['shipping_firstname'];
            }
            $lastname = $order_info['lastname'];
            if($lastname == '') {
                $lastname = $order_info['payment_lastname'];
            }
            if($lastname == '') {
                $lastname = $order_info['shipping_lastname']; 
            }
            $client_name = trim($firstname . ' ' . $lastname);

            $request = [
                'name' => 'Payment for order #' . $order_info['order_id'],
                'login' => $api_login,
                'order_id' => $order_info['order_id'],
                'amount' => round($order_info['total'] * $order_info['currency_value'], 2),
                'currency' => $order_info['currency_code'],
                'lang' => 'ENG',
                'notifications_url' => $this->url->link('extension/allpay/payment/allpay.callback', '', true),
                'success_url' => $this->url->link('checkout/success', '', true),
                'client_name' => $client_name,
                'client_email' => $order_info['email'],
                'client_phone' => $order_info['telephone'],
            ];
            if($installment > 0) {
                $request['tash'] = (int)$installment;
            }

            $request['sign'] = $this->getApiSignature($request, $api_key);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api_url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
            $result = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($result, true);

            // Set Credit Card response
			if (isset($data['payment_url'])) {
				$this->load->model('checkout/order');
				$this->model_checkout_order->addHistory($this->session->data['order_id'], $this->config->get('config_order_status_id'), '', true);
				$this->response->redirect($data['payment_url']);
			} else {
                $this->response->redirect($this->url->link('checkout/failure', '', true));
			}
        }
    }

    public function callback() {
        $this->load->model('checkout/order');

        $post = $this->request->post;
        $api_key = $this->config->get('payment_allpay_api_key');
        $sign = $this->getApiSignature($post, $api_key);
        
        if (isset($post['status']) && $post['status'] == 1 && $post['sign'] == $sign) {
            $this->model_checkout_order->addHistory($post['order_id'], $this->config->get('payment_allpay_approved_status_id'), '', true);
        }
    }

    private function getApiSignature($params, $apikey) {
        ksort($params);
        $chunks = [];
        foreach($params as $k => $v) {
            $v = trim($v);
            if ($v !== '' && $k != 'sign') {
                $chunks[] = $v;
            }
        }
        $signature = implode(':', $chunks) . ':' . $apikey;
        return hash('sha256', $signature);
    }
}
