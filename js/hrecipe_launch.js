


  var hrecipe_from_gui;

  // TODO: rename these next two functions appropriately.
  function edInsertHRecipe() {
    tb_show("Add an hRecipe", 'media-upload.php?type=hrecipe&tab=hrecipe&amp;TB_iframe=true');
  }


  function edInsertHRecipeCode() {
    tb_show("Add an hRecipe", 'media-upload.php?type=hrecipe&tab=hrecipe&amp;TB_iframe=true');
    hrecipe_from_gui = false; // Called from Quicktags
  }

  hrecipe_qttoolbar = document.getElementById("ed_toolbar");


  if (hrecipe_qttoolbar !== null) {
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
  } 
