<?php

include dirname( __FILE__ ).'/plugin_base.php';
include_once dirname( __FILE__ ).'/models/options_db.php';
//include dirname( __FILE__ ).'/controller/shortcode.php';


$hrecipe_pagehook = null;
$hrecipe_options_file = 'view/admin/options.php';
//$hrecipe_options_file = __FILE__;
  
include('hrecipe_localize_vars.php');

class hrecipe extends PluginBase {

    //var $firephp;
    
    //var $hrecipe_options_file = 'hrecipe/view/admin/options2.php';
    //var $hrecipe_options_file = __FILE__;

    var $meta_box = array(
        'id' => 'recipe-info',
        'title' => 'Recipe Information',
        'page' => 'recipe',
        'context' => 'normal',
        'priority' => 'high',
        'fields' => array(
            array(
                'name' => 'Source URL',
                'id' => 'recipe_url',
                'type' => 'text'
            ),
            array(
                'name' => 'Summary',
                'id' => 'recipe_summary',
                'type' => 'text'
            ),
            array(
                'name' => 'Ingredients',
                'desc' => '(one ingredient per line)',
                'id' => 'recipe_ingredients',
                'type' => 'textarea'
            ),
            array(
                'name' => 'Instructions',
                'desc' => '(one step per line)',
                'id' => 'recipe_instructions',
                'type' => 'textarea'
            ),
            array(
                'name' => 'Preparation time',
                'desc' => '(in minutes)',
                'id' => 'recipe_preptime',
                'type' => 'text',
                'width' => '10'
            ),
            array(
                'name' => 'Cooking time',
                'desc' => '(in minutes)',
                'id' => 'recipe_cooktime',
                'type' => 'text',
                'width' => '10'
            ),
            array(
                'name' => 'Yield',
                'desc' => '(number of servings)',
                'id' => 'recipe_yield',
                'type' => 'text',
                'width' => '10'
            ),
            array(
                'name' => 'My rating',
                'desc' => 'Number of stars',
                'id' => 'recipe_rating',
                'type' => 'select',
                'options' => array('', '1', '2', '3', '4', '5')
            )
        )
    );

    function init() {
        $this->register_plugin('hrecipe', __FILE__);    
    }


    function hrecipe_activate() {
        $this->register_plugin('hrecipe', __FILE__);
        hrecipe_add_options();
    }


    function hrecipe_deactivate() {
    }

    // Stub
    function hrecipe_uninstall() {
      
      //hrecipe_delete_options();
    }

    function hrecipe_plugin_init() {

        if (get_user_option('rich_editing') == 'true') {
            // Include hooks for TinyMCE plugin
            add_filter('mce_external_plugins', array($this, 'hrecipe_plugin_mce_external_plugins'));
            add_filter('mce_buttons_3', array($this, 'hrecipe_plugin_mce_buttons'));
        }
        global $hrecipe_plugin_url;
        load_plugin_textdomain('hrecipe', $hrecipe_plugin_url.'/lang', 'hrecipe/lang');
        
        //wp_register_script('hrecipe-reciply', 'http://www.recip.ly/static/js/jquery-reciply.js');
        //wp_enqueue_script('hrecipe-reciply');
        wp_register_script('hrecipeformat',plugins_url('hrecipe/js/hrecipe_format.js', dirname(__FILE__)),'','',true);
        wp_localize_script('hrecipeformat','hrecipe_handle',hrecipe_localize_vars());
        wp_register_script('hrecipescript',plugins_url('hrecipe/js/hrecipescript.js', dirname(__FILE__)),'','',true);

        wp_enqueue_script('hrecipeformat');                
        //wp_enqueue_script('hrecipescript');                

        // add the recipe custom post type
        $this->hrecipe_register_post_type('hrecipe', __FILE__);
        
        // add the recipe taxonomy system
        $this->hrecipe_register_taxonomy('hrecipe', __FILE__);

        // add a filter to the post class and the_content to add hrecipe, the microformat is the whole point!
        add_filter('post_class', array($this, 'recipe_post_class'));
        add_filter('the_content', array($this, 'recipe_the_content'));

        // have the new recipe custom post type act like a post (not a page)
        add_filter('pre_get_posts', array($this, 'hrecipe_get_posts'));

        // create a template file just in case the theme doesn't have one
        add_action('template_redirect', array($this, 'recipe_template_redirect'), 5);
    }
    
