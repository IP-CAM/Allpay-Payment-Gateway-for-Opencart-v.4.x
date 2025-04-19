<?php
namespace Opencart\Catalog\Model\Extension\Allpay\Payment;

class Allpay extends \Opencart\System\Engine\Model {
    
    public function getMethods(array $address): array {
        $this->load->language('extension/allpay/payment/allpay');
        
        // Check configuration settings for payment availability based on address
        if (!$this->config->get('config_checkout_payment_address')) {
            $status = true;
        } elseif (!$this->config->get('payment_allpay_geo_zone_id')) {
            $status = true;
        } else {
            $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int)$this->config->get('payment_allpay_geo_zone_id') . "' AND `country_id` = '" . (int)$address['country_id'] . "' AND (`zone_id` = '" . (int)$address['zone_id'] . "' OR `zone_id` = '0')");
            
            if ($query->num_rows) {
                $status = true;
            } else {
                $status = false;
            }
        }
        
        $method_data = [];
        
        if ($status) {
            // Customize option_data based on Allpay API response or configurations
            
            $option_data = [
                'allpay' => [
                    'code' => 'allpay.allpay',
                    'name' => $this->language->get('text_title')
                ],
                // Additional options based on API or other criteria
            ];
            
            $method_data = [
                'code'       => 'allpay',
                'name'       => $this->language->get('heading_title'),
                'option'     => $option_data,
                'sort_order' => $this->config->get('payment_allpay_sort_order')
            ];
        }
        
        return $method_data;
    }
    
    // Add other methods required for Allpay integration, such as handling payments, etc.
    // Example methods:
    
    public function createPayment(array $data) {
        $api_login = $this->config->get('payment_allpay_api_login');
        $api_key = $this->config->get('payment_allpay_api_key');
        $api_url = 'https://allpay.to/app/?show=getpayment&mode=api5';
        
        // Build the request array
        $request = [
            'name' => $data['name'],
            'login' => $api_login,
            'order_id' => $data['order_id'],
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'lang' => 'ENG', // Example language setting
            'notifications_url' => $data['notifications_url'],
            'success_url' => $data['success_url'],
            // Add other parameters as needed
        ];
        
        // Generate signature
        $sign = $this->getApiSignature($request, $api_key);
        $request['sign'] = $sign;
        
        // Send POST request to Allpay API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        $result = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($result, true);
        
        if (isset($data['payment_url'])) {
            return $data['payment_url'];
        } else {
            return false;
        }
    }
    
    public function handlePaymentNotification(array $notification) {
        // Handle payment notification from Allpay
        // Verify signature, process payment status, update order status, etc.
    }
    
    private function getApiSignature(array $params, $apikey) {
        ksort($params);
        $chunks = [];
        foreach ($params as $k => $v) {
            $v = trim($v);
            if ($v !== '' && $k != 'sign') {
                $chunks[] = $v;
            }
        }
        $signature = implode(':', $chunks) . ':' . $apikey;
        $signature = hash('sha256', $signature);
        return $signature;
    }
}