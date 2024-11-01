<?php
/*
Plugin Name:WP Cool Poll
Plugin URI: http://coolpoll.funsite.cz/
Description: Plugin makes it possible to create and manage a poll and display it in a widget.
Author: Martin Fiala
Text Domain: cool-poll
Domain Path: /languages
Version: 1.3
*/

define("CPOLL_PLUGIN_DIR", basename(dirname(__FILE__)));


class cool_poll_wid extends WP_Widget {
	
	public function __construct() {
		parent::__construct(
	 		'cool_wid',
			'Cool Poll Widget',
			array( 'description' => __( 'Widget to display selected poll.', 'cool-poll' ), )
		);
	 }
	 
	
	public static function curPageURL() {
		$pageURL = 'http';
		if (isset($_SERVER["HTTPS"]) and $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
			$pageURL .= "://";
		if (isset($_SERVER["SERVER_PORT"]) and $_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	} 
	 
	 
	public function did_ip_vote($poll_id)
	{
		global $wpdb;
		
		$query = $wpdb->prepare("SELECT * FROM ".$wpdb->prefix."cool_poll_votes WHERE p_id = %d AND user_ip = %s", $poll_id, $_SERVER["REMOTE_ADDR"]);
		$data = $wpdb->get_results($query);
		return (count($data) > 0); 
		
	} 

	public function widget( $args, $instance ) {
		extract( $args );
		echo $args['before_widget'];
		    
	      if(isset($_GET["ans"]) && isset($_GET["poll"]) && !$this->did_ip_vote($instance["poll_id"]) && $instance["poll_id"] == $_GET["poll"] && ctype_digit($_GET["ans"])) 
         {
         	
         	global $wpdb;
         	
         	$query = $wpdb->prepare("SELECT COUNT(*) AS pocet FROM ".$wpdb->prefix."cool_poll_a WHERE a_id=%d AND p_id=%d", sanitize_text_field($_GET["ans"]), $instance["poll_id"]);
         	$check = $wpdb->get_results($query);
         	if(count($check) && $check[0]->pocet == 1)
         	  $wpdb->insert($wpdb->prefix."cool_poll_votes", array("p_id" => $instance["poll_id"], "a_id" => sanitize_text_field($_GET["ans"]), "user_ip" => sanitize_text_field($_SERVER["REMOTE_ADDR"]), 'v_added' => current_time( 'mysql' )), array("%d", "%d", "%s", "%s" ));
         }				
		
			
			
			$data = cool_poll_settings::get_one_row_db($instance["poll_id"]);
			$data1 = cool_poll_settings::get_poll_answers_db($instance["poll_id"]);
			
			$total = cool_poll_settings::get_total_votes($instance["poll_id"]);
			
			if($data['p_status'] != 'Active')
			{
			  
			   return;
		   }
			//var_dump($_GET);

         
		//echo CPOLL_PLUGIN_DIR;	
		?>	
		
      <div style="width: 100%;margin: 0 auto;padding: 8px 10px;border: 1px solid #ccc;box-sizing: border-box;border-radius: 5px;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;-ms-border-radius: 5px;<?php if($data["p_wp_bg_color"] == 0) echo 'background-color:'.$data["p_bg_color"].';'; ?>">
       <h3><?php echo stripslashes($data['p_ques']);?></h3>          
          <form name="poll" id="cool-poll" method="post" action="" style="">
               					
					<input type="hidden" name="action" value="submit_poll" />
                    <input type="hidden" name="p_id" value="<?php echo $instance['poll_id'];?>" />
                    <input type="hidden" name="curr_page_url" value="<?php echo $this->curPageURL();?>">
					<?php 
					if(is_array($data1)){
					echo '<ul style="list-style-type:none;width:100%">';
						foreach($data1 as $key => $value){
							echo '<li>';
							
							
							if(!$this->did_ip_vote($instance["poll_id"])) echo '<a href="?ans='.$value["a_id"].'&poll='.$instance['poll_id'].'">';
							echo stripslashes($value['a_ans']);
							if(!$this->did_ip_vote($instance["poll_id"])) echo '</a>';
							
							echo '<div style="width:80%">';
							if($total != 0) 
							{
                        $perc = round(cool_poll_settings::get_answer_votes($value["a_id"])*100/$total, 2);								
								echo $perc."% (".cool_poll_settings::get_answer_votes($value["a_id"]).")<br>";
								echo '<img src="'.plugins_url().'/'.CPOLL_PLUGIN_DIR.'/imgs/progress-'.trim($value["a_bar_color"]).'.png" style="width:'.$perc.'%;height:10px;">';
							}
							echo '</div>';
							echo '</li>';
						}
					echo '</ul>';
					//echo '<br>';
					//$total = 10000000000000;
					echo '<div style="text-align:center;margin-top:10px;width:100%">'.__("Total Votes: ", "cool-poll").$total.'</div>';
					}
					?>
					 
					
				</form>      
      
      </div>		
		
		<?php	
		echo $args['after_widget'];
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['poll_id'] = sanitize_text_field( $new_instance['poll_id'] );
		return $instance;
	}
	
	public function form( $instance ) {
		$poll_id = @$instance[ 'poll_id' ];
		
		?>
		
		<p><label for="<?php echo $this->get_field_id('poll_id'); ?>"><?php _e('Poll:','cool-poll'); ?> </label>
		<select class="widefat" id="<?php echo $this->get_field_id('poll_id'); ?>" name="<?php echo $this->get_field_name('poll_id'); ?>"><option value="">-</option>
		
		<?php 
		global $wpdb;
      $query = "SELECT * FROM ".$wpdb->prefix."cool_poll_q where p_status='Active' order by p_added desc";
		$results = $wpdb->get_results( $query, ARRAY_A );
		
		
		foreach ( $results as $key => $value ) {
			if($poll_id == $value['p_id']){
				$ret .= '<option value="'.$value['p_id'].'" selected="selected">'.stripslashes($value['p_ques']).'</option>';
			} else {
				$ret .= '<option value="'.$value['p_id'].'">'.stripslashes($value['p_ques']).'</option>';
			}
		}		
		echo $ret;
		
		?>
		</select></p>
		-->
		<?php 
	}
	
}

// Register Cool Poll_Widget
add_action( 'widgets_init', 'register_foo' );
     
function register_foo() { 
    register_widget( 'cool_poll_wid' ); 
}
 
 
 



class cool_poll_init {
     static function install() {
        global $wpdb;
		$create_table = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."cool_poll_q` (
		  `p_id` int(11) NOT NULL AUTO_INCREMENT,
		  `p_ques` varchar(255) NOT NULL,
		  `p_author` int(11) NOT NULL,
		  `p_added` datetime NOT NULL,
		  `p_status` enum('Active','Inactive','Deleted','Stopped') NOT NULL,
		  `p_bg_color` varchar(20),
		  `p_wp_bg_color` tinyint(1),
		  PRIMARY KEY (`p_id`)
		)";
		$wpdb->query($create_table);
		$create_table = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."cool_poll_a` (
		  `a_id` int(11) NOT NULL AUTO_INCREMENT,
		  `p_id` int(11) NOT NULL,
		  `a_ans` varchar(255) NOT NULL,
		  `a_order` int(11) NOT NULL,
		  `a_bar_color` varchar(20),
		  PRIMARY KEY (`a_id`)
		)";
		$wpdb->query($create_table);
		$create_table = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."cool_poll_votes` (
		  `v_id` int(11) NOT NULL AUTO_INCREMENT,
		  `p_id` int(11) NOT NULL,
		  `a_id` int(11) NOT NULL,
		  `user_id` int(11) NOT NULL,
		  `user_ip` varchar(50) NOT NULL,
		  `v_added` datetime NOT NULL,
		  PRIMARY KEY (`v_id`)
		)";
		$wpdb->query($create_table);
     }
	 static function uninstall() {}
}
register_activation_hook( __FILE__, array( 'cool_poll_init', 'install' ) );

