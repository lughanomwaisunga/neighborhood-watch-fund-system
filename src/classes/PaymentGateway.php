<?php
/**
 * Payment Gateway Integration Class
 * Handles Mpamba and Airtel Money integrations
 */

namespace App\Classes;

class PaymentGateway {
    private $httpClient;

    public function __construct() {
        // Initialize HTTP client (using Guzzle or similar)
        // $this->httpClient = new \GuzzleHttp\Client();
    }

    /**
     * Initiate Mpamba payment
     */
    public function initiateMpambaPayment($member_id, $amount, $phone_number) {
        try {
            $apiKey = $_ENV['MPAMBA_API_KEY'];
            $apiSecret = $_ENV['MPAMBA_API_SECRET'];
            $apiUrl = $_ENV['MPAMBA_API_URL'];
            $callbackUrl = $_ENV['MPAMBA_CALLBACK_URL'];

            $payload = [
                'api_key' => $apiKey,
                'phone_number' => $phone_number,
                'amount' => $amount,
                'reference' => 'NW_' . $member_id . '_' . time(),
                'callback_url' => $callbackUrl,
                'description' => 'Neighborhood Watch Fund Collection'
            ];

            // Make HTTP request to Mpamba API
            // This is a placeholder - actual implementation depends on Mpamba API documentation
            $response = $this->makeHttpRequest($apiUrl . '/payment/initiate', $payload);

            if ($response['success']) {
                return [
                    'success' => true,
                    'transaction_id' => $response['transaction_id'],
                    'payment_url' => $response['payment_url'] ?? null
                ];
            }

            return ['success' => false, 'error' => $response['message'] ?? 'Payment initiation failed'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Initiate Airtel Money payment
     */
    public function initiateAirtelPayment($member_id, $amount, $phone_number) {
        try {
            $apiKey = $_ENV['AIRTEL_API_KEY'];
            $apiSecret = $_ENV['AIRTEL_API_SECRET'];
            $apiUrl = $_ENV['AIRTEL_API_URL'];
            $callbackUrl = $_ENV['AIRTEL_CALLBACK_URL'];

            $payload = [
                'api_key' => $apiKey,
                'phone_number' => $phone_number,
                'amount' => $amount,
                'reference' => 'NW_' . $member_id . '_' . time(),
                'callback_url' => $callbackUrl,
                'description' => 'Neighborhood Watch Fund Collection'
            ];

            // Make HTTP request to Airtel Money API
            // This is a placeholder - actual implementation depends on Airtel Money API documentation
            $response = $this->makeHttpRequest($apiUrl . '/payment/initiate', $payload);

            if ($response['success']) {
                return [
                    'success' => true,
                    'transaction_id' => $response['transaction_id'],
                    'payment_url' => $response['payment_url'] ?? null
                ];
            }

            return ['success' => false, 'error' => $response['message'] ?? 'Payment initiation failed'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Verify payment status
     */
    public function verifyPayment($transaction_id, $gateway) {
        try {
            if ($gateway === 'mpamba') {
                $apiUrl = $_ENV['MPAMBA_API_URL'];
                $apiKey = $_ENV['MPAMBA_API_KEY'];
            } else if ($gateway === 'airtel') {
                $apiUrl = $_ENV['AIRTEL_API_URL'];
                $apiKey = $_ENV['AIRTEL_API_KEY'];
            } else {
                return ['success' => false, 'error' => 'Invalid gateway'];
            }

            $response = $this->makeHttpRequest($apiUrl . '/payment/verify', [
                'transaction_id' => $transaction_id,
                'api_key' => $apiKey
            ]);

            return $response;
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Handle payment callback
     */
    public function handlePaymentCallback($data, $gateway) {
        try {
            $payment = new Payment();
            
            $status = $data['status'] === 'success' ? 'completed' : 'failed';
            
            $paymentData = [
                'member_id' => $data['member_id'],
                'amount' => $data['amount'],
                'payment_method' => $gateway,
                'transaction_id' => $data['transaction_id'],
                'status' => $status,
                'reference_number' => $data['reference'] ?? '',
                'notes' => 'Payment via ' . ucfirst($gateway)
            ];

            $result = $payment->createPayment($paymentData);

            if ($result['success'] && $status === 'completed') {
                // Update member balance
                $payment->updateMemberBalance($data['member_id'], $data['amount']);
                
                // Record transaction
                $balanceBefore = $payment->getMemberBalance($data['member_id']) - $data['amount'];
                $payment->recordTransaction([
                    'member_id' => $data['member_id'],
                    'payment_id' => $result['payment_id'],
                    'transaction_type' => 'credit',
                    'amount' => $data['amount'],
                    'description' => 'Payment via ' . ucfirst($gateway),
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceBefore + $data['amount']
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Make HTTP request to payment gateway
     */
    private function makeHttpRequest($url, $data, $method = 'POST') {
        try {
            // This is a placeholder implementation
            // In production, use Guzzle or similar HTTP client
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);
            
            if ($method === 'POST') {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                return json_decode($response, true);
            }

            return ['success' => false, 'message' => 'HTTP Error: ' . $httpCode];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
