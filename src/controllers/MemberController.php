<?php
/**
 * Member Controller
 */

namespace App\Controllers;

use App\Classes\Member;
use App\Classes\Database;

class MemberController {
    private $member;

    public function __construct() {
        $this->member = new Member();
    }

    /**
     * Display all members
     */
    public function index() {
        $page = $_GET['page'] ?? 1;
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;

        $members = $this->member->getAllMembers($limit, $offset);
        $totalMembers = $this->member->getTotalMembersCount();
        $totalPages = ceil($totalMembers / $limit);

        return [
            'members' => $members,
            'total' => $totalMembers,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ];
    }

    /**
     * Show member details
     */
    public function show($member_id) {
        $member = $this->member->getMemberById($member_id);
        if (!$member) {
            return ['error' => 'Member not found', 'status' => 404];
        }
        return $member;
    }

    /**
     * Create new member
     */
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['error' => 'Invalid request method', 'status' => 400];
        }

        // Validate input
        $validation = $this->validateMemberData($_POST);
        if ($validation['valid'] === false) {
            return ['error' => $validation['errors'], 'status' => 400];
        }

        // Check if plot number already exists
        $existing = $this->member->getMemberByPlotNumber($_POST['plot_number']);
        if ($existing) {
            return ['error' => 'Plot number already registered', 'status' => 409];
        }

        $data = [
            'plot_number' => sanitize($_POST['plot_number']),
            'first_name' => sanitize($_POST['first_name']),
            'last_name' => sanitize($_POST['last_name']),
            'email' => sanitize($_POST['email']),
            'phone_number' => sanitize($_POST['phone_number']),
            'contribution_tier' => sanitize($_POST['contribution_tier']),
            'amount_due' => floatval($_POST['amount_due'])
        ];

        $result = $this->member->register($data);
        return $result;
    }

    /**
     * Update member
     */
    public function update($member_id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['error' => 'Invalid request method', 'status' => 400];
        }

        $member = $this->member->getMemberById($member_id);
        if (!$member) {
            return ['error' => 'Member not found', 'status' => 404];
        }

        $validation = $this->validateMemberData($_POST);
        if ($validation['valid'] === false) {
            return ['error' => $validation['errors'], 'status' => 400];
        }

        $data = [
            'first_name' => sanitize($_POST['first_name']),
            'last_name' => sanitize($_POST['last_name']),
            'email' => sanitize($_POST['email']),
            'phone_number' => sanitize($_POST['phone_number']),
            'contribution_tier' => sanitize($_POST['contribution_tier']),
            'status' => sanitize($_POST['status'])
        ];

        $result = $this->member->updateMember($member_id, $data);
        return $result;
    }

    /**
     * Search members
     */
    public function search() {
        $query = sanitize($_GET['q'] ?? '');
        if (empty($query)) {
            return ['error' => 'Search query is required', 'status' => 400];
        }

        $db = Database::getInstance()->getConnection();
        $searchTerm = '%' . $query . '%';
        
        $sql = "SELECT * FROM members WHERE 
                plot_number LIKE ? OR 
                first_name LIKE ? OR 
                last_name LIKE ? OR 
                email LIKE ? OR 
                phone_number LIKE ?
                ORDER BY created_at DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->bind_param('sssss', $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $members = [];
        while ($row = $result->fetch_assoc()) {
            $members[] = $row;
        }
        
        return ['members' => $members, 'count' => count($members)];
    }

    /**
     * Validate member data
     */
    private function validateMemberData($data) {
        $errors = [];

        if (empty($data['plot_number'])) {
            $errors[] = 'Plot number is required';
        }
        if (empty($data['first_name'])) {
            $errors[] = 'First name is required';
        }
        if (empty($data['last_name'])) {
            $errors[] = 'Last name is required';
        }
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required';
        }
        if (empty($data['phone_number'])) {
            $errors[] = 'Phone number is required';
        }
        if (empty($data['contribution_tier'])) {
            $errors[] = 'Contribution tier is required';
        }
        if (empty($data['amount_due']) || !is_numeric($data['amount_due'])) {
            $errors[] = 'Valid amount due is required';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
