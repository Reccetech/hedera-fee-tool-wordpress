<?php

namespace HederaFeeCalculator\WordPress\Admin;

/**
 * WordPress Admin Page (Future Enhancement)
 * For now, this is a placeholder for future admin UI features
 */
class AdminPage {
    
    public function __construct() {
        // Future: Add admin menu and settings page
        // add_action('admin_menu', [$this, 'add_admin_menu']);
        // add_action('admin_init', [$this, 'register_settings']);
    }
    
    /**
     * Future: Add admin menu item
     */
    public function add_admin_menu() {
        add_options_page(
            'Hedera Fee Calculator',
            'Hedera Fees',
            'manage_options',
            'hedera-fee-calculator',
            [$this, 'render_admin_page']
        );
    }
    
    /**
     * Future: Render admin settings page
     */
    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1>Hedera Fee Calculator Settings</h1>
            <p>Admin UI coming soon. For now, update JSON files directly in:</p>
            <code><?php echo plugin_dir_path(__FILE__) . '../../data/'; ?></code>
        </div>
        <?php
    }
}