    function register_mysettings() {

       global $hrecipe_options_file;

        register_setting('hrecipe_options_group', 'hrecipe_options');//, array($this,'hrecipe_options_validate')); #next rev
        add_settings_section('hrecipe_labels', '', 'hrecipe_labels_text', $hrecipe_options_file);
        add_settings_section('hrecipe_structure', '', 'hrecipe_structure_text', $hrecipe_options_file);        

        add_settings_section('hrecipe_styling', '', array($this,'hrecipe_styling_text'), $hrecipe_options_file);
        add_settings_field('hrecipe_border_color', __('Border color', 'hrecipe'), array($this,'border_color'), $hrecipe_options_file, 'hrecipe_styling');        
        //add_settings_field('hrecipe_background_color', 'Background color', array($this,'background_color'), $hrecipe_options_file, 'hrecipe_styling');        
    }

    function hrecipe_register_post_type() {
        // add a new custom post type for recipes
        register_post_type('recipe', array(
            'labels' => array(
                'name' => __('Recipes','hrecipe'),
                'singular_name' => __('Recipe','hrecipe')
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => __('recipe','hrecipe')),
            'supports' => array(
                'title',
                'editor',
                'thumbnail',
                'comments',
                'revisions'
            ),
            'taxonomies' => array('meal', 'diet', 'culinary', 'ingredients'),
            'register_meta_box_cb' => array($this, 'recipe_add_meta_box')
        ));

        // add the save post for the new meta boxes we just created
        add_action('save_post', array($this, 'recipe_save_data'));
    }

    function hrecipe_register_taxonomy() {
        register_taxonomy(
            'meal',
            'recipe',
            array(
                'hierarchical' => true,
                'label' => 'Meal Type',
                'query_var' => true,
                'rewrite' => true
            )
        );

        register_taxonomy(
            'diet',
            'recipe',
            array(
                'hierarchical' => true,
                'label' => 'Diet Type',
                'query_var' => true,
                'rewrite' => true
            )
        );

        register_taxonomy(
            'culinary',
            'recipe',
            array(
                'hierarchical' => true,
                'label' => 'Culinary Tradition',
                'query_var' => true,
                'rewrite' => true
            )
        );

        register_taxonomy(
            'ingredients',
            'recipe',
            array(
                'hierarchical' => false,
                'label' => 'Major Ingredients',
                'query_var' => true,
                'rewrite' => true
            )
        );
    }

    function hrecipe_get_posts($query) {
        if (!is_admin() && (is_archive() || is_author() || is_category() || is_date() || is_feed() || is_home() || is_search() || is_tag() || is_tax()))
            $query->set( 'post_type', array( 'post', 'recipe' ) );

        return $query;
    }

    function recipe_add_meta_box() {
        add_meta_box($this->meta_box['id'], $this->meta_box['title'], array($this, 'recipe_show_meta_box'), $this->meta_box['page'], $this->meta_box['context'], $this->meta_box['priority']);
    }

