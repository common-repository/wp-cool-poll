
function cpChangeStatus(){
	jQuery.ajax({
	type: "POST",
	data: { action: "changeStatus", poll_nonce_field: jQuery('#poll_nonce_field').val(), cp_status: jQuery('#cp_status').val(), cp_id: jQuery('#cp_id').val() }
	})
	.done(function( res ) {
		alert(res);
	});
}

function cpConfirmRemove(){
	var con = confirm( 'Are you sure to remove this?');
	if( con ){
		return true;
	} else {
		return false;
	}
}