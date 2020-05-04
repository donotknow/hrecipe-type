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

	public function __construct($id_base, $name, $widget_ops, $control_ops) {
		$id_base                  = 'hrecipe_' . $id_base;
		$widget_ops['classname'] .= ' widget_categories';
		$control_ops['id_base']   = 'hrecipe_'.$control_ops['id_base'];
		parent::__construct($id_base, $name, $widget_ops, $control_ops);
	}

	public function widget( $args, $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Categories', 'custom-post-type-widgets' );

		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$taxonomy = ! empty( $instance['taxonomy'] ) ? $instance['taxonomy'] : 'category';
		$c        = ! empty( $instance['count'] ) ? (bool) $instance['count'] : false;
		$h        = ! empty( $instance['hierarchical'] ) ? (bool) $instance['hierarchical'] : false;
		$d        = ! empty( $instance['dropdown'] ) ? (bool) $instance['dropdown'] : false;

		echo $args['before_widget'];
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		$cat_args = array(
			'orderby'      => 'name',
			'taxonomy'     => $taxonomy,
			'show_count'   => $c,
			'hierarchical' => $h,
		);

		if ( $d ) {
			$dropdown_id = "{$this->id_base}-dropdown-{$this->number}";

			echo '<label class="screen-reader-text" for="' . esc_attr( $dropdown_id ) . '">' . $title . '</label>';

			$cat_args['show_option_none'] = __( 'Select Category', 'custom-post-type-widgets' );
			$cat_args['name']             = 'category' === $taxonomy ? 'category_name' : $taxonomy;
			$cat_args['id']               = $dropdown_id;
			$cat_args['value_field']      = 'slug';
?>

<form action="<?php echo esc_url( home_url() ); ?>" method="get">
			<?php
			wp_dropdown_categories(
				apply_filters(
					'custom_post_type_widgets/categories/widget_categories_dropdown_args',
					$cat_args,
					$instance,
					$this->id,
					$taxonomy
				)
			);
			?>
</form>
<script>
/* <![CDATA[ */
(function() {
	var dropdown = document.getElementById( "<?php echo esc_js( $dropdown_id ); ?>" );
	function onCatChange() {
		if ( dropdown.options[dropdown.selectedIndex].value ) {
			return dropdown.form.submit();
		}
	}
	dropdown.onchange = onCatChange;
})();
/* ]]> */
</script>
<?php
		}
		else {
?>
		<ul>
<?php
			$cat_args['title_li'] = '';
			wp_list_categories(
				apply_filters(
					'custom_post_type_widgets/categories/widget_categories_args',
					$cat_args,
					$instance,
					$this->id,
					$taxonomy
				)
			);
?>
		</ul>
<?php
		}

		echo $args['after_widget'];
	}

	public function update( $new_instance, $old_instance ) {
		$instance                 = $old_instance;
		$instance['title']        = sanitize_text_field( $new_instance['title'] );
		$instance['taxonomy']     = stripslashes( $new_instance['taxonomy'] );
		$instance['count']        = ! empty( $new_instance['count'] ) ? (bool) $new_instance['count'] : false;
		$instance['hierarchical'] = ! empty( $new_instance['hierarchical'] ) ? (bool) $new_instance['hierarchical'] : false;
		$instance['dropdown']     = ! empty( $new_instance['dropdown'] ) ? (bool) $new_instance['dropdown'] : false;

		return $instance;
	}

	public function form( $instance ) {
		$title        = isset( $instance['title'] ) ? $instance['title'] : '';
		$taxonomy     = isset( $instance['taxonomy'] ) ? $instance['taxonomy'] : '';
		$count        = isset( $instance['count'] ) ? (bool) $instance['count'] : false;
		$hierarchical = isset( $instance['hierarchical'] ) ? (bool) $instance['hierarchical'] : false;
		$dropdown     = isset( $instance['dropdown'] ) ? (bool) $instance['dropdown'] : false;
?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:', 'custom-post-type-widgets' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>

		<?php
		$taxonomies = get_taxonomies( '', 'objects' );

		if ( $taxonomies ) {
			printf(
				'<p><label for="%1$s">%2$s</label>' .
				'<select class="widefat" id="%1$s" name="%3$s">',
				$this->get_field_id( 'taxonomy' ),
				__( 'Taxonomy:', 'custom-post-type-widgets' ),
				$this->get_field_name( 'taxonomy' )
			);

			foreach ( $taxonomies as $taxobjects => $value ) {
				if ( ! $value->hierarchical ) {
					continue;
				}
				if ( 'nav_menu' === $taxobjects || 'link_category' === $taxobjects || 'post_format' === $taxobjects ) {
					continue;
				}

				printf(
					'<option value="%s"%s>%s</option>',
					esc_attr( $taxobjects ),
					selected( $taxobjects, $taxonomy, false ),
					__( $value->label, 'custom-post-type-widgets' ) . ' ' . $taxobjects
				);
			}
			echo '</select></p>';
		}
		?>

		<p><input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'dropdown' ); ?>" name="<?php echo $this->get_field_name( 'dropdown' ); ?>"<?php checked( $dropdown ); ?> />
		<label for="<?php echo $this->get_field_id( 'dropdown' ); ?>"><?php esc_html_e( 'Display as dropdown', 'custom-post-type-widgets' ); ?></label><br />

		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>"<?php checked( $count ); ?> />
		<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php esc_html_e( 'Show post counts', 'custom-post-type-widgets' ); ?></label><br />

		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'hierarchical' ); ?>" name="<?php echo $this->get_field_name( 'hierarchical' ); ?>"<?php checked( $hierarchical ); ?> />
		<label for="<?php echo $this->get_field_id( 'hierarchical' ); ?>"><?php esc_html_e( 'Show hierarchy', 'custom-post-type-widgets' ); ?></label></p>