register_deactivation_hook( __FILE__, array( 'cool_poll_init', 'uninstall' ) );




//////////////////////////////////////////////////////////////////////////////
// pagination class
//////////////////////////////////////////////////////////////////////////////


	class cool_poll_paginate{
		
		public $per_page = 15;
				
		public $tot_rec = 0;
		
		public $tot_page = 0;
		
		public $curr_page = '';
		
		public $paged_str = 'page_num';
	
		public function __construct($per_page = '', $paged_str = ''){
			if( $per_page != '' ){
				$this->per_page	= $per_page;
			}
			if( $paged_str != '' ){
				$this->paged_str = $paged_str;
			}
		}
		
	
		public function initialize($query = '', $curr_page = ''){
			global $wpdb;
			if(!$query){
				return;
			}
			$wpdb->get_results($query, ARRAY_A);
			$this->tot_rec = $wpdb->num_rows; 
			$this->tot_page = ceil($this->tot_rec/$this->per_page);
			$this->curr_page = $curr_page;
			//$start = $this->starting_point();
			$query .= " LIMIT ".$this->starting_point().", ".$this->per_page."";
			$data = $wpdb->get_results($query, ARRAY_A);
			return $data;
		}
	
		public function starting_point(){
			if(!$this->curr_page){
				$page = 1;
				$this->curr_page = 1;
			} else {
				$page = $this->curr_page;
			}
	
			$start = ($page-1)*$this->per_page;
			return $start;
		}
	
		public function paginate(){
			
			
			
			echo paginate_links( array('base' => add_query_arg( $this->paged_str, '%#%' ), 'format' => '', 'prev_text' => __('Previous', 'cool-poll'), 'next_text' => __('Next', 'cool-poll'), 'total' => $this->tot_page, 'current' => $this->curr_page));
			
		}
	}







