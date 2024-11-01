<form name="filter" action="" method="get">
<input type="hidden" name="page" value="cool_polls" />
<input type="hidden" name="search" value="p_search" />
<table width="100%" border="0">
  <tr>
    <td><input type="text" name="p_ques" value="<?php echo sanitize_text_field(@$_REQUEST['p_ques']);?>" placeholder="<?php _e('Poll','cool-poll');?>"/> <input type="submit" name="submit" value="<?php _e('Filter','cool-poll');?>" /></td>
  </tr>
</table>
</form>