
<?php
    global $hrecipe_plugin_url;
?>      

<script type="text/javascript">//<![CDATA[

  var hrecipe_from_gui;

  function edInsertHRecipe() {      
    tb_show("Add an hRecipe", "<?php echo $hrecipe_plugin_url; ?>/view/lightbox.php?TB_iframe=true");
    hrecipe_from_gui = true; /** Called from TinyMCE **/
  } // End edInsertHRecipe()


  function edInsertHRecipeCode() {
    tb_show("Add an hRecipe", "<?php echo $hrecipe_plugin_url; ?>/view/lightbox.php?TB_iframe=true");
    hrecipe_from_gui = false; /** Called from Quicktags **/
  } // End edInsertHRecipe()

  if (hrecipe_qttoolbar = document.getElementById("ed_toolbar")){
    newbutton = document.createElement("input");
    newbutton.type = "button";
    newbutton.id = "ed_hrecipe";
    newbutton.className = "ed_button";
    newbutton.value = "hRecipe";
    newbutton.onclick = edInsertHRecipeCode;
    hrecipe_qttoolbar.appendChild(newbutton);
  }

  function edInsertHRecipeAbort() {
    tb_remove();
  } // End edInsertHRecipeAbort()


  function edInsertHRecipeStars(itemRating) {
    var markup = '';
    if ( itemRating ) {
      var i, stars, itemRatingValue = parseFloat(itemRating);
      markup = '<p class="myrating"><?php echo get_option('hrecipe_rating_text');?>' +
        '<span class="rating">' + itemRating + '</span> <?php echo get_option('hrecipe_stars_text');?><br />';
      stars = 0;
      for ( i = 1; i <= itemRatingValue; i++ ) {
        stars++;
        markup = markup + '<img class="hrecipe_image" width="20" height="20" src="<?php echo $hrecipe_plugin_url;
?>/starfull.gif" alt="*" />';
      } // End for
      i = parseInt(itemRatingValue);
      if ( itemRatingValue - i > 0.1 ) {
        stars++;
        markup = markup + '<img class="hrecipe_image" width="20" height="20" src="<?php echo $hrecipe_plugin_url;
?>/starhalf.gif" alt="1/2" />';
      } // End if
      for ( i = stars; i < 5; i++ ) {
        markup = markup + '<img class="hrecipe_image" width="20" height="20" src="<?php echo $hrecipe_plugin_url;
?>/starempty.gif" alt="" />';
      } // End for
      markup = markup + '</p>';
    } // End if
    return markup;
  } // End edInsertHRecipeStars()



  function edInsertHRecipeStarChars(itemRating) {

    var markup = '';

    if ( itemRating ) {        
      var i;
      var itemRatingValue = parseInt(itemRating);
      
      markup = '<p class="myrating"><?php echo get_option('hrecipe_rating_text');?>' +
        '<span class="rating">' + itemRating + '</span> <?php echo get_option('hrecipe_stars_text');?>:&nbsp;';
        
      var stars = 0;
      for ( i = 1; i <= itemRatingValue; i++ ) {
        stars++;
        markup = markup + '&#9733;'; // solid_star
      }
     
      for ( i = stars; i < 5; i++ ) {
        markup = markup + '&#9734;'; // outline_star
      }

      markup = markup + '</p>';
    } // End if

    return markup;
    
  } // End edInsertHRecipeStarsChars()




  /*
  <p class="review hreview-aggregate">
  <span class="rating">
     <span class="average">4.0</span> stars based on
     <span class="count">35</span> reviews
  </span> 
 </p>
  */

  
  function google_compliant_rating(itemRating) {

	    var markup = '';
	    
	    if ( itemRating ) {        
	      var i;
	      var itemRatingValue = parseInt(itemRating);
	      
	      markup =  '<p class="review hreview-aggregate">';
          markup += '<?php echo get_option('hrecipe_rating_text');?> ';
          
	      markup += '<span class="rating">';
	      markup += '<span class="average">' + itemRating + ' </span> ';
	      markup += '<?php echo get_option('hrecipe_stars_text'); ?>:&nbsp; ';
	      
	      // These are the loops the print the star symbols.  
	      var stars = 0;
	      for ( i = 1; i <= itemRatingValue; i++ ) {
	        stars++;
	        markup += '&#9733;'; // solid_star
	      }
	     
	      for ( i = stars; i < 5; i++ ) {
	        markup += '&#9734;'; // outline_star
	      }
	      
          markup += '<span class="count"> 1</span> review(s)';
	      markup += '</span>';  
	      markup += '</p>';

	  	} // End if

	    return markup;
	    
	  } 



  function format_ingredients(itemIngredients) {
    //listtype
    lt =  '<?php if (get_option('hrecipe_ingredientlist') == 'bullets') echo "ul"; else echo "ol";?>';
    var imarkup = '';
    var lines = '';
    lines = itemIngredients.split("\*");
    imarkup = '<div class="ingredients">';
    //imarkup += '<h4>Ingredients</h4>';
    imarkup += '<h4><?php echo get_option('hrecipe_ingredients_text');?></h4>';
    imarkup += '<' + lt + ' class="ingredients">';
    for(var i=0; i<lines.length; i++) {
      if (lines[i] == '') continue;
      imarkup += '<li class="ingredient">' + lines[i] + '</li>';
    }
    imarkup += '</' + lt + '>';
    imarkup += '</div>';
    return imarkup;
  }


  function format_instructions(itemDescription) {
    var imarkup = '';
    var lines = '';
    lines = itemDescription.split("\*");
    imarkup = '<div class="instructions">';
    // Get the option for this.
    imarkup += '<h4><?php echo get_option('hrecipe_instructions_text');?></h4>';
    //imarkup += '<h4>Instructions</h4>';
    imarkup += '<ol class="instructions">';
    for(var i=0; i<lines.length; i++) {
      if (lines[i] == '') continue;
      imarkup += '<li>' + lines[i] + '</li>';
    }
    imarkup += '</ol>';
    imarkup += '</div>';
    return imarkup;
  }

  function format_quicknotes(itemQuicknotes) {
    var imarkup = '';
    var lines = '';
    //lines = itemQuicknotes.split("\*");
    imarkup = '<div class="quicknotes">';
    imarkup += '<h4><?php echo get_option('hrecipe_quicknotes_text');?></h4>';
    //imarkup += '<h4>Quick Notes</h4>';
    imarkup += '<p class="quicknotes">';
    imarkup += itemQuicknotes;
    imarkup += '</p>';
    imarkup += '</div>';
    return imarkup;
  }


  function format_variations(itemVariations) {
    var imarkup = '';
    var lines = '';
    //lines = itemVariations.split("\*");
    imarkup = '<div class="variations">';
    imarkup += '<h4><?php echo get_option('hrecipe_variations_text');?></h4>';
    //imarkup += '<h4>Variations</h4>';
    imarkup += '<p class="variations">';
    imarkup += itemVariations;
    imarkup += '</p>';
    imarkup += '</div>';
    return imarkup;
  }



  function format_summary(itemSummary) {
    var markup = '';
    if (itemSummary == '') return;
    markup = '<p class="summary">';
    markup += '<strong><?php echo get_option('hrecipe_summary_text');?></strong>: ';
    //markup += '<strong>Summary: </strong>';
    markup += '<em>' + itemSummary + '</em>';    
    markup += '</p>';
    return markup;
  }