    function recipe_show_meta_box($post) {
        // Use nonce for verification
        echo '<input type="hidden" name="recipe_meta_box_nonce" value="', wp_create_nonce(basename(__FILE__)), '" />';

        echo '<table class="form-table">';

        foreach ($this->meta_box['fields'] as $field) {
            // get current post meta data
            $meta = get_post_meta($post->ID, $field['id'], true);

            echo '<tr>',
                    '<th style="width:20%; line-height:1.3em; padding-top:13px;"><label for="', $field['id'], '" style="font-size:13px;">', $field['name'], '</label></th>',
                    '<td>';
            switch ($field['type']) {
                case 'text':
                    echo '<input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" size="30" style="width:', $field['width'] ? $field['width'] : '97', '%" />', ' ', $field['desc'];
                    break;
                case 'textarea':
                    echo '<textarea name="', $field['id'], '" id="', $field['id'], '" cols="60" rows="20" style="width:97%">', $meta ? $meta : $field['std'], '</textarea>', ' ', $field['desc'];
                    break;
                case 'select':
                    echo '<select name="', $field['id'], '" id="', $field['id'], '">';
                    foreach ($field['options'] as $option) {
                        echo '<option', $meta == $option ? ' selected="selected"' : '', '>', $option, '</option>';
                    }
                    echo '</select>';
                    break;
                case 'radio':
                    foreach ($field['options'] as $option) {
                        echo '<input type="radio" name="', $field['id'], '" value="', $option['value'], '"', $meta == $option['value'] ? ' checked="checked"' : '', ' />', $option['name'];
                    }
                    break;
                case 'checkbox':
                    echo '<input type="checkbox" name="', $field['id'], '" id="', $field['id'], '"', $meta ? ' checked="checked"' : '', ' />';
                    break;
            }
            echo     '<td>',
                '</tr>';
        }

        echo '</table>';

        // reposition the recipe meta info above the editor
        add_action('admin_print_footer_scripts', array($this, 'recipe_reposition_box'), 0);
    }

    function recipe_save_data($post_id) {
        // verify nonce
        if (!wp_verify_nonce($_POST['recipe_meta_box_nonce'], basename(__FILE__))) {
            return $post_id;
        }

        // check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }

        // check permissions
        if ('page' == $_POST['post_type']) {
            if (!current_user_can('edit_page', $post_id)) {
                return $post_id;
            }
        } elseif (!current_user_can('edit_post', $post_id)) {
            return $post_id;
        }

        foreach ($this->meta_box['fields'] as $field) {
            $old = get_post_meta($post_id, $field['id'], true);
            $new = $_POST[$field['id']];

            if ($new && $new != $old) {
                update_post_meta($post_id, $field['id'], $new);
            } elseif ('' == $new && $old) {
                delete_post_meta($post_id, $field['id'], $old);
            }
        }
    }

    function recipe_reposition_box() {
        // reposition the recipe meta info above the editor
        ?><script type="text/javascript">
            jQuery("#recipe-info").insertAfter("#titlediv");
        </script><?php
    }

    function recipe_post_class($c) {
        if (preg_match('/ type-recipe /', join($c, ' '))) {
            // remove hentry if it exists
            foreach ($c as $key => $className) {
                if ($className == 'hentry') {
                    unset($c[$key]);
                }
            }

            // add hrecipe
            $c[] = 'hrecipe';
        }

        return $c;
    }

    function recipe_the_content($c) {
        global $post;
        $content = $c;

        if ($post->post_type == 'recipe') {
            $info = recipe_info($post);
            $content = '';

            if ($info['summary'])
                $content .= '<p>'.$info['summary'].'</p>';

            if ($info['ingredients'])
                $content .= '<h3>Ingredients</h3>'.$info['ingredients'];

            if ($info['instructions'])
                $content .= '<h3>Method</h3>'.$info['instructions'];

            $content .= $c;
        }

        return $content;
    }

    function recipe_template_redirect() {
        $post_type = get_query_var('post_type');
        if ($post_type == 'recipe') {  // check your post type
            if (file_exists(TEMPLATEPATH.'/single-' . $post_type . '.php')) return;
            load_template(dirname( __FILE__ ).'/single-recipe.php');
            exit;
        }
    }

  function bordercolor() {
  }

  function background_color() {

    $options = get_option('hrecipe_options');
    echo "<input id='hrecipe_background_color' name='hrecipe_options[background_color]' size='40' type='text' value='{$options['background_color']}' />";
  }

  function border_color() {

    $options = get_option('hrecipe_options');
    echo "<input id='hrecipe_border_color' name='hrecipe_options[border_color]' size='40' type='text' value='{$options['border_color']}' />";
  }

  function hrecipe_labels_text() {
    echo '
    <p>
    Actions here control how your recipe is labeled.
    </p>';
  }

  function hrecipe_styling_text() {
    echo '
    <p>
    The actions in this section control how your recipe is styled. At the moment, only the global background color can be set as an option. In the future, fonts and font styles will be customizable.
    </p>';
  }

  function hrecipe_structure_text() {
    echo '
    <p>
    The actions in this section control how your recipe is structured.
    </p>';
  }

