/* 
 * Javascript to select all the checkboxes in one shot
 */

function markAllRows(container_id) {

	var rows = document.getElementById(container_id).getElementsByTagName('tr');
	if (rows) {
		var roger = 0;
		var result = false;

		for ( var i = 0; i < rows.length; i++) {

			checkbox = rows[i].getElementsByTagName('input')[0];

			if (checkbox && checkbox.type == 'checkbox') {

				roger++;

				if (roger == 1 && checkbox.checked == true) {
					result = true;
				}

				checkbox.checked = result;

			}
		}

		return true;
	} else {
		return false;
	}
}
function selectOption(selectObjectId, searchValue) {
	var selectObject = document.getElementById(selectObjectId);
	if (!selectObject) {
		alert("No select object with id" + selectObjectId);
	}
	var found = false;
	for (i = 0; i < selectObject.options.length; i++) {
		// alert(selectObject.options[i]+" examined");
		if (selectObject.options[i].value == searchValue) {
			// alert(selectObject.options[i].value+" found");
			selectObject.options[i].selected = true;
			found = true;
		}

	}
	if (!found) {
		alert("Failed to find " + searchValue)
	}
	// alert(selectObject.options.value);
	// selectObject.options.value=searchValue;
	// alert(selectObject.options.value);
}
var invalidElectors = new Array();
/** add invalid electors if they are not already in the array */
function addInvalidElector(idDge) {
	var index = invalidElectors.indexOf(idDge);
	if(index == -1) {
		invalidElectors.push(idDge);
	}
}
function removeInvalidElector(idDge) {
	var index = invalidElectors.indexOf(idDge);
	if(index != -1) {
		invalidElectors.splice(index);
		//alert ('deleted');
	}
}

function submitCheck() {
	if(invalidElectors.length>0) {
	alert("Désolé, "+invalidElectors.length+" électeur(s) avec des données de pointage incomplètes");
	return false;
	}
	else {
		return true;
	}
}

function processSelectBoxChange(idDge, selectId) {
	var voteSelectId = "VOTE[" + idDge + "]";
	var allegeanceSelectId = "ALLEGEANCE[" + idDge + "]";
	var contactSelectId = "CONTACT[" + idDge + "]";
	var contactSelectObj = document.getElementById(contactSelectId);
	var resultatSelectId = "RESULTAT[" + idDge + "]";
	var resultatSelectObj = document.getElementById(resultatSelectId);
	var saveCheckboxId = "elector_id_" + idDge;
	
	document.getElementById('saveWarning').style.display = 'block';
	document.getElementById(saveCheckboxId).checked = true;
	// alert(selectId);
	switch (selectId) {
	case (voteSelectId):
		break;
	case (allegeanceSelectId):
		// alert("vote!");
		if (document.getElementById(allegeanceSelectId).value != 'bof') {
			selectOption(resultatSelectId, 'success');
		}
		break;
	case (resultatSelectId):
		// alert("vote!");
		if (document.getElementById(resultatSelectId).value != 'bof') {
			selectOption(allegeanceSelectId, 'bof');
		}
		break;
	default:
		;
	}

   	if (resultatSelectObj.value != 'bof' && contactSelectObj.value != 'bof') {
		resultatSelectObj.parentNode.parentNode.style.backgroundColor='green';//The tr
		removeInvalidElector(idDge);
	}
	else {
		resultatSelectObj.parentNode.parentNode.style.backgroundColor='yellow';//The tr
		addInvalidElector(idDge);
	}
	
	if (resultatSelectObj.value == 'bof') {
		resultatSelectObj.parentNode.style.backgroundColor='red';//The td
	}
	else {
		resultatSelectObj.parentNode.style.backgroundColor='green';
	}
	
	if (contactSelectObj.value == 'bof') {
		contactSelectObj.parentNode.style.backgroundColor='red';
	}
	else {
		contactSelectObj.parentNode.style.backgroundColor='green';
	}
	

	
}

function changeStyle(el) {
	var css_file = null;
	var element_name = 'custom_style';

	if (el.value == "add" || el.value == "add_history" || el.value == "view") {
		if (el.value == "add" || el.value == "add_history") {
			document.getElementById('change_view_show_rslt').value = 1;
			document.getElementById('change_view_show_contact').value = 1;
			document.getElementById('change_view_show_allg').value = 1;
			document.getElementById('change_view_show_vote').value = 1;
			document.getElementById('change_view_show_date').value = 1;
		}
        el.form.submit();
	} else if (el.value == "liste_telephone") {
		document.getElementById('change_view_show_age').value = 1;
		document.getElementById('change_view_show_co_sexe').value = 0;
		document.getElementById('change_view_show_address').value = 0;
		document.getElementById('change_view_show_city').value = 1;
		document.getElementById('change_view_show_telephone1').value = 1;
		document.getElementById('change_view_show_telephone_m').value = 1;
		document.getElementById('change_view_show_category').value = 0;
		document.getElementById('change_view_show_rslt').value = 1;
		document.getElementById('change_view_show_contact').value = 0;
		document.getElementById('change_view_show_allg').value = 1;
		document.getElementById('change_view_show_vote').value = 0;
		document.getElementById('change_view_show_date').value = 0;
		document.getElementById('change_view_show_note').value = 0;
		document.getElementById('change_view_requested_css').value = "css/print_telephone.css";
		el.form.submit();
	} else if (el.value == "liste_domicile") {
		document.getElementById('change_view_show_age').value = 1;
		document.getElementById('change_view_show_co_sexe').value = 0;
		document.getElementById('change_view_show_address').value = 1;
		document.getElementById('change_view_show_city').value = 0;
		document.getElementById('change_view_show_telephone1').value = 0;
		document.getElementById('change_view_show_telephone_m').value = 0;
		document.getElementById('change_view_show_category').value = 0;
		document.getElementById('change_view_show_rslt').value = 1;
		document.getElementById('change_view_show_contact').value = 0;
		document.getElementById('change_view_show_allg').value = 0;
		document.getElementById('change_view_show_vote').value = 0;
		document.getElementById('change_view_show_date').value = 0;
		document.getElementById('change_view_show_note').value = 0;
		document.getElementById('change_view_requested_css').value = "css/print_porte.css";
		el.form.submit();
	}
	/*
	 * var head = document.getElementsByTagName('head').item(0); var link =
	 * document.getElementById(element_name); if (link !== null) {
	 * head.removeChild(link); }
	 * 
	 * var newLink = document.createElement('link');
	 * newLink.setAttribute('href', css_file); newLink.setAttribute('rel',
	 * 'stylesheet'); newLink.setAttribute('type', 'text/css');
	 * newLink.setAttribute('id', element_name); head.appendChild(newLink);
	 */
}
