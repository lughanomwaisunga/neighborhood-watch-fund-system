<?php
/**
 * Application Constants
 */

define('APP_NAME', $_ENV['APP_NAME'] ?? 'Neighborhood Watch Fund System');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
define('APP_DEBUG', $_ENV['APP_DEBUG'] ?? false);
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost:8000');

// Contribution Tiers
define('CONTRIBUTION_TIERS', [
    'standard' => ['name' => 'Standard', 'amount' => 50.00],
    'premium' => ['name' => 'Premium', 'amount' => 100.00],
    'vip' => ['name' => 'VIP', 'amount' => 200.00]
]);

// Payment Methods
define('PAYMENT_METHODS', ['mpamba', 'airtel', 'bank_transfer', 'cash']);

// Payment Status
define('PAYMENT_STATUS', [
    'pending' => 'Pending',
    'completed' => 'Completed',
    'failed' => 'Failed',
    'cancelled' => 'Cancelled'
]);

// Member Status
define('MEMBER_STATUS', [
    'active' => 'Active',
    'inactive' => 'Inactive',
    'suspended' => 'Suspended'
]);

// Pagination
define('ITEMS_PER_PAGE', 10);

// Error Messages
define('ERROR_MESSAGES', [
    'db_error' => 'Database error occurred. Please try again.',
    'not_found' => 'Record not found.',
    'invalid_input' => 'Invalid input provided.',
    'unauthorized' => 'You are not authorized to perform this action.'
]);
