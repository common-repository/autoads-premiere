<?php
/**
 * Plugin Name:       AutoAds Premiere
 * Plugin URI:        https://www.wutime.com/downloads/autoads-premiere/
 * Description:       This plugin allows you to quickly add AutoAds to your website. Install, Activate, Profit!
 * Version:           1.3.0
 * Author:            Wutime
 * Author URI:        https://www.wutime.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       autoads-premiere
 * Requires at least: 4.4
 * Requires PHP:      5.1
 */

if (!defined('WUAAP_PLUGIN_VERSION')) {
    define('WUAAP_PLUGIN_VERSION', '1.3.0');
}

// If this file is called directly, abort.
if (!defined('ABSPATH')) die();

// Only load if not already existing (maybe included from another plugin)
if (defined('WUAAP_BASE_PATH')) {
    return;
}

// Load basic path to the plugin
define('WUAAP_BASE', plugin_basename(__FILE__)); // Plugin base as used by WordPress to identify it
define('WUAAP_BASE_PATH', plugin_dir_path(__FILE__));
define('WUAAP_BASE_URL', plugin_dir_url(__FILE__));
define('WUAAP_BASE_DIR', dirname(WUAAP_BASE)); // Directory of the plugin without any paths
// General and global slug, e.g. to store options in WP
define('WUAAP_SLUG', 'autoads-premiere');
define('WUAAP_URL', 'https://www.wutime.com/downloads/autoads-premiere/');

/**
 * Check version routine
 */
function autoads_premiere_check_version() {
    if (WUAAP_PLUGIN_VERSION !== get_option('autoads_premiere_plugin_version')) {
        autoads_premiere_plugin_activation();
    }
}
add_action('plugins_loaded', 'autoads_premiere_check_version');

/**
 * Plugin activation routine
 */
function autoads_premiere_plugin_activation() {
    update_option('autoads_premiere_plugin_version', WUAAP_PLUGIN_VERSION);
}

/**
 * Add AdSense code to <head>
 */
add_action('wp_head', 'autoads_premiere_hook_head');

/**
 * Create AdSense code
 */
function autoads_premiere_hook_head() {
    $options = get_option('autoads_premiere_options');

    if (isset($options)) {
        if (!empty($options)) {
            if (!empty($options['autoads_premiere_field_googleid'])
                && !autoads_premiere_role_excluded()
                && autoads_premiere_page_included()) {
                ?>
                <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=<?php echo $options['autoads_premiere_field_googleid']; ?>" crossorigin="anonymous"></script>
                <?php
            }
        }
    }
}

/**
 * Check page type to see if it was excluded from display
 */
function autoads_premiere_page_included() {
    $page_type[] = get_post_type();
    $options = get_option('autoads_premiere_options');

    if (isset($options['autoads_premiere_page_inclusions'])) {
        if (!empty($options['autoads_premiere_page_inclusions'])) {
            if (count(array_intersect($page_type, $options['autoads_premiere_page_inclusions'])))  {
                return true;
            }
        }
    }
    return false;
}

/**
 * Check current role to see if it was excluded from display
 */
function autoads_premiere_role_excluded() {
    $user_role = autoads_premiere_get_current_user_roles();
    $options = get_option('autoads_premiere_options');

    if (isset($options['autoads_premiere_field_exclusions'])) {
        if (!empty($options['autoads_premiere_field_exclusions'])) {
            if (count(array_intersect($user_role, $options['autoads_premiere_field_exclusions'])))  {
                return true;
            }
        }
    }
    return false;
}

/**
 * Get current role
 */
function autoads_premiere_get_current_user_roles() {
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $roles = (array) $user->roles;
        return $roles; // This returns an array
        // Use this to return a single value
        // return $roles[0];
    } else {
        return array();
    }
}

/**
 * Add settings links to plugin page
 */
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'autoads_premiere_add_plugin_page_settings_link');
function autoads_premiere_add_plugin_page_settings_link($links) {
    $links[] = '<a href="' . admin_url('admin.php?page=autoads_premiere') . '">' . __('Settings') . '</a>';
    return $links;
}

/**
 * Custom option and settings
 */
