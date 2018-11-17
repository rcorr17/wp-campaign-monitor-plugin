// JavaScript Document

function _(el) {
	return document.getElementById(el);	
}

function unsubscribeAll() {
	// definitely unsubscribe all
	
	var form = document.getElementById('getUser');
    var inputs = form.elements;
	
	if(!inputs){
        //no inputs found
        return;
    }
	
    if(!inputs.length){
        //only one elements, forcing into an array"
        inputs = new Array(inputs);        
    }

    for (var i = 0; i < inputs.length; i++) {  
      //checking input
      if (inputs[i].type == "checkbox" && inputs[i].id != "chkUnsubscribeAll"  && inputs["chkUnsubscribeAll"].checked == true) {  
		inputs[i].checked = false;
      }  
    }  
}

function checkForUnsubscribeAll() {
	//possibility of unsubscribe all - check if all the other checkboxes are unchecked and if so checked the unsubscribe checkbox
	
	var form = document.getElementById('getUser');
    var inputs = form.elements;
	var bUnsubscribeAll = true;
	
	if(!inputs){
        //no inputs found
        return;
    }
	
    if(!inputs.length){
        //only one elements, forcing into an array"
        inputs = new Array(inputs);        
    }

    for (var i = 0; i < inputs.length; i++) {  
      //check to see if any checkbox has been selected; if so unsubscribe all is false.
      if (inputs[i].type == "checkbox" && inputs[i].checked == true) {  
		bUnsubscribeAll = false;
      }  
    } 

	// if all checkboxes are empty, then check off the unsubscribe all check box
	if (bUnsubscribeAll)
		_("chkUnsubscribeAll").checked = true;
	else
		_("chkUnsubscribeAll").checked = false;
}