<?php include_once("functions.php");?>

<form name="f" action="" method="post">
<?php echo '<input type="hidden" name="action" value="cp_edit" />';?>
<input type="hidden" name="cp_id" id="cp_id" value="<?php echo $id;?>" />
<?php wp_nonce_field( 'poll_nonce_action', 'poll_nonce_field' ); ?>
<strong> <?php if(isset($_GET["msg"]) && $_GET["msg"] == "success") _e("Poll succesfully updated.", "cool-poll");?></strong>
<h3><?php _e('Poll Edit', 'cool-poll');?></h3>
<table width="100%" border="0" cellspacing="10">
	<tr>
		<td style="width:300px;"><strong><?php _e('Poll Question','cool-poll');?></strong></td>
		<td><input type="text" name="cp_ques" value="<?php echo stripslashes($data['p_ques']);?>" class="widefat"/></td>
	</tr>
	<tr>
		<td><strong><?php _e('Author','cool-poll');?></strong></td>
		<td><input type="text" name="cp_author" value="<?php echo get_user_by("id", $data['p_author'])->user_login;?>" readonly style="background-color: white;border:1px solid;"> </td>
	</tr>
	
	<tr>
		<td><strong><?php _e('Status','cool-poll');?></strong></td>
		<td><select name="cp_status" id="cp_status"><?php echo cpoll_get_poll_status_selected($data['p_status']);?></select>
		<input type="button" name="submit" value="<?php _e('Save','cool-poll');?>" class="button" onclick="cpChangeStatus();" />
		</td>
	</tr>
	<tr>
		<td><strong><?php _e('Poll Answers','cool-poll');?></strong></td>
		<td><button class="add_more_ans button"><?php _e('Add More Answers','cool-poll');?></button></td>
	</tr>
	<tr>
		<td>
		<div class="ans_fields_label_wrap">
		<?php 
         if(is_array($data1))
         {
			  foreach($data1 as $key => $value)
			  {
			  	  echo '<div id="3div'.($key+1).'" style="margin-bottom:30px;">Answer '.($key+1).'</div>';
			  }
		   }		
		
		?>
		</div>
		</td>
		<td><div class="ans_fields_wrap">
		<?php 
		if(is_array($data1)){
			foreach($data1 as $key => $value){
				echo '<div style="margin-bottom:20px"><input type="text" name="cp_anss[]" value="'.stripslashes($value['a_ans']).'" class="widefat" style="width:96%"> <a href="#" class="remove_field" id="'.($key+1).'">X</a></div>';
			}
		}
		?></div>
		</td>
	</tr>
	
	
	<tr>
	   <td><strong><?php _e('Poll Visualisation','cool-poll');?></strong></td>
	   <td>&nbsp;</td>
	</tr>
	<tr>
		<td><div class="ans_fields_bar_desc_wrap">
		      <?php 
         if(is_array($data1))
         {
			  foreach($data1 as $key => $value)
			  {
			  	  echo '<div id="2div'.($key+1).'" style="margin-bottom:30px;">Answer '.($key+1).' bar color</div>';
			  }
		   }		
		
		?>
		    </div>
		</td>
		<td><div class="ans_fields_bar_wrap">
		<?php
		   if(is_array($data1))
         {
			  foreach($data1 as $key => $value)
			  {
              $bar_colors = array("violet", "orange", "yellow", "red", "azure", "green", "blue", "grey", "black");			  	  
			  	  echo '<div id="div'.($key+1).'" style="margin-bottom:20px;"><select name="cp_anss_bar[]" class="widefat " style="width:96%">'; 
			  	  foreach($bar_colors as $color)
			  	  {
			  	  	  if($value["a_bar_color"] == $color) 
			  	  	  { 
			  	  	     echo "<option selected>".$color."</option>";
			  	  	  }else echo "<option>".$color."</option>";
			  	  }
			  	   
			  	  echo '</select></div>';
			  }
		   }	
		   ?>
      	</div>
      </td>
	</tr>
	<tr>
	   <td><?php _e('Background color','cool-poll');?></td>
	   <td><input type="color" name="bg-color" value="<?php echo $data["p_bg_color"];?>"></td>
	</tr>
	
	<tr>
	   <td><?php _e('Use template background color','cool-poll');?></td>
	   <td><input type="checkbox" name="wp-bg-color" <?php if($data["p_wp_bg_color"]) echo "checked"; ?>></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>
		<?php if($this->is_poll_started($id)){
		_e('Poll is activated, only status can be updated now!','cool-poll');
		} else if(cpoll_is_poll_stopped($id)) 
		{
			_e('Poll is stopped, so can not be updated now!','cool-poll');
		}
		else
		 { ?>
		<input type="submit" name="submit" value="<?php _e('Submit','cool-poll');?>" class="button" />
		<?php } ?>
		</td>
	</tr>
	
</table>
</form>