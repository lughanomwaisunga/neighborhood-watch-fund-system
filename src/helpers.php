<?php
/**
 * Helper Functions
 */

/**
 * Sanitize user input
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    return number_format($amount, 2, '.', ',');
}

/**
 * Format date
 */
function formatDate($date) {
    return date('Y-m-d', strtotime($date));
}

/**
 * Format datetime
 */
function formatDateTime($datetime) {
    return date('Y-m-d H:i:s', strtotime($datetime));
}

/**
 * Get time ago
 */
function getTimeAgo($datetime) {
    $time = strtotime($datetime);
    $current = time();
    $diff = $current - $time;
    
    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        return round($diff / 60) . ' minutes ago';
    } elseif ($diff < 86400) {
        return round($diff / 3600) . ' hours ago';
    } elseif ($diff < 604800) {
        return round($diff / 86400) . ' days ago';
    } else {
        return date('Y-m-d', $time);
    }
}

/**
 * Redirect to URL
 */
function redirect($url) {
    header('Location: ' . $url);
    exit();
}

/**
 * Set session message
 */
function setMessage($type, $message) {
    $_SESSION['message'] = [
        'type' => $type,
        'text' => $message
    ];
}

/**
 * Get and clear session message
 */
function getMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        unset($_SESSION['message']);
        return $message;
    }
    return null;
}