/////////////////////////////////////////////////////////////////////////////////////////////////
// main cool poll class, handles creation and editing of polls and showing reports
////////////////////////////////////////////////////////////////////////////////////////////////

class cool_poll_settings {

	public function __construct() {
		$this->load_settings();
	}
	
		
	
   public function jQueryAnswerScript(){?>
	 <script type="text/javascript">
	jQuery(document).ready(function() {
		//alert("jquery runs");
		var max_fields      = 7; 
		var wrapper         = jQuery(".ans_fields_wrap"); 
		var wrapper2        = jQuery(".ans_fields_bar_wrap");
		var wrapper3        = jQuery(".ans_fields_bar_desc_wrap");
		var wrapper4        = jQuery(".ans_fields_label_wrap");
		var add_button      = jQuery(".add_more_ans"); 
		
		var x = wrapper.children('div').length; 
		jQuery(add_button).click(function(e){ 
			e.preventDefault();
			if(x < max_fields){ 
				x++; 
				jQuery(wrapper).append('<div style="margin-bottom:20px"><input type="text" name="cp_anss[]" class="widefat " style="width:96%"/> <a href="#" id="'+x+'" class="remove_field">X</a></div>');
				jQuery(wrapper2).append('<div id="div'+x+'" style="margin-bottom:20px;"><select name="cp_anss_bar[]" class="widefat " style="width:96%"> <option> violet</option> <option> orange</option> <option> yellow</option> <option> red</option> <option> azure</option> <option> green</option> <option> blue</option> <option> grey</option><option> black</option> </select></div>');
				jQuery(wrapper3).append('<div id="2div'+x+'" style="margin-bottom:30px;">Answer '+x+' bar color</div>');
				jQuery(wrapper4).append('<div id="3div'+x+'" style="margin-bottom:30px;">Answer '+x+'</div>'); 
			}
		});
	   
		jQuery(wrapper).on("click",".remove_field", function(e){
			e.preventDefault(); jQuery(this).parent('div').remove(); x--;
			jQuery('#div'+jQuery(this).attr('id')).remove();
			jQuery('#2div'+jQuery(this).attr('id')).remove();
			jQuery('#3div'+jQuery(this).attr('id')).remove();
			//alert('#div'+jQuery(this).id);
		})
		
	});
	</script>
	<?php }	
	

   public function table_columns($value){

		$ret = '';
		if(is_array($value)){
			foreach($value as $vkey => $vval){
				$ret .= $this->gener_data($vkey, $vval, $value, $value["p_id"]);
			}
		}
		
		$ret .= $this->gener_actions($value['p_id']);
		return $ret;
	} 
	
