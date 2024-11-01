<?php include_once("functions.php");?>


<form name="f" action="" method="post">
<?php echo '<input type="hidden" name="action" value="cp_add" />';?>
<?php wp_nonce_field( 'poll_nonce_action', 'poll_nonce_field' ); ?>
<h3><?php _e('Poll Add','cool-poll');?></h3>
<table width="100%" border="0" cellspacing="10" align="left">
	<tr>
		<td  style="width:300px;"><strong><?php _e('Poll Question','cool-poll');?></strong></td>
		<td><input type="text" name="cp_ques" class="widefat" required /></td>
	</tr>
	<tr>
		<td><strong><?php _e('Author','cool-poll');?></strong></td>
		<td><input type="text" name="cp_author" value="<?php $user = wp_get_current_user(); echo $user->user_login;?>" readonly style="background-color: white;border:1px solid;"></td>
	</tr>
	
	<tr>
		<td><strong><?php _e('Status','cool-poll');?></strong></td>
		<td><select name="cp_status"><?php echo cpoll_get_poll_status_selected();?></select></td>
	</tr>
	<tr>
	   <td><strong><?php _e('Poll Answers','cool-poll');?></strong></td>
	   <td>&nbsp;</td>
	</tr>
	
	<tr>
		<td><div class="ans_fields_label_wrap"><div><div style="margin-bottom: 25px;margin-top: 10px;">Answer 1</div></div></div></td>
		<td><div class="ans_fields_wrap"><div><p><input type="text" name="cp_anss[]" class="widefat"></p></div></div></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><button class="add_more_ans button"><?php _e('Add Answer','cool-poll');?></button></td>
	</tr>
	<tr>
	   <td><strong><?php _e('Poll Visualisation','cool-poll');?></strong></td>
	   <td>&nbsp;</td>
	</tr>
	<tr>
		<td><div class="ans_fields_bar_desc_wrap">
		      <div>
		        <div style="margin-bottom: 25px;margin-top: 10px;">
		        Answer 1 bar color
		        </div>
		       
      		</div>
		    </div>
		</td>
		<td><div class="ans_fields_bar_wrap">
		      <div><p>
		      <select name="cp_anss_bar[]" class="widefat">
      		  <option> violet</option> <option> orange</option> <option> yellow</option> <option> red</option> <option> azure</option> <option> green</option> <option> blue</option> <option> grey</option><option> black</option> 
      		</select></p>
      		</div>
      	</div>
      </td>
	</tr>
	<tr>
	   <td><?php _e('Background color','cool-poll');?></td>
	   <td><input type="color" name="bg-color" value="#ffffff"></td>
	</tr>
	
	<tr>
	   <td><?php _e('Use template background color','cool-poll');?></td>
	   <td><input type="checkbox" name="wp-bg-color" checked></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><input type="submit" name="submit" value="<?php _e('Submit','cool-poll');?>" class="button" /></td>
	</tr>
</table>
</form>