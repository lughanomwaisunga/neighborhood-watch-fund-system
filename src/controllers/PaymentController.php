<?php
/**
 * Payment Controller
 */

namespace App\Controllers;

use App\Classes\Payment;
use App\Classes\PaymentGateway;
use App\Classes\Database;

class PaymentController {
    private $payment;
    private $gateway;

    public function __construct() {
        $this->payment = new Payment();
        $this->gateway = new PaymentGateway();
    }

    /**
     * Display all payments
     */
    public function index() {
        $page = $_GET['page'] ?? 1;
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;

        $payments = $this->payment->getAllPayments($limit, $offset);
        
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM payments");
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $totalPayments = $row['total'];
        $totalPages = ceil($totalPayments / $limit);

        return [
            'payments' => $payments,
            'total' => $totalPayments,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ];
    }

    /**
     * Get member payments
     */
    public function getMemberPayments($member_id) {
        $page = $_GET['page'] ?? 1;
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;

        $payments = $this->payment->getPaymentsByMemberId($member_id, $limit, $offset);
        return [
            'payments' => $payments,
            'member_id' => $member_id
        ];
    }

    /**
     * Initiate payment
     */
    public function initiatePayment() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['error' => 'Invalid request method', 'status' => 400];
        }

        $member_id = intval($_POST['member_id'] ?? 0);
        $amount = floatval($_POST['amount'] ?? 0);
        $phone_number = sanitize($_POST['phone_number'] ?? '');
        $payment_method = sanitize($_POST['payment_method'] ?? '');

        // Validation
        if (!$member_id || !$amount || !$phone_number || !$payment_method) {
            return ['error' => 'Invalid input parameters', 'status' => 400];
        }

        if ($payment_method === 'mpamba') {
            return $this->gateway->initiateMpambaPayment($member_id, $amount, $phone_number);
        } elseif ($payment_method === 'airtel') {
            return $this->gateway->initiateAirtelPayment($member_id, $amount, $phone_number);
        } elseif ($payment_method === 'bank_transfer' || $payment_method === 'cash') {
            // Manual payment recording
            return $this->recordManualPayment($member_id, $amount, $payment_method);
        } else {
            return ['error' => 'Invalid payment method', 'status' => 400];
        }
    }

    /**
     * Record manual payment (cash/bank transfer)
     */
    public function recordManualPayment($member_id, $amount, $payment_method) {
        $data = [
            'member_id' => $member_id,
            'amount' => $amount,
            'payment_method' => $payment_method,
            'transaction_id' => 'MANUAL_' . time(),
            'status' => 'completed',
            'reference_number' => sanitize($_POST['reference_number'] ?? ''),
            'notes' => 'Manual payment - ' . ucfirst($payment_method)
        ];

        $result = $this->payment->createPayment($data);
        
        if ($result['success']) {
            // Update member balance
            $this->payment->updateMemberBalance($member_id, $amount);
            
            // Record transaction
            $balanceBefore = $this->payment->getMemberBalance($member_id) - $amount;
            $this->payment->recordTransaction([
                'member_id' => $member_id,
                'payment_id' => $result['payment_id'],
                'transaction_type' => 'credit',
                'amount' => $amount,
                'description' => ucfirst($payment_method) . ' payment',
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceBefore + $amount
            ]);
        }

        return $result;
    }

    /**
     * Handle payment callback
     */
    public function handleCallback($gateway) {
        $data = $_POST;
        $result = $this->gateway->handlePaymentCallback($data, $gateway);
        return $result;
    }

    /**
     * Verify payment
     */
    public function verifyPayment() {
        $transaction_id = sanitize($_POST['transaction_id'] ?? '');
        $gateway = sanitize($_POST['gateway'] ?? '');

        if (!$transaction_id || !$gateway) {
            return ['error' => 'Missing parameters', 'status' => 400];
        }

        return $this->gateway->verifyPayment($transaction_id, $gateway);
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats() {
        $totalCollected = $this->payment->getTotalCollected();
        $pendingPayments = $this->payment->getPendingPaymentsTotal();
        
        $db = Database::getInstance()->getConnection();
        
        // Get total members
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM members WHERE status = 'active'");
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $totalMembers = $row['total'];
        
        // Get payments this month
        $stmt = $db->prepare("SELECT SUM(amount) as total FROM payments WHERE status = 'completed' AND MONTH(payment_date) = MONTH(NOW()) AND YEAR(payment_date) = YEAR(NOW())");
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $monthlyCollection = $row['total'] ?? 0;

        return [
            'total_collected' => $totalCollected,
            'pending_payments' => $pendingPayments,
            'active_members' => $totalMembers,
            'monthly_collection' => $monthlyCollection
        ];
    }
}