/**
 * Thanks for Michael Allen Smith for help 
 * with bringing duration into spec so that 
 * hrecipes will be properly displayed as 
 * Google Rich Snippets.  Please visit Michael:
 * http://criticalmas.com/
 */
 // Example from http://microformats.org/wiki/hrecipe#duration
 //<span class="duration"><span class="value-title" title="PT1H30M"> </span>90 min</span>
 // TODO: Add an hrlabel span for formatting
  function format_duration(totalminutes) {

	    //Convert the minutes into hours and minutes
	    var hours = Math.floor(totalminutes/60);
	    var minutes = totalminutes%60;
	  
		var markup = '';
		markup = '<p class="duration">Cooking time (duration): ';
		markup += '<span class="value-title" title="PT' + hours + 'H' + minutes + 'M"></span>';    
	    markup += totalminutes + '</p>';
		return markup;
	}
	  
  
function format_item(hrclass, hrtext, hritem) {
	var markup = '';
	markup = '<p class="' + hrclass + '">';
	markup += '<span class="hrlabel">' + hrtext + ': </span>';
	markup += '<span class="hritem">' + hritem + '</span>';    
    markup += '</p>';
	return markup;
}


  function format_enclosure(itemName, itemURL) {

    var et =  '<?php if (get_option('hrecipe_enclosure') == 'div') echo "div"; else echo "fieldset";?>';
	var markup = '';
	
	if ("div" == et) {
		markup += '<div class="hrecipe">';
        markup += '<h2 class="fn"><?php echo get_option('hrecipe_recipe_text');?>: ';
		//markup += '<h2 class="fn">Recipe: ';
		markup += (itemURL ? '<a class="url" href="' + itemURL + '">' : '') + itemName + (itemURL ? '</a>' : '');
		markup += '</h2>';
	} else {
		markup += '<fieldset class="hrecipe">';
		markup += '<legend class="fn"><?php echo get_option('hrecipe_recipe_text');?>: ';
		//markup += '<legend class="fn">Recipe: ';
		markup += (itemURL ? '<a class="url" href="' + itemURL + '">' : '') + itemName + (itemURL ? '</a>' : '');
		markup += '</legend>';
	}	
	
    return markup;
  }

  function linklove() {  	
	return 'Microformatting by <a href="http://website-in-a-weekend.net/hrecipe/" target="_blank">hRecipe</a>.<br />';
  }

  function reciply() {
    return '<div class="reciply-addtobasket-widget" href="' + url + '"></div>';
  }
  
