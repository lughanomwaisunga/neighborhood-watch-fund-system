<?php
/**
 * Dashboard Controller
 */

namespace App\Controllers;

use App\Classes\Statement;
use App\Classes\Member;
use App\Classes\Payment;

class DashboardController {
    private $statement;
    private $member;
    private $payment;

    public function __construct() {
        $this->statement = new Statement();
        $this->member = new Member();
        $this->payment = new Payment();
    }

    /**
     * Get dashboard overview
     */
    public function getOverview() {
        $collectionSummary = $this->statement->getCollectionSummary();
        $paymentController = new PaymentController();
        $stats = $paymentController->getDashboardStats();

        return [
            'collection_summary' => $collectionSummary,
            'payment_stats' => $stats
        ];
    }

    /**
     * Get members summary
     */
    public function getMembersSummary() {
        $totalMembers = $this->member->getTotalMembersCount();
        $activeMembers = $this->member->getActiveMembersCount();
        
        return [
            'total_members' => $totalMembers,
            'active_members' => $activeMembers,
            'inactive_members' => $totalMembers - $activeMembers
        ];
    }

    /**
     * Get all members statements
     */
    public function getAllMembersStatements() {
        return $this->statement->getAllMembersStatements();
    }

    /**
     * Get member detailed statement
     */
    public function getMemberStatement($member_id) {
        $summary = $this->statement->getMemberStatementSummary($member_id);
        if (!$summary) {
            return ['error' => 'Member not found', 'status' => 404];
        }
        
        $transactions = $this->statement->getMemberTransactionHistory($member_id);
        
        return [
            'summary' => $summary,
            'transactions' => $transactions
        ];
    }
}
