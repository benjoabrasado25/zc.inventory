<?php
/**
 * Custom Roles Handler Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class ZC_Roles {

    /**
     * Create custom roles
     */
    public static function create_roles() {
        // Owner role - full access
        add_role(
            'zc_owner',
            'Inventory Owner',
            array(
                'read' => true,
                'zc_manage_cashiers' => true,
                'zc_manage_products' => true,
                'zc_view_sales' => true,
                'zc_manage_inventory' => true,
                'zc_full_access' => true,
            )
        );

        // Cashier role - limited access
        add_role(
            'zc_cashier',
            'Inventory Cashier',
            array(
                'read' => true,
                'zc_view_products' => true,
                'zc_process_sales' => true,
            )
        );

        // Add capabilities to administrator
        $admin = get_role('administrator');
        if ($admin) {
            $admin->add_cap('zc_manage_cashiers');
            $admin->add_cap('zc_manage_products');
            $admin->add_cap('zc_view_sales');
            $admin->add_cap('zc_manage_inventory');
            $admin->add_cap('zc_full_access');
        }
    }

    /**
     * Remove custom roles
     */
    public static function remove_roles() {
        remove_role('zc_owner');
        remove_role('zc_cashier');

        // Remove capabilities from administrator
        $admin = get_role('administrator');
        if ($admin) {
            $admin->remove_cap('zc_manage_cashiers');
            $admin->remove_cap('zc_manage_products');
            $admin->remove_cap('zc_view_sales');
            $admin->remove_cap('zc_manage_inventory');
            $admin->remove_cap('zc_full_access');
        }
    }

    /**
     * Check if user is owner
     */
    public static function is_owner($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $user = get_userdata($user_id);
        if (!$user) {
            return false;
        }

        return in_array('zc_owner', $user->roles) || in_array('administrator', $user->roles);
    }

    /**
     * Check if user is cashier
     */
    public static function is_cashier($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $user = get_userdata($user_id);
        if (!$user) {
            return false;
        }

        return in_array('zc_cashier', $user->roles);
    }

    /**
     * Check if user has access to inventory system
     */
    public static function has_access($user_id = null) {
        return self::is_owner($user_id) || self::is_cashier($user_id);
    }
}
