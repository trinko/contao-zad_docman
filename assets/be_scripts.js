/**
 * Display or hide the list field.
 * @param {string} id      The ID of the target element
 * @param {int}    num     The row number
 */
function zad_docman_infofields(id,num) {
  visibility = (id.value=='choice' || id.value=='mchoice') ? 'visible' : 'hidden';
  id2 = document.getElementById('ctrl_infoFields_'+num+'_list');
  id3 = document.getElementById('lbl_infoFields_'+num+'_list');
  id2.style.visibility = visibility;
  id3.style.visibility = visibility;
}

