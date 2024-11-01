<?php
function cpoll_get_poll_status_selected($sel_id = '')
{
      switch($sel_id) {
      	case "Inactive":
      	case "":
      	$statuses = array('Active', 'Inactive');
      	break;
      	case "Active":
      	$statuses = array('Active', 'Stopped');
      	break;
      	case "Stopped":
      	$statuses = array('Stopped');
      	break;
         case "Deleted":
      	$statuses = array('Deleted');
      	break;

      }	
		
		$ret = '';
		foreach ( $statuses as $status ) {
			if($sel_id == $status){
				$ret .= '<option value="'.$status.'" selected="selected">'.$status.'</option>';
			} else {
				$ret .= '<option value="'.$status.'">'.$status.'</option>';
			}
		}
		return $ret;
}

?>