	public function gener_actions($id){
		return '<td><a href="'.'?page=cool_polls&action=edit&id='.$id.'">'.__("Edit", "cool-poll").'</a>|<a onclick="return cpConfirmRemove();" href="'.wp_nonce_url( '?page=cool_polls&action=cp_delete&id='.$id, 'poll_nonce_action'.$id, 'poll_nonce' ).'">'.__("Delete", "cool-poll").'</a></td>';
	
	
	}	

   	public function gener_data($key, $value, $all, $poll_id){
		$sh = false;
		switch ($key){
			case 'p_id':
			$v = $value;
			//$sh = true;
			break;
			case 'p_ques':
			$v = '<a href="?page=cool_polls&action=poll_report&p_id='.$poll_id.'">'.stripslashes($value)."</a>";
			$sh = true;
			break;
			case 'p_author':
			$v = get_user_by("id", $value)->user_login;
			$sh = true;
			break;
			case 'p_start':
			$v = $value;
			$sh = true;
			break;
			case 'p_end':
			$v = $value;
			$sh = true;
			break;
			case 'p_status':
			$v = ' <strong>'.$value.'</strong>';
			$sh = true;
			break;
			default:
			//$v = $value; 
			break;
		}
		if($sh){
			return '<td>'.$v.'</td>';
		}
	} 	
 	
 	
 	public function get_tbody($data){
		$ret = '';
		$cnt = 0;
		if(is_array($data) and count($data)){
			$ret .= '<tbody id="cp-poll-list">';
			foreach($data as $key => $val){
				$ret .= '<tr class="'.($cnt%2==0?'alternate':'').'">';
				$ret .= $this->table_columns($val);
				$ret .= '</tr>';
				$cnt++;
			}
			$ret .= '</tbody>';
		} else {
			$ret .= '<tbody id="cp-poll-list">';
			$ret .= '<tr>';
			$ret .= '<td align="center" colspan="4">'.__('No records found','cool-poll').'</td>';
			$ret .= '</tr>';
			$ret .= '</tbody>';	
		}
		return $ret;
	}
	
