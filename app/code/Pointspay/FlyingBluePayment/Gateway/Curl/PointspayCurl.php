<?php

namespace Pointspay\FlyingBluePayment\Gateway\Curl;

use Pointspay\FlyingBluePayment\Helper\Data;

class PointspayCurl
{
    const AUTH_PATH = 'checkout/oauth/token';
    const TRANSACTIONS_PATH = 'checkout/services/v3/transactions';

    protected $helper;

    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    private function getAuthHeaders($bearer = false)
    {
        $headers = array("Content-type: application/x-www-form-urlencoded");
        $security = "Authorization: Basic " . $this->helper->getEncodedAuth();
        if ($bearer) {
            $headers = array("Content-type: application/json");
            $security = "Authorization: Bearer " . $bearer;
        }
        array_push($headers, $security);
        return $headers;
    }

    private function getToken()
    {
        $payload = 'grant_type=client_credentials';
        $response_data = $this->runCurl($this->helper->getAPIURL() . 'checkout/oauth/token', $this->getAuthHeaders(),
            'POST', $payload);
        if (isset($response_data['access_token'])) {
            return $response_data;
        } else {
            return false;
        }
    }

    public function refundTransaction($order, $payment)
    {
        $auth = $this->getToken();
        if (!$auth) {
            return false;
        }
        $transactionID = str_replace('-capture', '', $payment->getParentTransactionId());
        $url = $this->helper->getAPIURL() . self::TRANSACTIONS_PATH . '/' . $transactionID . '/refunds';
        $requestParams = array(
            'amount' => number_format($this->getRefundedAmount($payment), 2, '.', ''),
            'currency' => $order->getCurrencyCode(),
            'timestamp' => (string)$this->helper->getTimeStamp()
        );
        $baseString = json_encode($requestParams, JSON_UNESCAPED_SLASHES);
        $cert_path = $this->helper->getCertificate();
        $cert_store = file_get_contents($cert_path);
        $signature = '';
        if (!empty($cert_store)) {
            openssl_pkcs12_read($cert_store, $cert_info, $this->helper->getPrivatekeyPassword());
            $privateKey = $cert_info['pkey'];
            $messageDigest = openssl_digest($baseString, 'sha256', true);
            openssl_private_encrypt($messageDigest, $encryptedData, $privateKey);
            $signature = base64_encode($encryptedData);
        }
        $headers = $this->getAuthHeaders($auth['access_token']);
        $headers[] = 'signature: ' . $signature;
        $response = $this->runCurl($url, $headers, 'POST', json_encode($requestParams, JSON_UNESCAPED_SLASHES));
        if ($response['status'] == 'success') {
            return $response;
        } else {
            return $response;
        }
    }

    private function getRefundedAmount($payment)
    {
        return $payment->getCreditMemo()->getGrandTotal();
    }

    private function runCurl($url, $header, $method = 'GET', $payload = '')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response_data = curl_exec($ch);
        if (curl_errno($ch) && $this->helper->isDebug()) {
            $this->helper->debugData('[ERROR]', 'cURL error: ' . curl_errno($ch), $url);
        }
        if ($this->helper->isDebug()) {
            $this->helper->debugData('[INFO-REQUEST]', print_r(json_decode($payload), true), $url);
            $this->helper->debugData('[INFO-RESPONSE]', print_r(json_decode($response_data), true), $url);
        }
        return json_decode($response_data, true);
    }
}
