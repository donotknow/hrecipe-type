<?php

include dirname( __FILE__ ).'/plugin_base.php';
include_once dirname( __FILE__ ).'/models/options_db.php';
include_once dirname( __FILE__ ).'/hrecipe.posttype.php';
include_once dirname( __FILE__ ).'/hrecipe.taxonomy.php';
include_once dirname( __FILE__ ).'/hrecipe.widgets.php';

$hrecipe_pagehook = null;
$hrecipe_options_file = 'view/admin/options.php';
  
include('hrecipe_localize_vars.php');

class hrecipe extends PluginBase {

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
      // hrecipe_delete_options();
    }


    function hrecipe_plugin_init() {

        global $hrecipe_plugin_url;

        load_plugin_textdomain('hrecipe', $hrecipe_plugin_url.'/lang', 'hrecipe/lang');
        
        //wp_register_script('hrecipe-reciply', 'http://www.recip.ly/static/js/jquery-reciply.js');
        //wp_enqueue_script('hrecipe-reciply');
        // wp_register_script('hrecipescript',plugins_url('hrecipe/js/hrecipescript.js', dirname(__FILE__)),'','',true);

        //wp_enqueue_script('hrecipescript');

        // add the recipe custom post type
        $hrecipe_posttype = new hRecipe_PostType();
        $hrecipe_posttype->hrecipe_register_post_type();
        
        // add the recipe taxonomy system
        $hrecipe_taxonomy = new hRecipe_Taxonomy();
        $hrecipe_taxonomy->hrecipe_register_taxonomy();

        // add a filter to the post class and the_content to add hrecipe, the microformat is the whole point!
        add_filter('post_class', array($this, 'recipe_post_class'));
        add_filter('the_excerpt', array($this, 'recipe_the_excerpt'));
        add_filter('the_content', array($this, 'recipe_the_content'));

        // have the new recipe custom post type act like a post (not a page)
        add_filter('pre_get_posts', array($hrecipe_posttype, 'hrecipe_get_posts'));

        // create a template file just in case the theme doesn't have one
        add_action('template_redirect', array($this, 'recipe_template_redirect'), 5);
    }

    function register_mysettings() {

        global $hrecipe_options_file;

        register_setting('hrecipe_options_group', 'hrecipe_options');//, array($this,'hrecipe_options_validate')); #next rev
        add_settings_section('hrecipe_labels', '', 'hrecipe_labels_text', $hrecipe_options_file);
        add_settings_section('hrecipe_structure', '', 'hrecipe_structure_text', $hrecipe_options_file);        

        add_settings_section('hrecipe_styling', '', array($this,'hrecipe_styling_text'), $hrecipe_options_file);
        add_settings_field('hrecipe_custom_style', __('Custom CSS class', 'hrecipe'), array($this,'custom_style'), $hrecipe_options_file, 'hrecipe_styling');

        // Testing...!!!!
        //add_settings_field('hrecipe_custom_style', $this->custom_style_label(), array($this,'custom_style'), $hrecipe_options_file, 'hrecipe_styling');        

        //add_settings_field('hrecipe_border_color', __('Border color', 'hrecipe'), array($this,'border_color'), $hrecipe_options_file, 'hrecipe_styling');        
        //add_settings_field('hrecipe_background_color', 'Background color', array($this,'background_color'), $hrecipe_options_file, 'hrecipe_styling');        
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

    function recipe_the_excerpt($c) {
        global $post;
        $content = $c;

        if ($post->post_type == 'recipe') {
            $info = recipe_info($post);
            $content = '';

            if ($info['summary'])
                $content .= '<p>'.$info['summary'].'</p>';
        }

        return $content;
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
                $content .= '<div class="ingredients"><h3>Ingredients</h3>'.$info['ingredients'].'</div>';

            if ($info['instructions'])
                $content .= '<div class="method"><h3>Method</h3>'.$info['instructions'].'</div>';

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

  // Check carefully... if this works, all these functions should be 
  // loaded from a file.
  function custom_style_label() {
    return __('Custom CSS class, from a custom function call...', 'hrecipe');
  }

  function custom_style() {
    
    $options = get_option('hrecipe_options');
    echo "Add the styling for your custom class in your theme's style.css, or create your own recipe styling plugin.";  
    echo " Your custom css class will be automatically added to the recipe output.<br />";
    echo "<input id='hrecipe_custom_style' name='hrecipe_options[custom_style]' size='40' type='text' value='{$options['custom_style']}' />";
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
            // For plugins, admin_menu fires before admin_init, so we set the 
            // page hook variable for our spiffy UJS. See the Codex:
            // http://codex.wordpress.org/Plugin_API/Action_Reference
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
    if ($hours > 0) {
        $value .= $hours .' hour(s) ';
    }
    if ($minutes > 0) {
        $value .= $minutes .' minutes';
    }
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