function autoads_premiere_settings_init() {
    // Register a new setting for "autoads_premiere" page
    register_setting('autoads_premiere', 'autoads_premiere_options');

    // Register a new section in the "autoads_premiere" page
    add_settings_section(
        'autoads_premiere_section_developers',
        __('', 'autoads_premiere'),
        'autoads_premiere_section_developers_cb',
        'autoads_premiere'
    );

    // Register a new field in the "autoads_premiere_section_developers" section, inside the "autoads_premiere" page
    add_settings_field(
        'autoads_premiere_field_googleid',
        __('Google AdSense Publisher ID', 'autoads_premiere'),
        'autoads_premiere_field_googleid_cb',
        'autoads_premiere',
        'autoads_premiere_section_developers',
        [
            'label_for' => 'autoads_premiere_field_googleid',
            'class' => 'autoads_premiere_row',
            'autoads_premiere_custom_data' => 'custom',
        ]
    );

    // Register a new field in the "autoads_premiere_section_developers" section, inside the "autoads_premiere" page
    add_settings_field(
        'autoads_premiere_page_inclusions',
        __('Show Adverts On', 'autoads_premiere'),
        'autoads_premiere_page_inclusions_cb',
        'autoads_premiere',
        'autoads_premiere_section_developers',
        [
            'label_for' => 'autoads_premiere_page_inclusions',
            'class' => 'autoads_premiere_row',
            'autoads_premiere_custom_data' => 'custom',
        ]
    );

    // Register a new field in the "autoads_premiere_section_developers" section, inside the "autoads_premiere" page
    add_settings_field(
        'autoads_premiere_field_exclusions',
        __('Do Not Show Adverts To', 'autoads_premiere'),
        'autoads_premiere_field_exclusions_cb',
        'autoads_premiere',
        'autoads_premiere_section_developers',
        [
            'label_for' => 'autoads_premiere_field_exclusions',
            'class' => 'autoads_premiere_row',
            'autoads_premiere_custom_data' => 'custom',
        ]
    );
}

/**
 * Register our autoads_premiere_settings_init to the admin_init action hook
 */
add_action('admin_init', 'autoads_premiere_settings_init');

/**
 * Custom option and settings:
 * Callback functions
 */

// Developers section cb

// Section callbacks can accept an $args parameter, which is an array.
// $args have the following keys defined: title, id, callback.
// The values are defined at the add_settings_section() function.
function autoads_premiere_section_developers_cb($args) {
    ?>

    <img src="<?php echo plugin_dir_url(__FILE__); ?>images/banner-772x250.png" alt="<?php esc_html_e('AutoAds Premiere', 'autoads_premiere'); ?>" />

    <h1><?php esc_html_e('AutoAds Premiere', 'autoads_premiere'); ?></h1>
    <p id="<?php echo esc_attr($args['id']); ?>">

    <?php

    // Sample contact URL (may be from an unsafe place like user input)
    $contact_url = 'https://support.google.com/adsense/answer/105516?hl=en';
    // Escaping $contact_url
    $contact_url = esc_url($contact_url);

    printf(
        esc_html__('Customize your AutoAds setup here by adding your %1$s and excluding any page types and roles you don\'t want to see advertisements.', 'autoads_premiere'),
        sprintf(
            '<a href="%s" target="_blank">%s</a>',
            $contact_url,
            esc_html__('Google AdSense Publisher ID', 'autoads_premiere')
        )
    );
    ?>
    </p>
    <?php
}

// Google ID field cb

// Field callbacks can accept an $args parameter, which is an array.
// $args is defined at the add_settings_field() function.
// WordPress has magic interaction with the following keys: label_for, class.
// The "label_for" key value is used for the "for" attribute of the <label>.
// The "class" key value is used for the "class" attribute of the <tr> containing the field.
// You can add custom key-value pairs to be used inside your callbacks.
function autoads_premiere_field_googleid_cb( $args ) {
    // Get the value of the setting we've registered with register_setting()
    $options = get_option('autoads_premiere_options');

    // Check if 'label_for' exists in $args, otherwise set a default
    $label_for = isset($args['label_for']) ? $args['label_for'] : 'default_label';

    // Ensure $options is an array before trying to access it
    $google_id_value = (isset($options[$label_for]) && is_array($options)) ? esc_attr($options[$label_for]) : '';

    // Output the field
    ?>
    <input type="text"
           size="30"
           id="<?php echo esc_attr($label_for); ?>"
           data-custom="<?php echo esc_attr($args['autoads_premiere_custom_data']); ?>"
           name="autoads_premiere_options[<?php echo esc_attr($label_for); ?>]"
           value="<?php echo $google_id_value; ?>">
       
    <p class="description">
        <div>
        <?php
        // Sample contact URL (may be from an unsafe place like user input)
        $contact_url = 'https://support.google.com/adsense/answer/105516?hl=en';
        // Escaping $contact_url
        $contact_url = esc_url($contact_url);

        printf(
            esc_html__('Add your %1$s ( Example: ca-pub-1234567890123456 )', 'autoads_premiere'),
            sprintf(
                '<a href="%s" target="_blank">%s</a>',
                $contact_url,
                esc_html__('Google AdSense Publisher ID', 'autoads_premiere')
            )
        );
        ?>
        </div>
    </p>
    </p>
    <?php
}


/**
 * Get a list of all public page types and allow the user to select which to show advertisement code on
 */
