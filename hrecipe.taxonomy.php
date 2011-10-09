<?php

class hRecipe_Taxonomy {

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

}

?>
