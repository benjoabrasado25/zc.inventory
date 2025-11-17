<?php
/**
 * Custom Roles Handler Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class ZCA_Roles {

    /**
     * Create custom roles
     */
    public static function create_roles() {
        // Remove old roles first
        remove_role('zc_owner');
        remove_role('zc_cashier');
        remove_role('zca_owner');
        remove_role('zca_cashier');

        // Owner role - full access
        add_role(
            'zca_owner',
            'Inventory Owner',
            array(
                'read' => true,
                'level_0' => true,
                'zca_manage_cashiers' => true,
                'zca_manage_products' => true,
                'zca_view_sales' => true,
                'zca_manage_inventory' => true,
                'zca_full_access' => true,
            )
        );

        // Cashier role - limited access
        add_role(
            'zca_cashier',
            'Inventory Cashier',
            array(
                'read' => true,
                'level_0' => true,
                'zca_view_products' => true,
                'zca_process_sales' => true,
            )
        );

        // Add capabilities to administrator
        $admin = get_role('administrator');
        if ($admin) {
            $admin->add_cap('zca_manage_cashiers');
            $admin->add_cap('zca_manage_products');
            $admin->add_cap('zca_view_sales');
            $admin->add_cap('zca_manage_inventory');
            $admin->add_cap('zca_full_access');
        }
    }

    /**
     * Remove custom roles
     */
    public static function remove_roles() {
        remove_role('zca_owner');
        remove_role('zca_cashier');

        // Remove capabilities from administrator
        $admin = get_role('administrator');
        if ($admin) {
            $admin->remove_cap('zca_manage_cashiers');
            $admin->remove_cap('zca_manage_products');
            $admin->remove_cap('zca_view_sales');
            $admin->remove_cap('zca_manage_inventory');
            $admin->remove_cap('zca_full_access');
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

        return in_array('zca_owner', $user->roles) || in_array('administrator', $user->roles);
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

        return in_array('zca_cashier', $user->roles);
    }

    /**
     * Check if user has access to inventory system
     */
    public static function has_access($user_id = null) {
        return self::is_owner($user_id) || self::is_cashier($user_id);
    }
}