   static public function get_one_row_db($id){
		global $wpdb;
		$query = $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."cool_poll_q where p_id = %d", $id );
		$result = $wpdb->get_row( $query, ARRAY_A );
		return $result;
	}
	
	static public function get_poll_answers_db($id){
		global $wpdb;
		$query = $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."cool_poll_a where p_id = %d order by a_order", $id );
		$result = $wpdb->get_results( $query, ARRAY_A );
		return $result;
	}	
	
   public function is_poll_started($p_id = ''){
		if($p_id == ''){
			return false;
		}
		global $wpdb;
		
		$query = $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."cool_poll_q where p_id = %d and p_status = 'Active'", $p_id );
		$result = $wpdb->get_row( $query, ARRAY_A );
		if($result){
			return true;
		} else {
			return false;
		}
	}	
	
	static public function get_total_votes($p_id = ''){
		if($p_id == ''){	
			return;
		}
		global $wpdb;
		
		$query = $wpdb->prepare( "SELECT count(*) as tot FROM ".$wpdb->prefix."cool_poll_votes where p_id = %d", $p_id );
		$result = $wpdb->get_row( $query, ARRAY_A );
		if($result){
			return $result['tot']; 
		} else {
			return 0;
		}
	}
	
	static public function get_answer_votes($a_id = ''){
		if($a_id == ''){	
			return;
		}
		global $wpdb;
		
		$query = $wpdb->prepare( "SELECT count(*) as tot FROM ".$wpdb->prefix."cool_poll_votes where a_id = %d", $a_id);
		$result = $wpdb->get_row( $query, ARRAY_A );
		if($result){
			return $result['tot']; 
		} else {
			return 0;
		}
	}
	
	public function get_ans_from_a_id($a_id = ''){
		if($a_id == ''){	
			return;
		}
		global $wpdb;
		
		$query = $wpdb->prepare( "SELECT a_ans FROM ".$wpdb->prefix."cool_poll_a where a_id = %d", $a_id );
		$result = $wpdb->get_row( $query, ARRAY_A );
		if($result){
			return sanitize_text_field(stripslashes($result['a_ans'])); 
		} else {
			return;
		}
	}
	
	public function cool_poll_options(){
		
		
		if(isset($_REQUEST['action']) and $_REQUEST['action'] == 'edit'){
			
			$id = sanitize_text_field($_REQUEST['id']);
		   $data = $this->get_one_row_db($id);
		   $data1 = $this->get_poll_answers_db($id);
		   require( 'poll_edit.php');
		   $this->jQueryAnswerScript();
		} elseif(isset($_REQUEST['action']) and $_REQUEST['action'] == 'add'){
			
			require( 'poll_add.php');
		   $this->jQueryAnswerScript();
		} elseif(isset($_REQUEST['action']) and $_REQUEST['action'] == 'poll_report'){
			
         ?>			
			
         <h3><?php _e('Poll Report','cool-poll');?></h3>
         <table width="100%" border="0" cellspacing="10">
         <tr>
            <td><?php 
               
               
      $p_id = sanitize_text_field($_REQUEST["p_id"]);         
      if($p_id != ''){
			
		   
	   	global $wpdb;
		   $ret = '';
	   	$query =  $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."cool_poll_votes where p_id = %d order by v_added desc", $p_id );
		   $result = $wpdb->get_results( $query, ARRAY_A );
	   	$p_ans_data = $this->get_poll_answers_db($p_id);
		
         
         $query = $wpdb->prepare( "SELECT p_ques FROM ".$wpdb->prefix."cool_poll_q where p_id = %d", $p_id );
    		$result2 = $wpdb->get_row( $query, ARRAY_A );
	   	if($result2){
		   	$quest = sanitize_text_field(stripslashes($result2['p_ques'])); 
	   	} else {
		      $quest = "";
		   }		
		
   		$ret .= '<h3>'.$quest.'</h3>';
	   	$ret .= '<table width="100%" >';
		
   		if( is_array($p_ans_data) and count($p_ans_data)){
	   		foreach ($p_ans_data as $key => $value){
	   			$total_votes = $this->get_total_votes($p_id);
		         $total_votes_by_ans = $this->get_answer_votes($value["a_id"]);
		   		$ret .= '<tr>';
			   	$ret .= '<td><strong>'.stripslashes($value['a_ans']).' ('.$total_votes_by_ans.'/'.$total_votes.') '.'</strong></td>';
		   		$ret .= '</tr>';
			   }
	   	}
	   	$ret .= '</table>';
		
   		$ret .= '<table width="100%" >';
	   	$ret .= '<tr bgcolor="#ccc" style="height:30px;">';
   		$ret .= '<td width="33%" align="center"><strong>'.__('Answer','cool-poll').'</strong></td>';
   		$ret .= '<td width="33%" align="center"><strong>'.__('IP','cool-poll').'</strong></td>';
   		$ret .= '<td width="33%" align="center"><strong>'.__('Date','cool-poll').'</strong></td>';
   		$ret .= '</tr>';
		
	   	$cnt = 1;
	   	if( is_array($result) and count($result)){
		   	foreach ($result as $key => $value){
			   	$ret .= '<tr bgcolor="'.($cnt%2==0?'#f1f1f1':'#fff').'">';
			   	$ret .= '<td width="33%" align="center">'.$this->get_ans_from_a_id($value['a_id']).'</td>';
		         $ret .= '<td width="33%" align="center">'.$value['user_ip'].'</td>';
		   		$ret .= '<td width="33%" align="center">'.$value['v_added'].'</td>';
			   	$ret .= '</tr>';
		   		$cnt++;
	   		} 
		   } else {
		   	$ret .= '<tr>';
				$ret .= '<td colspan="3" align="center">'.__('No records found','cool-poll').'</td>';
	   		$ret .= '</tr>';
		   }
 	   	$ret .= '</table>';
	   	echo $ret;
	   }//if p_id!= ''
            ?></td>
         </tr>
         </table>			
			
			<?php
		} else{  //display list of polls
			
			global $wpdb;
		
		   if(isset($_REQUEST['search']) and $_REQUEST['search'] == 'p_search'){
			  if(isset($_REQUEST['p_ques'])){
				  $query = $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."cool_poll_q where p_status <> 'Deleted' and p_ques like %s order by p_added desc", '%'.sanitize_text_field($_REQUEST['p_ques']).'%' );
		   	}
	   	} else {
		   	$query = "SELECT * FROM ".$wpdb->prefix."cool_poll_q where p_status <> 'Deleted' order by p_added desc";
		   }
		
		   $ap = new cool_poll_paginate(10);
		   $data = $ap->initialize($query, sanitize_text_field(@$_REQUEST['page_num']));
		
		   echo '<h3>' . __('Polls','cool-poll') .'</h3><h3><a href="'."?page=cool_polls".'&action=add" style="display:block;width:130px;text-decoration:none;border:1px solid;margin:5px;border-radius:3px;padding:5px;margin-bottom:20px;">'.__('Add New Poll','cool-poll').'</a>' . '</h3>';
		   //var_dump($data);
	
                	
	      require('poll_search.php');
	      echo "<br>";
	      
         if(/*!isset($_REQUEST["search"]) && $_REQUEST["search"] != "p_search" && */!isset($_REQUEST["p_ques"]) || empty($_REQUEST["p_ques"])) 
         {
         	echo __("Showing ", "cool-poll").($ap->starting_point()+1)." - ".($ap->starting_point()+count($data)).__(" of total ", "cool-poll").$ap->tot_rec.__(" polls.", "cool-poll");
         }	else echo __("Showing filtered polls.", "cool-poll");
	      ?>
	      <table class="wp-list-table widefat ap-table">
	      <thead>
           <tr>
             
             <td>
             <?php echo __('Poll','cool-poll'); ?>             
             </td> 
             <td>
             <?php echo __('Author','cool-poll'); ?>             
             </td> 
             <td>
             <?php echo __('Status','cool-poll'); ?>             
             </td> 
             <td>
             <?php echo __('Action','cool-poll'); ?>             
             </td>           
           </tr>	      
	      </thead>
	      <?php
         echo $this->get_tbody($data);	      
	      echo "</table>";
	      
	      
		   echo '<div style="margin-top:10px;">';
		   echo $ap->paginate();
		   echo '</div>';
		}
	}
	
	public function cool_poll_menu () {
		add_menu_page( 'Cool Poll', 'Cool Poll', 'activate_plugins', 'cool_polls', array( $this,'cool_poll_options' ), 'dashicons-chart-bar' );
			
	}
	
	public function load_settings(){
		add_action( 'admin_menu' , array( $this, 'cool_poll_menu' ) );
		
	}

}