<?php
	}
}



/**
 * Custom taxonomy tag widgets (tag cloud)
 */
class hRecipe_Tag_Cloud extends WP_Widget {
	public function __construct($id_base, $name, $widget_ops, $control_ops) {
		$id_base = 'hrecipe_'.$id_base;
		$widget_ops['classname'] .= ' widget_tagcloud';
		$control_ops['id_base'] = 'hrecipe_'.$control_ops['id_base'];
		parent::__construct($id_base, $name, $widget_ops, $control_ops);
	}

	public function widget( $args, $instance ) {
		$taxonomy   = $this->get_taxonomy( $instance );
		$show_count = ! empty( $instance['count'] );

		if ( ! empty( $instance['title'] ) ) {
			$title = $instance['title'];
		} else {
			if ( 'post_tag' === $taxonomy ) {
				$title = __( 'Tags', 'custom-post-type-widgets' );
			} else {
				$tax   = get_taxonomy( $taxonomy );
				$title = $tax->labels->name;
			}
		}

		$tag_cloud = wp_tag_cloud(
			apply_filters(
				'custom_post_type_widgets/tag_cloud/widget_tag_cloud_args',
				array(
					'taxonomy'   => $taxonomy,
					'echo'       => false,
					'show_count' => $show_count,
				),
				$instance,
				$this->id,
				$taxonomy
			)
		);

		if ( empty( $tag_cloud ) ) {
			return;
		}

		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		echo $args['before_widget'];
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		echo '<div class="tagcloud">';
		echo $tag_cloud;
		echo '</div>';
		echo $args['after_widget'];
	}

	public function update( $new_instance, $old_instance ) {
		$instance             = $old_instance;
		$instance['title']    = sanitize_text_field( $new_instance['title'] );
		$instance['taxonomy'] = stripslashes( $new_instance['taxonomy'] );
		$instance['count']    = ! empty( $new_instance['count'] ) ? (bool) $new_instance['count'] : false;

		return $instance;
	}

	public function form( $instance ) {
		$title    = isset( $instance['title'] ) ? $instance['title'] : '';
		$taxonomy = isset( $instance['taxonomy'] ) ? $instance['taxonomy'] : 'post_tag';
		$count    = isset( $instance['count'] ) ? (bool) $instance['count'] : false;
?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:', 'custom-post-type-widgets' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>

		<?php
		$taxonomies = get_taxonomies( array( 'show_tagcloud' => true ), 'objects' );
		if ( $taxonomies ) {
			printf(
				'<p><label for="%1$s">%2$s</label>' .
				'<select class="widefat" id="%1$s" name="%3$s">',
				$this->get_field_id( 'taxonomy' ),
				__( 'Taxonomy:', 'custom-post-type-widgets' ),
				$this->get_field_name( 'taxonomy' )
			);

			foreach ( $taxonomies as $taxobjects => $value ) {
				if ( ! $value->show_tagcloud || empty( $value->labels->name ) ) {
					continue;
				}
				if ( $value->hierarchical ) {
					continue;
				}
				if ( 'nav_menu' === $taxobjects || 'link_category' === $taxobjects || 'post_format' === $taxobjects ) {
					continue;
				}

				printf(
					'<option value="%s"%s>%s</option>',
					esc_attr( $taxobjects ),
					selected( $taxobjects, $taxonomy, false ),
					__( $value->label, 'custom-post-type-widgets' ) . ' ' . $taxobjects
				);
			}
			echo '</select></p>';
?>
			<p><input class="checkbox" type="checkbox" <?php checked( $count ); ?> id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php esc_html_e( 'Show tag counts', 'custom-post-type-widgets' ); ?></label></p>
		<?php
		}
		else {
			echo '<p>' . __( 'The tag cloud will not be displayed since there are no taxonomies that support the tag cloud widget.', 'custom-post-type-widgets' ) . '</p>';
		}
	}

	public function get_taxonomy( $instance ) {
		if ( ! empty( $instance['taxonomy'] ) && taxonomy_exists( $instance['taxonomy'] ) ) {
			return $instance['taxonomy'];
		}

		return 'post_tag';
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

}


