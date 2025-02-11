<?php
/**
 * Plugin Name: Content/Page Restriction
 * Plugin URI: 
 * Description: Content/Page Restriction is a simple yet powerful WordPress plugin that allows administrators to control access to pages and specific content based on user roles. With an easy-to-use interface, you can restrict entire pages or sections of content inside posts using a shortcode.
 * Version: 1.0
 * Text Domain: content-page-restriction
 * Author: Aakash Sharma
 * Requires at least: 6.1
 * Tested up to: 6.7
 * PHP Version: 7.4
 * License: GPL3
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class ContentPageRestriction {
    public function __construct() {
		// Add a custom menu item in the WordPress admin panel.
		add_action('admin_menu', [$this, 'add_admin_menu_cr']);
        add_action('add_meta_boxes', [$this, 'register_meta_boxes']);
        add_action('save_post', [$this, 'save_meta_box']);
        add_action('template_redirect', [$this, 'restrict_page_access']);
		add_shortcode('restrict_content', [$this, 'restrict_content_shortcode']);
		add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'add_settings_link']);
		add_action('admin_init', [$this, 'register_error_message_settings']);
    }

	public function add_admin_menu_cr() {
        add_menu_page(
            __('Content Restrict', 'content-page-restriction'),  // The title of the parent menu page
            __('Content Restrict', 'content-page-restriction'),  // The label of the parent menu item
            'manage_options',   // The capability required to access the parent menu
            'content-restrict', // The slug of the parent menu
            [$this, 'admin_page_cr'], // The function to display the parent menu page content  
            'dashicons-lock',   // The Icon for the parent menu page content
            20
        );
    }
	
	/**
	* Render the content for the plugin's admin page.
	*/
    public function admin_page_cr() {
    ?>
		<h1><?php esc_html_e('Settings', 'content-page-restriction'); ?></h1>
		<div class="main-wrapper">
			<div class="wrap">
				<h2><?php esc_html_e('Plugin Description: Content/Page Restriction:', 'content-page-restriction'); ?></h2>
				<div class="form-layout">
					<p><?php esc_html_e('Content/Page Restriction is a simple yet powerful WordPress plugin that allows administrators to control access to pages and specific content based on user roles. With an easy-to-use interface, you can restrict entire pages or sections of content inside posts using a shortcode.', 'content-page-restriction'); ?></p>
					
					<p><strong><?php esc_html_e('Features:', 'content-page-restriction'); ?></strong></p>
					<ul>
						<li>✅ <?php esc_html_e('Restrict Page Access: Assign user roles to specific pages and prevent unauthorized access.', 'content-page-restriction'); ?></li>
                        <li>✅ <?php esc_html_e('Custom Message Display: Instead of redirecting users, show a message if they are not authorized.', 'content-page-restriction'); ?></li>
                        <li>✅ <?php esc_html_e('Shortcode Support: Use <code>[restrict_content]Your Content[/restrict_content]</code> to protect content within posts or pages.', 'content-page-restriction'); ?></li>
                        <li>✅ <?php esc_html_e('Role-Based Access: Only users with assigned roles can view restricted pages or content.', 'content-page-restriction'); ?></li>
                        <li>✅ <?php esc_html_e('Easy Setup: Configure permissions directly from the page editor without coding.', 'content-page-restriction'); ?></li>
                    </ul>
					
					<p><strong><?php esc_html_e('Use of Shortcode:', 'content-page-restriction'); ?></strong></p>
					<code>
					[restrict_content]<?php esc_html_e('Your site content goes here..', 'content-page-restriction'); ?>[/restrict_content]
					</code>
					
					<p><?php esc_html_e('You can put this shortcode in posts or pages.', 'content-page-restriction'); ?></p>
                </div>
				
				<h1><?php esc_html_e('Error Message Settings', 'content-page-restriction'); ?></h1>
                <form method="post" action="options.php">
					<?php
					settings_fields('error_message_group');
					do_settings_sections('error-message-settings');
					submit_button();
					?>
				</form>

			</div>
		</div>
    <?php
    }
	
    /**
     * Register meta box for pages.
     */
    public function register_meta_boxes() {
        add_meta_box('skcrt', __('Page Permission by User Roles', 'content-page-restriction'), [$this, 'display_meta_box'], 'page');
    }

    /**
     * Display the meta box content.
     *
     * @param WP_Post $post Current post object.
     */
    public function display_meta_box($post) {
        include plugin_dir_path(__FILE__) . 'form.php';
    }

    /**
     * Save meta box content.
     *
     * @param int $post_id Post ID
     */
    public function save_meta_box($post_id) {
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
		
		if ($parent_id = wp_is_post_revision($post_id)) {
			$post_id = $parent_id;
		}

		// Check if the current user has permission to edit the post
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		if (!empty($_POST['hcf_user_role']) && is_array($_POST['hcf_user_role'])) {
			$user_roles = wp_unslash($_POST['hcf_user_role']); // Remove slashes
			$sanitized_roles = array_map('sanitize_text_field', $user_roles); // Sanitize input
			
			// Encode JSON safely
			$encoded_roles = wp_json_encode($sanitized_roles, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			
			update_post_meta($post_id, 'hcf_user_role', $encoded_roles);
		}
	}

    /**
     * Restrict page access based on user roles.
     */
    public function restrict_page_access() {
        if (!is_page()) return;
        
        if (is_user_logged_in()) {
            $user_id  = get_current_user_id();
            $value    = get_post_meta(get_the_ID(), 'hcf_user_role', true);
            $savedVal = json_decode($value, true);
            
            $user_meta  = get_userdata($user_id);
            $user_roles = $user_meta->roles;
            
            if (!empty($savedVal) && !in_array($user_roles[0], $savedVal)) {
				$errorMsg2 = get_option('error_message_404', '');
				add_filter('the_content', function($content) use ($errorMsg2) {
					return '<div style="color: red; font-weight: bold; text-align: center;">'.esc_html($errorMsg2).'</div>';
				});
			}
        }else{
			$value    = get_post_meta(get_the_ID(), 'hcf_user_role', true);
            $savedVal = json_decode($value, true);
			if (!empty($savedVal) && in_array('guest', $savedVal)) {
				return;
			}else{
				$errorMsg1 = get_option('error_message_500');
				add_filter('the_content', function($content) use ($errorMsg1) {
					return '<div style="color: red; font-weight: bold; text-align: center;">'.esc_html($errorMsg1).'</div>';
				});
			}
        }
    }
	
	public function restrict_content_shortcode($atts, $content = null) {
		$value    = get_post_meta(get_the_ID(), 'hcf_user_role', true);
		$savedVal = json_decode($value, true);
		
		if (!is_user_logged_in()) {
			if (!empty($savedVal) && in_array('guest', $savedVal)) {
				return do_shortcode($content); // Show the restricted content
			}else{
				$errorMsg1 = get_option('error_message_500', '');
				add_filter('the_content', function($content) use ($errorMsg1) {
					return '<div style="color: red; font-weight: bold; text-align: center;">'.esc_html($errorMsg1).'</div>';
				});
			}
		}else{
			$user_id  = get_current_user_id();
			
			$user_meta  = get_userdata($user_id);
			$user_roles = $user_meta->roles;

			if (!empty($savedVal) && !in_array($user_roles[0], $savedVal)) {
				$errorMsg2 = get_option('error_message_404', '');
				add_filter('the_content', function($content) use ($errorMsg2){
					return '<div style="color: red; font-weight: bold; text-align: center;">'.esc_html($errorMsg2).'</div>';
				});
			}

			return do_shortcode($content); // Show the restricted content
		}
	}
	
	// Add settings link in the Plugins list
	public function add_settings_link($links) {
		$settings_link = '<a href="admin.php?page=content-restrict">Settings</a>';
		array_push($links, $settings_link);
		return $links;
	}
	
	public function register_error_message_settings() {
		$args = array(
			'type' => 'string', 
			'sanitize_callback' => 'sanitize_text_field',
			'default' => NULL,
		);
		
        register_setting('error_message_group', 'error_message_404', $args);
        register_setting('error_message_group', 'error_message_500', $args);

        add_settings_section(
            'error_messages_section',
            __('Customize Error Messages', 'content-page-restriction'),
            null,
            'error-message-settings'
        );

        add_settings_field(
            'error_message_404',
            __('Error message when Permissions not matched', 'content-page-restriction'),
            [$this, 'error_message_404_field'],
            'error-message-settings',
            'error_messages_section'
        );

        add_settings_field(
            'error_message_500',
            __('Non Logged In Error Message', 'content-page-restriction'),
            [$this, 'error_message_500_field'],
            'error-message-settings',
            'error_messages_section'
        );
    }

	public function error_message_404_field() {
		$value = get_option('error_message_404', '');
		echo '<textarea name="error_message_404" rows="5" cols="50">' . esc_textarea($value) . '</textarea>';
	}

	public function error_message_500_field() {
		$value = get_option('error_message_500', '');
		echo '<textarea name="error_message_500" rows="5" cols="50">' . esc_textarea($value) . '</textarea>';
	}
	
}

// Initialize the plugin
new ContentPageRestriction();