new cool_poll_settings;







////////////////////////////////////////////////////////////////////////////////////////////
// functions handle saving of forms to database
/////////////////////////////////////////////////////////////////////////////////////////////

//if(!function_exists( 'start_session_if_not_started' )){
	function cpoll_start_session(){
		if(!session_id()){
			@session_start();
		}
	}
//}



function cpoll_is_poll_started($p_id = ''){
		if($p_id == ''){
			return false;
		}
		global $wpdb;
		
		$query = $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."cool_poll_q where p_id = %d and p_status = 'Active'", $p_id );
		$result = $wpdb->get_row( $query, ARRAY_A );
		if($result){
			return true;
		} else {
			return false;
		}
	}
	
	
function cpoll_is_poll_stopped($p_id = ''){
		if($p_id == ''){
			return false;
		}
		global $wpdb;
		
		$query = $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."cool_poll_q where p_id = %d and p_status = 'Stopped'", $p_id );
		$result = $wpdb->get_row( $query, ARRAY_A );
		if($result){
			return true;
		} else {
			return false;
		}
	}	
	
	
		

function cool_process_poll_data(){
		
	if(isset($_REQUEST['action']) and $_REQUEST['action'] == 'cp_delete'){
		cpoll_start_session();
		if ( ! isset( $_REQUEST['poll_nonce'] ) || ! wp_verify_nonce( $_REQUEST['poll_nonce'], 'poll_nonce_action'.sanitize_text_field($_REQUEST['id']) ) ) {
		   wp_die( 'Sorry, your nonce did not verify.');
		} 
		global $wpdb;
		
		$update =  array('p_status' => 'Deleted');
		$data_format = array( '%s' );
		$where = array('p_id' => sanitize_text_field($_REQUEST['id']));
		$data_format1 = array( '%d' );
		$wpdb->update( $wpdb->prefix."cool_poll_q", $update, $where, $data_format, $data_format1 );
		
		wp_redirect("?page=cool_polls");
		exit;
	}
	
	if(isset($_REQUEST['action']) and $_REQUEST['action'] == 'changeStatus'){
		cpoll_start_session();
		if ( ! isset( $_REQUEST['poll_nonce_field'] ) || ! wp_verify_nonce( $_REQUEST['poll_nonce_field'], 'poll_nonce_action') ) {
		echo 'Sorry, your nonce did not verify.';
		exit;
		} 
		
		global $wpdb;
		
		if(!ctype_digit($_REQUEST["cp_id"])) {echo "Wrong cp_id"; exit;}
		$query = $wpdb->prepare( "SELECT p_status FROM ".$wpdb->prefix."cool_poll_q where p_id = %d", sanitize_text_field($_REQUEST["cp_id"] ));
		$result = $wpdb->get_row( $query, ARRAY_A );
		switch($result["p_status"]) {
			case "Inactive":
			break;
			case "Active":
			if($_REQUEST["cp_status"] == "Inactive") 
			{
				echo "Status not allowed!";
				 exit;
			}
			break;
			case "Stopped":
			if(($_REQUEST["cp_status"] == "Inactive") || $_REQUEST["cp_status"] == "Active") 
			{
				echo "Status not allowed!";
				 exit;
			}
			break;
			case "Deleted":
			exit;
			break;
		}
		$update =  array('p_status' => sanitize_text_field($_REQUEST['cp_status']));
		$data_format = array( '%s' );
		$where = array('p_id' => sanitize_text_field($_REQUEST['cp_id']));
		$data_format1 = array( '%d' );
		$wpdb->update( $wpdb->prefix."cool_poll_q", $update, $where, $data_format, $data_format1 );
		echo 'Poll status updated successfully.';
		exit;
	}
	
	
	if(isset($_REQUEST['action']) and $_REQUEST['action'] == 'cp_edit'){
		cpoll_start_session();
		if ( ! isset( $_REQUEST['poll_nonce_field'] ) || ! wp_verify_nonce( $_REQUEST['poll_nonce_field'], 'poll_nonce_action') ) {
		wp_die( 'Sorry, your nonce did not verify.');
		exit;
		} 
		global $wpdb;
		
		
      if(!ctype_digit($_REQUEST["cp_id"])) {echo "Wrong cp_id"; exit;}
		$query = $wpdb->prepare( "SELECT p_status FROM ".$wpdb->prefix."cool_poll_q where p_id = %d", sanitize_text_field($_REQUEST["cp_id"]) );
		$result = $wpdb->get_row( $query, ARRAY_A );
		switch($result["p_status"]) {
			case "Inactive":
			break;
			case "Active":
			if($_REQUEST["cp_status"] == "Inactive") 
			{
				echo "Status not allowed!";
				 exit;
			}
			break;
			case "Stopped":
			if(($_REQUEST["cp_status"] == "Inactive") || $_REQUEST["cp_status"] == "Active") 
			{
				echo "Status not allowed!";
				 exit;
			}
			break;
			case "Deleted":
			exit;
			break;
		}		
		
		
		
		if(cpoll_is_poll_started(sanitize_text_field($_REQUEST['cp_id']))){
			
			wp_redirect("?page=cool_polls&action=edit&id=".$_REQUEST['cp_id']);
			exit;
		}
		
		if(isset($_REQUEST["wp-bg-color"]) && $_REQUEST["wp-bg-color"] == "on") 
      {
      	$wp_bg_color = true;
      }	else $wp_bg_color = false;	
		
		$update =  array('p_ques' => sanitize_text_field($_REQUEST['cp_ques']),	'p_author' => sanitize_text_field(get_user_by("login", sanitize_text_field($_REQUEST['cp_author']))->ID), 'p_status' => sanitize_text_field($_REQUEST['cp_status']), 'p_bg_color' => sanitize_text_field($_REQUEST['bg-color']), 'p_wp_bg_color' => $wp_bg_color	);
		$data_format = array('%s', '%s', '%s', '%s', '%d' );
		$where = array('p_id' => sanitize_text_field($_REQUEST['cp_id']));
		$data_format1 = array('%d');
		$wpdb->update( $wpdb->prefix."cool_poll_q", $update, $where, $data_format, $data_format1 );
		
		// remove old answers and add new ones //
		$wpdb->delete( $wpdb->prefix."cool_poll_a", $where, $data_format1 );
		$p_anss = $_REQUEST['cp_anss'];
		if(is_array($p_anss) and sanitize_text_field($_REQUEST['cp_id'])){
			foreach($p_anss as $key => $value){
				if($value != ''){
					$insert1 = array('p_id' => sanitize_text_field($_REQUEST['cp_id']), 'a_ans' => sanitize_text_field($value),	'a_order' => $key+1, 'a_bar_color' => sanitize_text_field($_REQUEST['cp_anss_bar'][$key]));
					$data_format = array('%d', '%s', '%d', '%s' );
					$wpdb->insert( $wpdb->prefix."cool_poll_a", $insert1, $data_format );
				}
			}
		}
		// remove old answers and add new ones //
		
		
		wp_redirect("?page=cool_polls&action=edit&msg=success&id=".$_REQUEST['cp_id']);
		exit;
	}
	
	
	//add new poll to db
	if(isset($_REQUEST['action']) and $_REQUEST['action'] == 'cp_add'){
		cpoll_start_session();
		if ( ! isset( $_REQUEST['poll_nonce_field'] ) || ! wp_verify_nonce( $_REQUEST['poll_nonce_field'], 'poll_nonce_action') ) {
		wp_die( 'Sorry, your nonce did not verify.');
		exit;
		} 
		global $wpdb;
		

      if(isset($_REQUEST["wp-bg-color"]) && $_REQUEST["wp-bg-color"] == "on") 
      {
      	$wp_bg_color = true;
      }	else $wp_bg_color = false;	
		
		$insert = array('p_ques' => sanitize_text_field($_REQUEST['cp_ques']), 'p_author' => sanitize_text_field(get_user_by("login", sanitize_text_field($_REQUEST['cp_author']))->ID), 'p_added' => current_time( 'mysql' ), 'p_status' => sanitize_text_field($_REQUEST['cp_status']), 'p_bg_color' => sanitize_text_field($_REQUEST['bg-color']), 'p_wp_bg_color' => $wp_bg_color);
		//echo $_REQUEST['wp-bg-color'];
		$data_format = array('%s', '%s', '%s', '%s', '%s', '%d' );
		
		$wpdb->insert( $wpdb->prefix."cool_poll_q", $insert, $data_format );
		$new_poll_id = $wpdb->insert_id;
		
		$p_anss = $_REQUEST['cp_anss'];
		if(is_array($p_anss) and $new_poll_id){
			foreach($p_anss as $key => $value){
				if($value != ''){
					$insert1 = array('p_id' => $new_poll_id, 'a_ans' => sanitize_text_field($value), 'a_order' => $key+1, 'a_bar_color' => sanitize_text_field($_REQUEST['cp_anss_bar'][$key]));
					$data_format = array('%d', '%s', '%d', '%s' );
					$wpdb->insert( $wpdb->prefix."cool_poll_a", $insert1, $data_format );
				}
			}
		}
		
		
	}//end add
	
}

add_action( 'admin_init', 'cool_process_poll_data' );

/////////////////////////////////////////////////////////////////////////////////////////
// load translations
/////////////////////////////////////////////////////////////////////////////////////////

function cool_poll_text_domain(){
		load_plugin_textdomain('cool-poll', FALSE, basename( dirname( __FILE__ ) ) .'/languages');
	}
add_action( 'plugins_loaded', 'cool_poll_text_domain' );



////////////////////////////////////////////////////////////////////////////////////////
// load jquery and js
////////////////////////////////////////////////////////////////////////////////////////
function cpoll_load_scripts()
{
   //global $pagenow;
   //var_dump($pagenow);
   //var_dump($_GET["page"]);
   //exit;
   if(/*$pagenow == "admin.php" && */ isset($_GET["page"]) && $_GET["page"] == "cool_polls") {   
          
	  wp_enqueue_script('jquery' /*, array(), null, true*/);
   }
	
	wp_enqueue_script( 'cool-poll-js', plugins_url( CPOLL_PLUGIN_DIR.'/js/cool-poll-js.js' ) );
}
add_action('wp_enqueue_scripts', 'cpoll_load_scripts');	
add_action('admin_enqueue_scripts', 'cpoll_load_scripts');


?>