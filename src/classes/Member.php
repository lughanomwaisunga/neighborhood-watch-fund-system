<?php
/**
 * Member Class
 */

namespace App\Classes;

class Member {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Register a new member
     */
    public function register($data) {
        try {
            $sql = "INSERT INTO members (plot_number, first_name, last_name, email, phone_number, contribution_tier, amount_due)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('ssssssd', 
                $data['plot_number'],
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $data['phone_number'],
                $data['contribution_tier'],
                $data['amount_due']
            );

            if ($stmt->execute()) {
                return ['success' => true, 'member_id' => $this->db->insert_id];
            }
            return ['success' => false, 'error' => $stmt->error];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get member by ID
     */
    public function getMemberById($member_id) {
        $sql = "SELECT * FROM members WHERE member_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $member_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Get all members
     */
    public function getAllMembers($limit = 0, $offset = 0) {
        $sql = "SELECT * FROM members ORDER BY created_at DESC";
        
        if ($limit > 0) {
            $sql .= " LIMIT ? OFFSET ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('ii', $limit, $offset);
        } else {
            $stmt = $this->db->prepare($sql);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $members = [];
        while ($row = $result->fetch_assoc()) {
            $members[] = $row;
        }
        return $members;
    }

    /**
     * Update member
     */
    public function updateMember($member_id, $data) {
        try {
            $sql = "UPDATE members SET first_name = ?, last_name = ?, email = ?, phone_number = ?, contribution_tier = ?, status = ? WHERE member_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('ssssssi',
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $data['phone_number'],
                $data['contribution_tier'],
                $data['status'],
                $member_id
            );

            if ($stmt->execute()) {
                return ['success' => true];
            }
            return ['success' => false, 'error' => $stmt->error];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get member by plot number
     */
    public function getMemberByPlotNumber($plot_number) {
        $sql = "SELECT * FROM members WHERE plot_number = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $plot_number);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Get total members count
     */
    public function getTotalMembersCount() {
        $sql = "SELECT COUNT(*) as total FROM members";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'];
    }

    /**
     * Get active members count
     */
    public function getActiveMembersCount() {
        $sql = "SELECT COUNT(*) as total FROM members WHERE status = 'active'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'];
    }
}