// Add Restrictions, Yield, Author, and Published (date) into first parameter, 
// an object which gets passed in this function.
  function edInsertHRecipeDone(r) {
  	
    tb_remove();
	
	var HRecipeOutput = '';	
    HRecipeOutput += format_enclosure(r["name"], r["url"]);

    HRecipeOutput += ( r["summary"]     ? format_summary(r["summary"]) : '' );
    HRecipeOutput += ( r["ingredients"] ? format_ingredients(r["ingredients"]) : '' );
    HRecipeOutput += ( r["description"] ? format_instructions(r["description"]) : '' );
    HRecipeOutput += ( r["quicknotes"]  ? format_quicknotes(r["quicknotes"]) : '' );
    HRecipeOutput += ( r["variations"]  ? format_variations(r["variations"]) : '' );
    HRecipeOutput += ( r["duration"]    ? format_duration(r["duration"]) : '' );
    //HRecipeOutput += ( r["duration"]    ? format_item('duration', 'Cooking time (duration)', r["duration"]) : '' );
    HRecipeOutput += ( r["diettype"]    ? format_item('diettype', 'Diet type', r["diettype"]) : '' );
    HRecipeOutput += ( r["dietother"]   ? format_item('dietother', 'Diet (other)', r["dietother"]) : '' );
	HRecipeOutput += ( r["restriction"] ? format_item('restriction', 'Dietary restriction', r["restriction"]) : '' );
	HRecipeOutput += ( r["servings"]    ? format_item('yield', 'Number of servings (yield)', r["servings"]) : '' );
    HRecipeOutput += ( r["mealtype"]    ? format_item('mealtype', 'Meal type', r["mealtype"]) : '');
    HRecipeOutput += ( r["tradition"]   ? format_item('tradition', 'Culinary tradition', r["tradition"]) : '');
	//HRecipeOutput += ( r["rating"]      ? edInsertHRecipeStarChars(r["rating"]) : '' );
	HRecipeOutput += ( r["rating"]      ? google_compliant_rating(r["rating"]) : '' );


    var want_copyright = '<?php echo get_option('hrecipe_copyright'); ?>'
    //alert (want_copyright);
	if (want_copyright != ''){
		HRecipeOutput += 'Copyright &copy; ' + want_copyright + '.<br />'
	}

	///*
	var want_byline = '<?php echo get_option('hrecipe_byline'); ?>'
    if (want_byline != '') {
		HRecipeOutput += 'Recipe by ' + want_byline + '.<br />'
	}
    //*/

    var want_linklove = '<?php if (get_option('hrecipe_linklove') == 'on') echo 'true'; else echo ''; ?>'
    //alert (want_linklove);
    if (want_linklove) {
	   HRecipeOutput += linklove();
    }
	
    var want_reciply = '<?php if (get_option('hrecipe_reciply') == 'on') echo 'true'; else echo ''; ?>'
    //alert (want_reciply);
    if (want_reciply) {
        // This is supposed to result from a function call 
        // to reciply(), which for some reason is crashing the 
        // entire hrecipe output.  Stupid.    
	   HRecipeOutput += '<div class="reciply-addtobasket-widget" href="' 
		             + '<?php echo get_permalink(); ?>' 
		             + '"></div>';
    }
	
    var et =  '<?php if (get_option('hrecipe_enclosure') == 'div') echo "div"; else echo "fieldset";?>';
    HRecipeOutput += '</' + et + '>';
    //HRecipeOutput += '</div>';

    if (hrecipe_from_gui)
      {
	tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, HRecipeOutput);
	tinyMCE.execCommand('mceCleanup');
      } else
      {
	edInsertContent(edCanvas, HRecipeOutput);
      }
  } // End edInsertHRecipeDone()

//]]></script>
