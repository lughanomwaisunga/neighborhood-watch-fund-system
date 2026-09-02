<?php
/**
 * Statement Class - Generate member statements
 */

namespace App\Classes;

class Statement {
    private $db;
    private $member;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->member = new Member();
    }

    /**
     * Get member statement summary
     */
    public function getMemberStatementSummary($member_id) {
        try {
            $member = $this->member->getMemberById($member_id);
            if (!$member) {
                return null;
            }

            $sql = "SELECT 
                        COUNT(*) as total_payments,
                        SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_paid,
                        SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_amount
                    FROM payments 
                    WHERE member_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $member_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats = $result->fetch_assoc();

            return [
                'member' => $member,
                'total_payments' => $stats['total_payments'] ?? 0,
                'total_paid' => $stats['total_paid'] ?? 0.00,
                'pending_amount' => $stats['pending_amount'] ?? 0.00,
                'outstanding_balance' => $member['amount_due'] - ($stats['total_paid'] ?? 0.00)
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get member transaction history
     */
    public function getMemberTransactionHistory($member_id, $limit = 50, $offset = 0) {
        $sql = "SELECT * FROM transactions WHERE member_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('iii', $member_id, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $transactions = [];
        while ($row = $result->fetch_assoc()) {
            $transactions[] = $row;
        }
        return $transactions;
    }

    /**
     * Get all members statements summary
     */
    public function getAllMembersStatements() {
        $sql = "SELECT 
                    m.*,
                    COUNT(p.payment_id) as total_payments,
                    SUM(CASE WHEN p.status = 'completed' THEN p.amount ELSE 0 END) as total_paid,
                    SUM(CASE WHEN p.status = 'pending' THEN p.amount ELSE 0 END) as pending_amount
                FROM members m
                LEFT JOIN payments p ON m.member_id = p.member_id
                GROUP BY m.member_id
                ORDER BY m.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $statements = [];
        while ($row = $result->fetch_assoc()) {
            $row['outstanding_balance'] = $row['amount_due'] - ($row['total_paid'] ?? 0.00);
            $statements[] = $row;
        }
        return $statements;
    }

    /**
     * Generate PDF statement (requires mpdf library)
     */
    public function generatePDFStatement($member_id) {
        try {
            $summary = $this->getMemberStatementSummary($member_id);
            if (!$summary) {
                return ['success' => false, 'error' => 'Member not found'];
            }

            $transactions = $this->getMemberTransactionHistory($member_id, 100);

            $html = $this->generateStatementHTML($summary, $transactions);

            // This requires mpdf to be installed via composer
            if (class_exists('\\Mpdf\\Mpdf')) {
                $mpdf = new \Mpdf\Mpdf();
                $mpdf->WriteHTML($html);
                $filename = 'statement_' . $member_id . '_' . date('Y-m-d') . '.pdf';
                $mpdf->Output($filename, 'D');
                return ['success' => true];
            }

            return ['success' => false, 'error' => 'PDF library not available'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Generate HTML statement
     */
    private function generateStatementHTML($summary, $transactions) {
        $member = $summary['member'];
        $date = date('Y-m-d');
        $appName = APP_NAME ?? 'Neighborhood Watch Fund System';

        $html = "<h2>{$appName}</h2>";
        $html .= "<h3>Member Statement</h3>";
        $html .= "<p>Generated on: {$date}</p>";
        
        $html .= "<h4>Member Information</h4>";
        $html .= "<table border='1' cellpadding='10'>";
        $html .= "<tr><td><strong>Plot Number:</strong></td><td>{$member['plot_number']}</td></tr>";
        $html .= "<tr><td><strong>Name:</strong></td><td>{$member['first_name']} {$member['last_name']}</td></tr>";
        $html .= "<tr><td><strong>Email:</strong></td><td>{$member['email']}</td></tr>";
        $html .= "<tr><td><strong>Phone:</strong></td><td>{$member['phone_number']}</td></tr>";
        $html .= "<tr><td><strong>Tier:</strong></td><td>" . ucfirst($member['contribution_tier']) . "</td></tr>";
        $html .= "</table>";

        $html .= "<h4>Account Summary</h4>";
        $html .= "<table border='1' cellpadding='10'>";
        $html .= "<tr><td><strong>Amount Due:</strong></td><td>" . number_format($summary['member']['amount_due'], 2) . "</td></tr>";
        $html .= "<tr><td><strong>Total Paid:</strong></td><td>" . number_format($summary['total_paid'], 2) . "</td></tr>";
        $html .= "<tr><td><strong>Outstanding Balance:</strong></td><td>" . number_format($summary['outstanding_balance'], 2) . "</td></tr>";
        $html .= "<tr><td><strong>Pending Payments:</strong></td><td>" . number_format($summary['pending_amount'], 2) . "</td></tr>";
        $html .= "</table>";

        $html .= "<h4>Transaction History</h4>";
        $html .= "<table border='1' cellpadding='10'>";
        $html .= "<tr><th>Date</th><th>Type</th><th>Amount</th><th>Description</th><th>Balance After</th></tr>";
        
        foreach ($transactions as $transaction) {
            $html .= "<tr>";
            $html .= "<td>" . date('Y-m-d', strtotime($transaction['created_at'])) . "</td>";
            $html .= "<td>" . ucfirst($transaction['transaction_type']) . "</td>";
            $html .= "<td>" . number_format($transaction['amount'], 2) . "</td>";
            $html .= "<td>{$transaction['description']}</td>";
            $html .= "<td>" . number_format($transaction['balance_after'], 2) . "</td>";
            $html .= "</tr>";
        }
        
        $html .= "</table>";

        return $html;
    }

    /**
     * Generate collection summary report
     */
    public function getCollectionSummary() {
        $sql = "SELECT 
                    COUNT(DISTINCT m.member_id) as total_members,
                    SUM(m.amount_due) as total_expected,
                    SUM(CASE WHEN p.status = 'completed' THEN p.amount ELSE 0 END) as total_collected,
                    SUM(CASE WHEN p.status = 'pending' THEN p.amount ELSE 0 END) as pending_collection
                FROM members m
                LEFT JOIN payments p ON m.member_id = p.member_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $summary = $result->fetch_assoc();

        if ($summary) {
            $summary['collection_percentage'] = $summary['total_expected'] > 0 
                ? round(($summary['total_collected'] / $summary['total_expected']) * 100, 2)
                : 0;
        }

        return $summary;
    }
}
