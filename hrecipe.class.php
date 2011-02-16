<?php

include dirname( __FILE__ ).'/plugin_base.php';
include_once dirname( __FILE__ ).'/models/options_db.php';


$hrecipe_pagehook = null;

class hrecipe extends PluginBase {

    var $firephp;

    function init() {
        $this->register_plugin('hrecipe', __FILE__);    
    }


    function hrecipe_activate() {
        $this->register_plugin('hrecipe', __FILE__);
        hrecipe_add_options();
        // Register the scripts for Recip.ly.
        // The jQuery script is probably redundant, and 
        // in any case, Recip.ly shouldn't depend on a 
        // particular version. IMO.  Makes for too much
        // overhead for hRecipe maintenance.
    }

    function hrecipe_deactivate() {
        hrecipe_delete_options();
    }

    function hrecipe_plugin_init() {

        if (get_user_option('rich_editing') == 'true') {
            // Include hooks for TinyMCE plugin
            add_filter('mce_external_plugins', array($this, 'hrecipe_plugin_mce_external_plugins'));
            add_filter('mce_buttons_3', array($this, 'hrecipe_plugin_mce_buttons'));
        }
        global $hrecipe_plugin_url;
        load_plugin_textdomain('hrecipe', $hrecipe_plugin_url.'/lang', 'hrecipe/lang');
        
        wp_register_script('hrecipe-jquery-min', 'http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js');
        //wp_register_script('hrecipe-reciply', 'http://www.recip.ly/static/js/jquery-reciply.js');
        wp_enqueue_script('hrecipe-jquery-min');
        //wp_enqueue_script('hrecipe-reciply');
    }


    function hrecipe_plugin_menu() {

        if (function_exists('add_options_page')) {

            /* This pagehook stuff is critical.
             * TODO: Look at the return values for add_options_page, write
             * up a detailed report of what's going on here.
             */
            global $hrecipe_pagehook;
            $hrecipe_pagehook = add_options_page('hRecipe Options', 'hRecipe', 8, 'hrecipe.class', array($this, 'hrecipe_plugin_options_page'));
            add_action('load-'.$hrecipe_pagehook, array($this,'on_load_page'));
        }
    }


    function on_load_page() {
        wp_enqueue_script('common');
        wp_enqueue_script('wp-lists');
        wp_enqueue_script('postbox');
    }


    /**
     * Not using this currently, kind of obnoxious.
     * @return nothing
     */
    function hrecipe_admin_footer() {

        $plugin_data = get_plugin_data(__FILE__);
        printf('%1$s plugin | Version %2$s | by %3$s<br />', $plugin_data['Title'], $plugin_data['Version'], $plugin_data['Author']);
    }

    function hrecipe_plugin_options_page() {

        global $hrecipe_plugin_url;
        global $hrecipe_plugin_url1;

        if ( $_GET['sub'] == 'support' ) {
            return $this->render_admin('support');
        } else {
            include ('view/admin/options.php');
        }
    }


    function hrecipe_plugin_mce_external_plugins($plugins) {

        global $hrecipe_plugin_url;
        $plugins['hrecipe_plugin'] = $hrecipe_plugin_url.'/tinymceplugin/editor_plugin.js';
        return $plugins;
    }


    function hrecipe_plugin_mce_buttons($buttons) {

        array_push($buttons, 'hrecipe_button');
        return $buttons;
    }

    function hrecipe_plugin_footer() {

        //global $hrecipe_plugin_url;
        include ('hrecipe_format.php');
    }

    function add_hrecipe_stylesheet() {

        $css_url = WP_PLUGIN_URL.'/hrecipe/hrecipe.css';
        $css_file = WP_PLUGIN_DIR.'/hrecipe/hrecipe.css';
        if (file_exists($css_file)) {
            wp_register_style('hrecipe_stylesheet', $css_url);
            wp_enqueue_style('hrecipe_stylesheet');
        }
    }

    function add_hrecipe_editor_stylesheet() {

        $css_url = WP_PLUGIN_URL.'/hrecipe/hrecipe-editor.css';
        $css_file = WP_PLUGIN_DIR.'/hrecipe/hrecipe-editor.css';
        if (file_exists($css_file)) {
            wp_register_style('hrecipe_editor_stylesheet', $css_url);
            wp_enqueue_style('hrecipe_editor_stylesheet');
        }
    }

}

?>