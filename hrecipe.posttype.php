<?php

class hRecipe_PostType {

	public function hrecipe_register_post_type() {
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
			'register_meta_box_cb' => array($this, 'hrecipe_add_meta_box')
		));

		// add the save post for the new meta boxes we just created
		add_action('save_post', array($this, 'hrecipe_save_data'));
	}

	public function hrecipe_add_meta_box() {
		add_meta_box($this->meta_box['id'], $this->meta_box['title'], array($this, 'hrecipe_show_meta_box'), $this->meta_box['page'], $this->meta_box['context'], $this->meta_box['priority']);
	}

	public function hrecipe_show_meta_box($post) {
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
			echo	 '<td>',
				'</tr>';
		}

		echo '</table>';

		// reposition the recipe meta info above the editor
		add_action('admin_print_footer_scripts', array($this, 'hrecipe_reposition_box'), 0);
	}

	public function hrecipe_reposition_box() {
		// reposition the recipe meta info above the editor
		?><script type="text/javascript">
			jQuery("#recipe-info").insertAfter("#titlediv");
		</script><?php
	}

	public function hrecipe_save_data($post_id) {
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

	public function hrecipe_get_posts($query) {
		if (!is_admin() && (is_archive() || is_author() || is_category() || is_date() || is_feed() || is_home() || is_search() || is_tag() || is_tax()))
			$query->set( 'post_type', array( 'post', 'recipe' ) );

		return $query;
	}

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

}

?>