function autoads_premiere_editable_page_types() {
    //$page_types = array('pages'=>'pages','posts'=>'posts');
    //$page_types = apply_filters('page_types', $page_types);
    //return $page_types;
    $args = array(
        'public' => true,
        //'_builtin' => false,
    );
    $output = 'names'; // Names or objects, note names is the default
    $operator = 'and'; // 'and' or 'or'
    $post_types = get_post_types($args, $output, $operator);
    $types = array();
    foreach ($post_types as $post_type) {
        $types[$post_type] = $post_type;
    }
    $types = apply_filters('page_types', $types);
    return $types;
}

function autoads_premiere_editable_roles() {
    global $wp_roles;

    $all_roles = $wp_roles->roles;
    $editable_roles = apply_filters('editable_roles', $all_roles);

    return $editable_roles;
}

function autoads_premiere_get_checked($needle, $haystack, $id) {

    if (isset($haystack) && isset($haystack[$id])) {
        if (is_array($haystack[$id])) {
            if (in_array($needle, $haystack[$id])) {
                return 'checked';
            }
        }
    }

    return '';
}

// Field callbacks can accept an $args parameter, which is an array.
// $args is defined at the add_settings_field() function.
// WordPress has magic interaction with the following keys: label_for, class.
// The "label_for" key value is used for the "for" attribute of the <label>.
// The "class" key value is used for the "class" attribute of the <tr> containing the field.
// You can add custom key-value pairs to be used inside your callbacks.
function autoads_premiere_page_inclusions_cb($args) {
    // Get the value of the setting we've registered with register_setting()
    $options = get_option('autoads_premiere_options');
    $page_types = autoads_premiere_editable_page_types();

    // Output the field
    ?>

    <?php foreach ($page_types as $key => $page_type) { ?>
        <div>
            <label>
                <input type='checkbox'
                       <?php echo autoads_premiere_get_checked($key, $options, 'autoads_premiere_page_inclusions'); ?>
                       name="autoads_premiere_options[<?php echo esc_attr($args['label_for']); ?>][]"
                       value="<?php echo $key; ?>">
                <?php echo ucwords($key); ?>
            </label>
        </div>
    <?php } ?>

    <p class="description" style="margin-top:10px;">
        <?php esc_html_e('To abide by AdSense rules and ensure advertisements aren\'t shown on error and login pages, please specify which types of content to show advertisements on.', 'autoads_premiere'); ?>
    </p>
    <?php
}

// Field callbacks can accept an $args parameter, which is an array.
// $args is defined at the add_settings_field() function.
// WordPress has magic interaction with the following keys: label_for, class.
// The "label_for" key value is used for the "for" attribute of the <label>.
// The "class" key value is used for the "class" attribute of the <tr> containing the field.
// You can add custom key-value pairs to be used inside your callbacks.
function autoads_premiere_field_exclusions_cb($args) {
    // Get the value of the setting we've registered with register_setting()
    $options = get_option('autoads_premiere_options');
    $roles = autoads_premiere_editable_roles();

    // Output the field
    ?>

    <?php foreach ($roles as $key => $role) { ?>
        <div>
            <label>
                <input type='checkbox'
                       <?php echo autoads_premiere_get_checked($key, $options, 'autoads_premiere_field_exclusions'); ?>
                       name="autoads_premiere_options[<?php echo esc_attr($args['label_for']); ?>][]"
                       value="<?php echo $key; ?>">
                <?php echo ucwords($key); ?>
            </label>
        </div>
    <?php } ?>

    <p class="description" style="margin-top:10px;">
        <?php esc_html_e('Do not show advertisements to the roles selected above.', 'autoads_premiere'); ?>
    </p>
    <?php
}

/**
 * Top level menu
 */
function autoads_premiere_options_page() {
    // Add top level menu page
    add_options_page(
        __('', 'autoads-premiere'),
        __('AutoAds Premiere', 'autoads-premiere'),
        'manage_options',
        'autoads_premiere',
        'autoads_premiere_options_page_html'
    );
}

/**
 * Register our autoads_premiere_options_page to the admin_menu action hook
 */
add_action('admin_menu', 'autoads_premiere_options_page');

/**
 * Top level menu:
 * Callback functions
 */
function autoads_premiere_options_page_html() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }

    // Check if the user has submitted the settings
    // WordPress will add the "settings-updated" $_GET parameter to the URL
    if (isset($_GET['settings-updated']) && !has_action('admin_notices', 'autoads_premiere_settings_saved_notice')) {
        // Add settings saved message with the class of "updated"
        add_settings_error('autoads_premiere_messages', 'autoads_premiere_message', __('Settings Saved', 'autoads_premiere'), 'updated');
    }

    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            // Output security fields for the registered setting "autoads_premiere"
            settings_fields('autoads_premiere');

            // Output setting sections and their fields
            // (sections are registered for "autoads_premiere", each field is registered to a specific section)
            do_settings_sections('autoads_premiere');

            // Output save settings button
            submit_button('Save Settings');
            ?>
        </form>
    </div>
    <?php
}