  /**
   * Place holder for options registration.
   */
   function hrecipe_options_validate($input) {
     return $input;
   }

    function hrecipe_plugin_menu() {

        global $hrecipe_options_file;
        
        if (function_exists('add_options_page')) {

            global $hrecipe_pagehook;
            $hrecipe_pagehook = add_options_page('hRecipe Options', 'hRecipe', 'administrator', $hrecipe_options_file, array($this, 'hrecipe_plugin_options_page'));
            add_action('load-'.$hrecipe_pagehook, array($this,'on_load_page'));
        }
    }


    function on_load_page() {
        wp_enqueue_script('common');
        wp_enqueue_script('wp-lists');
        wp_enqueue_script('postbox');
        wp_enqueue_script('hrecipescript');                
    }



    function hrecipe_plugin_options_page() {

        global $hrecipe_plugin_url;
        global $hrecipe_plugin_url1;

        if ( isset($_GET['sub']) && $_GET['sub'] == 'support' ) {
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

        // TODO: Wrap to load only on post or page editing admin pages.
        wp_register_script('hrecipeformat',plugins_url('hrecipe/js/hrecipe_format.js', dirname(__FILE__)),'','',true);
        wp_enqueue_script('hrecipeformat');
    }

    function add_hrecipe_stylesheet() {

        // TODO: Replace constants with plugins_url()
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


function recipe_format_summary($value) {
    $value = '<span class="summary">'.$value.'</span>';
    return $value;
}

function recipe_format_ingredients($value) {
    $value = preg_replace('/\n/', '</li><li class="ingredient">', $value);
    $value = '<ul><li class="ingredient">'.$value.'</li></ul>';
    return $value;
}

function recipe_format_instructions($value) {
    $value = preg_replace('/\n/', '</li><li>', $value);
    $value = '<ol class="instructions"><li>'.$value.'</li></ol>';
    return $value;
}

function recipe_format_yield($value) {
    $value = '<span class="yield">'.$value.'</span>';
    return $value;
}

function recipe_format_duration($totalminutes) {
    $hours        = floor($totalminutes / 60);
    $minutes      = $totalminutes % 60;

    $value  = '<span class="duration">';
    $value .= '<span class="value-title" title="PT'. $hours .'H'. $minutes .'M">';
    $value .= $hours .' hour(s) '. $minutes .' minutes';
    $value .= '</span></span>';
    return $value;
}

function recipe_format_url($url) {
    $name = preg_replace('/\w*:\/\/(www\.)*([^\/]*).*/', "$2", $url);
    $value  = '<a class="url" href="'.$url.'">'.$name.'</a>';
    return $value;
}

function recipe_format_rating($rating) {
    $value  = '<div class="review hreview-aggregate"><span class="rating">';
    $value .= '  <span class="average">'.$rating.'</span> stars: <span class="stars"> ';

    for ($i=0; $i<$rating; $i++) {
        $value .= '★ ';
    }

    $value .= '  </span><span class="count">1</span> review(s)';
    $value .= '</span></div>';

    return $value;
}

function recipe_info() {
    global $post;

    $info = array();
    foreach (get_post_custom() as $key => $value)  {
        $id = str_replace('recipe_', '', $key);
        if (is_array($value) && count($value) == 1) {
            if ($id == 'ingredients') {
                $info[$id] = recipe_format_ingredients($value[0]);
            } elseif ($id == 'instructions') {
                $info[$id] = recipe_format_instructions($value[0]);
            } elseif ($id == 'summary') {
                $info[$id] = recipe_format_summary($value[0]);
            } elseif ($id == 'yield') {
                $info[$id] = recipe_format_yield($value[0]);
            } elseif ($id == 'url') {
                $info[$id] = recipe_format_url($value[0]);
            } elseif ($id == 'rating') {
                $info[$id] = recipe_format_rating($value[0]);
            } elseif (preg_match('/time$/m', $id)) {
                $info[$id] = recipe_format_duration($value[0]);
            } else {
                $info[$id] = $value[0];
            }
        } else {
            $info[$id] = $value;
        }
    }

    return $info;
}

function recipe_meta() {
}


?>