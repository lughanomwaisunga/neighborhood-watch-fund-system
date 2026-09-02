<?php
/**
 * Payment Class
 */

namespace App\Classes;

class Payment {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create a payment record
     */
    public function createPayment($data) {
        try {
            $sql = "INSERT INTO payments (member_id, amount, payment_method, transaction_id, status, reference_number, notes)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('idssss',
                $data['member_id'],
                $data['amount'],
                $data['payment_method'],
                $data['transaction_id'],
                $data['status'],
                $data['reference_number'],
                $data['notes']
            );

            if ($stmt->execute()) {
                return ['success' => true, 'payment_id' => $this->db->insert_id];
            }
            return ['success' => false, 'error' => $stmt->error];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get payment by ID
     */
    public function getPaymentById($payment_id) {
        $sql = "SELECT * FROM payments WHERE payment_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $payment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Get payments by member ID
     */
    public function getPaymentsByMemberId($member_id, $limit = 0, $offset = 0) {
        $sql = "SELECT * FROM payments WHERE member_id = ? ORDER BY payment_date DESC";
        
        if ($limit > 0) {
            $sql .= " LIMIT ? OFFSET ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('iii', $member_id, $limit, $offset);
        } else {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $member_id);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $payments = [];
        while ($row = $result->fetch_assoc()) {
            $payments[] = $row;
        }
        return $payments;
    }

    /**
     * Get all payments
     */
    public function getAllPayments($limit = 0, $offset = 0) {
        $sql = "SELECT p.*, m.plot_number, CONCAT(m.first_name, ' ', m.last_name) as member_name 
                FROM payments p 
                JOIN members m ON p.member_id = m.member_id 
                ORDER BY p.payment_date DESC";
        
        if ($limit > 0) {
            $sql .= " LIMIT ? OFFSET ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('ii', $limit, $offset);
        } else {
            $stmt = $this->db->prepare($sql);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $payments = [];
        while ($row = $result->fetch_assoc()) {
            $payments[] = $row;
        }
        return $payments;
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus($payment_id, $status) {
        try {
            $sql = "UPDATE payments SET status = ? WHERE payment_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('si', $status, $payment_id);

            if ($stmt->execute()) {
                return ['success' => true];
            }
            return ['success' => false, 'error' => $stmt->error];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Record a transaction
     */
    public function recordTransaction($data) {
        try {
            $sql = "INSERT INTO transactions (member_id, payment_id, transaction_type, amount, description, balance_before, balance_after)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('iisdsdd',
                $data['member_id'],
                $data['payment_id'],
                $data['transaction_type'],
                $data['amount'],
                $data['description'],
                $data['balance_before'],
                $data['balance_after']
            );

            if ($stmt->execute()) {
                return ['success' => true, 'transaction_id' => $this->db->insert_id];
            }
            return ['success' => false, 'error' => $stmt->error];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get member balance
     */
    public function getMemberBalance($member_id) {
        $sql = "SELECT balance FROM members WHERE member_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $member_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? $row['balance'] : 0.00;
    }

    /**
     * Update member balance
     */
    public function updateMemberBalance($member_id, $amount) {
        try {
            $sql = "UPDATE members SET balance = balance + ? WHERE member_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('di', $amount, $member_id);

            if ($stmt->execute()) {
                return ['success' => true];
            }
            return ['success' => false, 'error' => $stmt->error];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get total collected amount
     */
    public function getTotalCollected() {
        $sql = "SELECT SUM(amount) as total FROM payments WHERE status = 'completed'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'] ? $row['total'] : 0.00;
    }

    /**
     * Get pending payments total
     */
    public function getPendingPaymentsTotal() {
        $sql = "SELECT SUM(amount) as total FROM payments WHERE status = 'pending'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'] ? $row['total'] : 0.00;
    }
}
