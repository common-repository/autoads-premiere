<?php // uninstall remove options

if (!defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN')) exit();

// delete options
delete_option('autoads_premiere');
delete_option('autoads_premiere_options');
delete_option('autoads_premiere_field_googleid');
delete_option('autoads_premiere_field_exclusions');
