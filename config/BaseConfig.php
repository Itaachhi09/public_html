<?php
/**
 * Base Configuration - Global Constants
 * Defines application paths and URLs
 * All files should include this immediately after session_start()
 */

// Define base URL with proper path handling
// This ensures all redirects and form actions work regardless of how the app is accessed
if (!defined('BASE_URL')) {
    // Auto-detect the base URL based on the script location
    // For XAMPP: /public_html/
    // For standard installations: /
    $script_path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    
    // If running from /public_html, the script path will be /public_html
    // If running from root, it will be empty string
    define('BASE_URL', $script_path === '' ? '/' : $script_path . '/');
    
    // Set protocol (http or https)
    define('PROTOCOL', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://');
    
    // Full base URL with protocol and domain
    define('FULL_BASE_URL', PROTOCOL . $_SERVER['HTTP_HOST'] . BASE_URL);
}

// Additional path constants
define('BASE_PATH', __DIR__ . '/../');
define('MODULES_PATH', BASE_PATH . 'modules/');
define('CONFIG_PATH', BASE_PATH . 'config/');
define('ASSETS_PATH', BASE_PATH . 'assets/');

// API base URL
define('API_BASE_URL', BASE_URL . 'api/');

// Module URLs
define('PAYROLL_BASE', BASE_URL . 'dashboard.php?ref=payroll');
define('COMPENSATION_BASE', BASE_URL . 'dashboard.php?ref=compensation');
define('ANALYTICS_BASE', BASE_URL . 'dashboard.php?ref=analytics');
define('AUTH_BASE', BASE_URL . 'dashboard.php?ref=auth');

/**
 * Check if user has access to a specific menu item (RBAC)
 * @param string $module Module name (hr_core, payroll, compensation, hmo, analytics)
 * @param string|null $submodule Specific submodule/feature within the module
 * @return bool True if user can access, false otherwise
 */
function canAccessMenuItem($module, $submodule = null) {
    $userRole = $_SESSION['role'] ?? 'Admin';
    
    // Admin role has unrestricted access
    if ($userRole === 'Admin' || strtolower($userRole) === 'admin') {
        return true;
    }
    
    // HR Chief/Chief restrictions
    if ($userRole === 'HR_Chief' || strtolower($userRole) === 'hr_chief' || $userRole === 'hrchief') {
        $restricted = [
            'hr_core' => ['job_titles', 'employment_types', 'locations', 'roles'],
            'payroll' => ['setup_configuration', 'tax_contributions_engine', 'disbursement_bank_files', 'government_reports_compliance', 'security_audit_trail'],
            'compensation' => ['compensation_approval', 'compensation_versioning', 'pay_bonds_contracts'],
            'hmo' => ['billing'],
            'analytics' => []
        ];
        
        // If submodule specified, check if it's restricted
        if ($submodule && isset($restricted[$module]) && in_array($submodule, $restricted[$module])) {
            return false;
        }
        
        // If no submodule specified and module exists in restricted list, user can access permitted items
        return isset($restricted[$module]) ? true : false;
    }
    
    // Unknown role - deny access
    return false;
}
