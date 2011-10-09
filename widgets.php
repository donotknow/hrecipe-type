<?php

/**
 * Add function to widgets_init that'll load hrecipe widgets.
 */
add_action('widgets_init', 'hrecipe_load_widgets');

/**
 * Register hrecipe widgets.
 */
function hrecipe_load_widgets() {
	register_widget('hRecipe_Meal_Type_Categories');
	register_widget('hRecipe_Diet_Type_Categories');
	register_widget('hRecipe_Culinary_Tradition_Categories');
	register_widget('hRecipe_Major_Ingredients_Tag_Cloud');
}



/**
 * Custom taxonomy category widgets
 */
class hRecipe_Categories extends WP_Widget {

	function __construct($id_base, $name, $widget_ops, $control_ops) {
		$id_base = 'hrecipe_'.$id_base;
		$widget_ops['classname'] .= ' widget_categories';
		$control_ops['id_base'] = 'hrecipe_'.$control_ops['id_base'];
		parent::__construct($id_base, $name, $widget_ops, $control_ops);
	}

	function widget($args, $instance, $taxonomy) {
		extract($args);

		$title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);
		$c = $instance['count'] ? '1' : '0';
		$h = $instance['hierarchical'] ? '1' : '0';
		$d = $instance['dropdown'] ? '1' : '0';

		echo $before_widget;
		if ($title)
			echo $before_title . $title . $after_title;

		$cat_args = array('taxonomy' => $taxonomy, 'orderby' => 'name', 'show_count' => $c, 'hierarchical' => $h);

		if ($d) {
			$cat_args['show_option_none'] = __('Select');
			wp_dropdown_categories(apply_filters('widget_categories_dropdown_args', $cat_args));
?>

<script type='text/javascript'>
/* <![CDATA[ */
	var dropdown = document.getElementById("cat");
	function onCatChange() {
		if (dropdown.options[dropdown.selectedIndex].value > 0) {
			location.href = "<?php echo home_url(); ?>/?cat="+dropdown.options[dropdown.selectedIndex].value;
		}
	}
	dropdown.onchange = onCatChange;
/* ]]> */
</script>

<?php
		} else {
?>
		<ul>
<?php
		$cat_args['title_li'] = '';
		wp_list_categories(apply_filters('widget_categories_args', $cat_args));
?>
		</ul>
<?php
		}

		echo $after_widget;
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['count'] = !empty($new_instance['count']) ? 1 : 0;
		$instance['hierarchical'] = !empty($new_instance['hierarchical']) ? 1 : 0;
		$instance['dropdown'] = !empty($new_instance['dropdown']) ? 1 : 0;

		return $instance;
	}

	function form($instance) {
		$instance = wp_parse_args((array) $instance, array('title' => ''));
		$title = esc_attr($instance['title']);
		$count = isset($instance['count']) ? (bool) $instance['count'] :false;
		$hierarchical = isset($instance['hierarchical']) ? (bool) $instance['hierarchical'] : false;
		$dropdown = isset($instance['dropdown']) ? (bool) $instance['dropdown'] : false;
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('dropdown'); ?>" name="<?php echo $this->get_field_name('dropdown'); ?>"<?php checked($dropdown); ?> />
		<label for="<?php echo $this->get_field_id('dropdown'); ?>"><?php _e('Display as dropdown'); ?></label><br />

		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>"<?php checked($count); ?> />
		<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Show post counts'); ?></label><br />

		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('hierarchical'); ?>" name="<?php echo $this->get_field_name('hierarchical'); ?>"<?php checked($hierarchical); ?> />
		<label for="<?php echo $this->get_field_id('hierarchical'); ?>"><?php _e('Show hierarchy'); ?></label></p>
<?php
	}

}



/**
 * Custom taxonomy tag cloud widgets
 */
class hRecipe_Tag_Cloud extends WP_Widget {

	function __construct($id_base, $name, $widget_ops, $control_ops) {
		$id_base = 'hrecipe_'.$id_base;
		$widget_ops['classname'] .= ' widget_tagcloud';
		$control_ops['id_base'] = 'hrecipe_'.$control_ops['id_base'];
		parent::__construct($id_base, $name, $widget_ops, $control_ops);
	}

	function widget($args, $instance, $taxonomy) {
		extract($args);
		$title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);

		echo $before_widget;
		if ($title)
			echo $before_title . $title . $after_title;

		// use tag cloud for this custom taxonomy
		wp_tag_cloud(array('taxonomy' => $taxonomy));

		echo $after_widget;
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$new_instance = wp_parse_args((array) $new_instance, array('title' => ''));
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	}

	function form($instance) {
		$instance = wp_parse_args((array) $instance, array('title' => ''));
		$title = $instance['title'];
?>
		<p><label>
			<?php _e('Title:'); ?>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
		</label></p>
<?php
	}
}



/**
 * Meal type category widget
 */
class hRecipe_Meal_Type_Categories extends hRecipe_Categories {

	function __construct() {
		$widget_ops = array('classname' => 'meal', 'description' => __("A list or dropdown of meal types"));
		$control_ops = array('id_base' => 'meal');
		parent::__construct('meal', __('Meal Type'), $widget_ops, $control_ops);
	}

	function widget($args, $instance) {
		parent::widget($args, $instance, 'meal');
	}

	function update($new_instance, $old_instance) {
		parent::update($new_instance, $old_instance);
	}

	function form($instance) {
		parent::form($instance);
	}

}



/**
 * Diet type category widget
 */
class hRecipe_Diet_Type_Categories extends hRecipe_Categories {

	function __construct() {
		$widget_ops = array('classname' => 'diet', 'description' => __("A list or dropdown of diet types"));
		$control_ops = array('id_base' => 'diet');
		parent::__construct('diet', __('Diet Type'), $widget_ops, $control_ops);
	}

	function widget($args, $instance) {
		parent::widget($args, $instance, 'diet');
	}

	function update($new_instance, $old_instance) {
		parent::update($new_instance, $old_instance);
	}

	function form($instance) {
		parent::form($instance);
	}

}



/**
 * Culinary Tradition category widget
 */
class hRecipe_Culinary_Tradition_Categories extends hRecipe_Categories {

	function __construct() {
		$widget_ops = array('classname' => 'culinary', 'description' => __("A list or dropdown of culinary traditions"));
		$control_ops = array('id_base' => 'culinary');
		parent::__construct('culinary', __('Culinary Tradition'), $widget_ops, $control_ops);
	}

	function widget($args, $instance) {
		parent::widget($args, $instance, 'culinary');
	}

	function update($new_instance, $old_instance) {
		parent::update($new_instance, $old_instance);
	}

	function form($instance) {
		parent::form($instance);
	}

}



/**
 * Major Ingredients tag cloud widget
 */
class hRecipe_Major_Ingredients_Tag_Cloud extends hRecipe_Tag_Cloud {

	function __construct() {
		$widget_ops = array('classname' => 'ingredients', 'description' => __("Most used major ingredients in cloud format"));
		$control_ops = array('id_base' => 'ingredients');
		parent::__construct('ingredients', __('Major Ingredients'), $widget_ops, $control_ops);
	}

	function widget($args, $instance) {
		parent::widget($args, $instance, 'ingredients');
	}

	function update($new_instance, $old_instance) {
		parent::update($new_instance, $old_instance);
	}

	function form($instance) {
		parent::form($instance);
	}

}



?>