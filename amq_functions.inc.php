<?php 
if ( !defined('ABSPATH') ) define('ABSPATH', dirname(__FILE__));
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
global $amq_db_version;
global $amqplugindir;
$amq_db_version = "1.3";
$amqplugindir = plugin_dir_url(__FILE__);

//Update the question data
if(isset($_POST['posted']) && $_POST['posted'] == 1 && $_POST['id']){
	updateQuestionData($_POST);

	//Show success notification
	echo '
	<div class="updated"> 
		<p><strong>'.__("Updates Saved!", "answer-my-question").'</strong></p>
	</div>';
}

/**
 *
 * Runs the required SQL when the plugin is activated
 *
 * @param    none
 * @return	 none
 */
function amq_install() {
   global $wpdb,$amq_db_version,$table_name;
   $table_name = $wpdb->prefix . "answer_my_question";
      
   $sql = "CREATE TABLE $table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  date_asked DATETIME NOT NULL,
		  date_response DATETIME NOT NULL,
		  user_name VARCHAR(60) NOT NULL,
		  user_email VARCHAR(60) NOT NULL,
		  url VARCHAR(60) DEFAULT '' NOT NULL,
		  subject VARCHAR(60) NOT NULL,
		  question TEXT NOT NULL,
		  answer TEXT NOT NULL,
		  answered tinyint(1) NOT NULL,
		  notify_user tinyint(1) NOT NULL,
		  show_on_site tinyint(1) NOT NULL DEFAULT '1',
		  published tinyint(1) NOT NULL,
		  UNIQUE KEY id (id));";	

   dbDelta($sql);
   add_option("amq_db_version", $amq_db_version);
   
   //Update Database Schema
   $installed_ver = get_option("amq_db_version");
   if($installed_ver != $amq_db_version) {
	   $sql = "CREATE TABLE $table_name (
			  id mediumint(9) NOT NULL AUTO_INCREMENT,
			  date_asked DATETIME NOT NULL,
			  date_response DATETIME NOT NULL,
			  user_name VARCHAR(60) NOT NULL,
			  user_email VARCHAR(60) NOT NULL,
			  url VARCHAR(60) DEFAULT '' NOT NULL,
			  subject VARCHAR(60) NOT NULL,
			  question TEXT NOT NULL,
			  answer TEXT NOT NULL,
			  answered tinyint(1) NOT NULL,
			  notify_user tinyint(1) NOT NULL,
			  show_on_site tinyint(1) NOT NULL DEFAULT '1',
			  published tinyint(1) NOT NULL,
			  UNIQUE KEY id (id));";	
	   
	   dbDelta($sql);
	   update_option("amq_db_version", $amq_db_version);
   }
}

/**
 *
 * Compares the current database version with the version recorded as installed in the users database. If they don't match, amq_install is run to update the schema
 *
 * @param    none
 * @return	 none
 */
function amq_update_db_check() {
    global $amq_db_version;
    if (get_site_option('amq_db_version') != $amq_db_version) {
        amq_install();
    }
}


/**
 *
 * Adds top level and sub level menu item for the plugin. Editor and above can use the plugin
 *
 * @param    none
 * @return	 none
 */
function register_amq_menu_page() {
   add_menu_page("Chiedi a Rosario Tuo", "Chiedi a Rosario Tuo", "delete_pages", "answer-my-question", "answerMyQuestionView");
   add_submenu_page("answer-my-question", "", "Settings", "delete_pages", "answer-my-question-settings", "answerMyQuestionSettings");
}

/**
 *
 * Enqueues the plugin specific JavaScript and CSS to to the head for the main admin page
 *
 * @param    none
 * @return	 none
 */
function amq_admin_scripts() {
	global $amqplugindir;
	global $wp_scripts;
	wp_enqueue_script('jquery-ui-dialog');
	wp_enqueue_script('jquery-ui-draggable');
	wp_enqueue_script('jquery-ui-droppable');
	wp_enqueue_script('amq-main', $amqplugindir . '/js/main.js', array("jquery-ui-dialog"));
	wp_enqueue_style("jquery-ui", "http://ajax.googleapis.com/ajax/libs/jqueryui/{$wp_scripts->registered['jquery-ui-core']->ver}/themes/redmond/jquery-ui.min.css");
	wp_enqueue_style("amq-admin-main", plugins_url('css/admin_main.css', __FILE__));
}

/**
 *
 * Inserts the form markup
 *
 * @param    none
 * @return	 none
 */
function insert_form() {
	wp_enqueue_style("amq-form", plugins_url('css/answer_my_question_form.css', __FILE__));
	wp_enqueue_script('amq-scripts', plugins_url('js/answer_my_question_scripts.js', __FILE__), false, false, true);

	global $wpdb;
	$table_name = $wpdb->prefix . "options";
	
	//Get the plugin options
	//$result = $wpdb->get_results("SELECT option_value FROM $table_name WHERE option_name = 'amq_option_item' LIMIT 1;");
	$result = get_option("amq_option_item");
	
	if(count($result) > 0){
		foreach($result as $row){
			$formDetails = unserialize($row->option_value);
		}
	}

	$formTitle = (!empty($formDetails['title']) ? sprintf( __('%s', "answer-my-question"), $formDetails['title']) : __('Answer My Question', "answer-my-question"));
	$formBody = (!empty($formDetails['body']) ? str_replace("\n", "<br>", sprintf( __('%s', "answer-my-question"), $formDetails['body'])) : __('Please fill out the form below.', "answer-my-question"));
	
    return '<div id="answer-my-question-form">
	<div class="inner">
		<h1>'.$formTitle.'</h1>
		<h2 id="message-sent">'.__("Your question has been sent!").'</h2>
		<img id="sending-loader" src="'.WP_PLUGIN_URL.'/answer-my-question/images/ajax-loader.gif" alt="" />
		<div class="form-contents">
			<p>'.$formBody.'</p>
			<form id="question-form" action="" method="post">

				<input type="text" name="name" tabindex="1" placeholder="'.__("Name ", "answer-my-question").'" autocomplete="off" />
				<input type="text" name="email" tabindex="2" placeholder="'.__("Email ", "answer-my-question").'" autocomplete="off" />
				<input type="text" name="url" tabindex="3" placeholder="'.__("URL", "answer-my-question").'" autocomplete="off" />
				<input type="text" name="subject" tabindex="4" placeholder="'.__("Subject", "answer-my-question").'" autocomplete="off" />
				<textarea name="question" tabindex="5" placeholder="'.__("Question", "answer-my-question").'" ></textarea>
			
				<span class="legend"><span class="required">*</span> '.__("Required Field", "answer-my-question").'</span>
				<input type="hidden" name="post_location" id="post_location" value="'.WP_PLUGIN_URL.'/answer-my-question/record_question.php" />
				<button class="clean-gray" id="send">'.__("Send", "answer-my-question").'</button>
			</form>
		</div>
	</div>
</div>';
}

/**
 *
 * Front end markup for the admin panel screen
 *
 * @param    none
 * @return	 none
 */
function answerMyQuestionView() {
	amq_admin_scripts();
	global $amqplugindir,$wpdb;
	
	$table_name = $wpdb->prefix . "options";
	//Get the plugin options
	//$options = $wpdb->get_results("SELECT option_value FROM $table_name WHERE option_name = 'amq_option_item' LIMIT 1;");
	$options = get_option("amq_option_item");

	if (count($options) > 0) {
		foreach($options as $row) {
			$details = unserialize($row->option_value);
		}
	}
	$amq_category = (!empty($details['category']) ? $details['category'] : "");
	$amq_column = (!empty($details['column']) ? $details['column'] : "");
	
	$table_name = $wpdb->prefix . "answer_my_question";
	
	//Get a list of all questions
	$result = $wpdb->get_results("SELECT id, date_asked, user_name, user_email, subject, show_on_site, answered, published FROM $table_name ORDER BY date_asked DESC");

	if(count($result) > 0){
		//wp_editor("", "tinymceloader");
		?>
		<div id="sending-loader" style="display: none;">
			<img src="<?php echo WP_PLUGIN_URL; ?>/answer-my-question/images/ajax-loader.gif" alt="" />
		</div>
		<div id="post_creation">
			<h3>Create post</h3>
			<button class="cancel_answers button-primary" style="display:none;">Cancel</button>
			<button class="select_answers button-primary">Select answers</button>
			<button class="post_answers button-primary" disabled>Create post</button>
			<table class="to_be_posted" style="display: none;"></table>
			<span class="drag_label" style="display: none;">Trascina qui</span>
		</div>
		<div id="post_editing" style="display: none;">
			<h3>Edit post</h3>
			<button class="cancel_answers button-primary" style="display:none;">Cancel</button>
			<button class="select_answers button-primary">Select answers</button>
			<button class="post_answers button-primary" disabled>Update post</button>
			<table class="to_be_posted" style="display: none;"></table>
			<span class="drag_label" style="display: none;">Trascina qui</span>
		</div>
		<div id="answer_deletion">
			<h3>Delete answers</h3>
			<button id="cancel_delete" class="button-primary" style="display:none;">Cancel</button>
			<button id="select_delete" class="button-primary">Select answers</button>
			<button id="delete_answers" class="button-primary" disabled>Delete</button>
		</div>
		<div style="clear:both;"></div>
		
		<div id="questions">
		<?php
	
		echo '<div id="pager" class="pager">
					<img src="'.$amqplugindir.'/images/first.png" class="first">
					<img src="'.$amqplugindir.'/images/prev.png" class="prev">
					<input type="text" class="pagedisplay">
					<img src="'.$amqplugindir.'/images/next.png" class="next">
					<img src="'.$amqplugindir.'/images/last.png" class="last">
					<select class="pagesize">
						<option value="10">10</option>
						<option selected="selected" value="20">20</option>
						<option value="30">30</option>
						<option value="40">40</option>
						<option value="50">50</option>
					</select>
			</div>
			<table id="amq_list" class="tablesorter"> 
			<thead> 
			<tr>
				<th class="select-delete" style="display:none;">Delete</th>
				<th style="width: 10%">'.__("Date", "answer-my-question").'</th>
				<th style="width: 10%">'.__("Status", "answer-my-question").'</th>				
				<th>'.__("Name", "answer-my-question").'</th> 
				<th>'.__("Email", "answer-my-question").'</th> 
				<th>'.__("Subject", "answer-my-question").'</th>'.
				//<th><span title="'.__("Set to NO for each question you don\'t want to display. Click the table table cell to toggle the display state").'" class="info">'.__("Show On Site").'</span></th>
				'<th style="width: 13%">'.__("Actions", "answer-my-question").'</th> 
			</tr> 
			</thead> 
			<tbody>';
			$questions = array();
			foreach($result as $row){
				$questions[$row->id] = $row;
				$answerStatus = ($row->answered == 0 ? __('unanswered', "answer-my-question") : ($row->published == 0 ? __('answered', "answer-my-question") : __('published', "answer-my-question")));
				//$displayStatus = ($row->show_on_site == 0 ? '<span class="display_no">'.__("No").'</span>' : '<span class="display_yes">'.__("Yes").'</span>');
				echo '<tr data-status="'.$row->answered.'" data-id="'.$row->id.'"> 
						<td class="select-delete" style="display:none;"><input type="checkbox" rel="'.$row->id.'"/></td>
						<td>'.date("d/m/Y", strtotime($row->date_asked)).'</td> 
						<td class="'.$answerStatus.'">'.ucfirst($answerStatus).'</td> 
						<td>'.$row->user_name.'</td> 
						<td>'.$row->user_email.'</td>
						<td>'.$row->subject.'</td>'.
						//<td class="display_status" rel="'.$row->id.'">'.$displayStatus.'</td> 
						'<td class="actions">
						<a href="" class="answer" rel="'.$row->id.'"><img src="'.$amqplugindir.'/images/icon_answer.png" alt="'.__("Answer Question", "answer-my-question").'" title="'.__("Answer This Question", "answer-my-question").'"></a>
						<a href="" class="delete" rel="'.$row->id.'"><img src="'.$amqplugindir.'/images/icon_delete.png" alt="'.__("Delete Question", "answer-my-question").'" title="'.__("Delete This Question", "answer-my-question").'"></a>';
					/*if ($row->answered != 0)
						echo '<a href="" class="post" rel="'.$row->id.'"><img src="'.$amqplugindir.'/images/icon_mail.png" alt="'.__("Post Question").'" title="'.__("Post This Question").'"></a>';*/
						
						echo '</td>
					 </tr>';
			}
			echo '</tbody> 
			</table>';
			?>
			</div>
			<table id="amq_posts">
				<thead><th><h3>Previous posts</h3></th></thead>
				<?php
				if ($amq_column) {
					$tax_query = array(array(
						"taxonomy" => "rubriche",
						"field" => "term_id",
						"terms" => array($amq_column)
					));
				} else {
					$tax_query = array(array(
						"taxonomy" => "category",
						"field" => "term_id",
						"terms" => array($amq_category)
					));
				}
				$amq_query = new WP_Query(array(
					"post_type" => "post",
					"tax_query" => $tax_query,
					"posts_per_page" => 5
				));
				while ($amq_query->have_posts()) {
					$amq_query->the_post();
					$post = $amq_query->post;
					$content = $post->post_content;
					preg_match("/\[post_amq ids=([^\]]+)\]/", $content, $matches);
					$question_ids = $matches[1];
					$question_ids = explode(",", $question_ids);
					?>
					<tr><td>
						<table class="amq_post" data-post-id="<?php echo $post->ID; ?>">
						<thead><th colspan="4" class="amq_post_title"><?php echo $post->post_title; ?></th></thead>
						<?php
						$count = 0;
						foreach ($question_ids as $question_id) {
							$question = $questions[$question_id]; ?>
							<tr class="amq_post_question<?php if ($count % 2 == 0) echo ' even'; else echo ' odd'; ?>" data-question-id="<?php echo $question->id; ?>">
							<td><?php echo date("d/m/Y", strtotime($question->date_asked)); ?></td>
							<td><?php echo $question->user_name; ?></td>
							<td style="display: none;"><?php echo $question->user_email; ?></td>
							<td><?php echo $question->subject; ?></td>
							</tr>
							<?php 
							$count++;
						} ?>
						</table>
					</td></tr>
				<?php } ?>
			</table>
			<div id="amq_modal">
			<form id="amq_form" action="" method="post">
				<input type="hidden" name="id" value="">
				<input type="hidden" name="posted" value="1">
				<input type="hidden" name="notify" value="">
				<input type="hidden" name="user_email" value="">
				<h1 id="question_title"><em></em> asks:</h1>
				<input type="text" name="subject" id="subject" value="">
				<textarea class="question_text" name="question" id="question"></textarea>
				<h1 id="answer_title"><?php echo (strlen($answer) > 0 ? 'Modify Your Answer' : 'Your Answer');?>:</h1>
				<?php wp_editor("", "answer"); ?>
				<button class="clean-gray" id="save">Save</button>
				<button class="clean-gray" id="cancel">Cancel &amp; Close</button>
			</form>
			</div>
			<?php
	}else{
		echo '<strong>'.__("Looks like you don't have any questions yet!", "answer-my-question").'</strong>';
	}
	echo '<input type="hidden" value="'.$amqplugindir.'" id="plugin_path">';
} 

/**
 *
 * Updates the database record for the question
 *
 * @param	 array		$data: Array containing all posted question data
 * @return	 boolean 	True if row has been updated. False otherwise
 */
function updateQuestionData($data=array()){
	global $wpdb;
	$table_name = $wpdb->prefix . "answer_my_question";
	
	$questionID = $data['id'];
	$notifyUser = $data['notify'];
	$userEmail = $data['user_email'];
	unset($data['id'], $data['posted'], $data['notify'], $data['user_email']);
	$data['date_response'] = date("Y-m-d G:i:s");
	$data['answered'] = 1;
	
	$data = stripslashes_deep($data);
	
	$wpdb->update(
	  $table_name,
	  $data,
	  array('id' => $questionID)
	);
	
	if($wpdb->rows_affected > 0){
		//Should user be notified?
		if($notifyUser == 1){
			
			$result = $wpdb->get_results("SELECT answer FROM $table_name WHERE id = $questionID LIMIT 1;");	
			foreach($result as $row){
				$response = $row->answer;
				
			}
			sendNotificationMail($userEmail, $data['subject'], $data['date_response'], $response);
		}
	}
	return true;
}

/**
 *
 * Send an email notification to a user that requested to be notified
 *
 * @param	 string		$email: Email Address
 * @param	 string		$subject: The subject of the users question
 * @param	 string		$responseDate: Date of admin response. Should be in MySQL datetime format
 * @return	 boolean 	True if email was sent. False otherwise
 */
function sendNotificationMail($email, $subject, $responseDate, $response){
	$to  = $email;
	$emailSubject = 'Your Question Has Been Answered!';
	$message = '
	<html>
	<head>
	  <title>Your Question Has Been Answered!</title>
	</head>
	<body>
		<p>Hello,<br>
		This is an automated email from <a href="'.site_url().'">'.get_bloginfo("name").'</a>.</p> 
		<p>Your question titled: <strong>'.$subject.'</strong>, has been answered on <strong>'.date("F j, Y", strtotime($responseDate)).'</strong>.</p>
		<hr>
		<p><em>"'.$response.'"</em></p>
	</body>
	</html>';

	// To send HTML mail, the Content-type header must be set
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

	// Additional headers
	// NOTE: If multiple admins, you need to explode here and change $headers
	$headers .= 'To: '.$email.' <'.$email.'>' . "\r\n";
	$headers .= 'From: '.get_bloginfo('admin_email').' <'.get_bloginfo('admin_email').'>' . "\r\n";
	
	// Mail it
	if(mail($to, $emailSubject, $message, $headers)){
		return true;
	}else{	
		return false;
	}
}

/**
 *
 * Settings options page
 *
 * @param    none
 * @return	 none
 */
function answerMyQuestionSettings() {
	amq_admin_scripts();
	if($_GET['settings-updated'] == true){
		echo '<div id="setting-error-settings_updated" class="updated settings-error"> 
				<p><strong>'.__("Settings saved", "answer-my-question").'.</strong></p>
			  </div>';
	}
	?>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br></div>
		<h2><?php _e("Settings", "answer-my-question");?></h2>
		<form method="post" action="options.php">
			<?php settings_fields('amq_options'); ?>
			<?php $options = get_option('amq_option_item'); ?>
			<table class="form-table">
				<tr valign="top"><th scope="row"><?php _e("Form title", "answer-my-question");?></th>
					<td><input class="regular-text" type="text" name="amq_option_item[title]" value="<?php echo (isset($options['title']) ? $options['title'] : ''); ?>" /> <span class="description"><?php _e("The title text of the question form window", "answer-my-question");?></span></td>
				</tr>
				<tr valign="top"><th scope="row"><?php _e("Form body", "answer-my-question");?></th>
					<td><textarea style="width: 25em; height: 9em; vertical-align: top;" name="amq_option_item[body]"><?php echo (isset($options['body']) ? $options['body'] : ''); ?></textarea> <span class="description"><?php _e("Intro text for the form window body", "answer-my-question");?></span></td>
				</tr>
				<tr valign="top"><th scope="row"><?php _e("Answer post title", "answer-my-question");?></th>
					<td><input class="regular-text" type="text" name="amq_option_item[anstitle]" value="<?php echo (isset($options['anstitle']) ? $options['anstitle'] : ''); ?>" /> <span class="description"><?php _e("The title text of the posted question/answer", "answer-my-question");?></span></td>
				</tr>
				<tr valign="top"><th scope="row"><?php _e("Answer post category", "answer-my-question");?></th>
					<td><?php wp_dropdown_categories(array("hide_empty" => false, "name" => "amq_option_item[category]", "selected" => (isset($options['category']) ? $options['category'] : 0))); ?> <span class="description"><?php _e("The category of the posted question/answer", "answer-my-question");?></span></td>
				</tr>
				<tr valign="top"><th scope="row"><?php _e("Answer post column", "answer-my-question");?></th>
					<td><?php wp_dropdown_categories(array("hide_empty" => false, "taxonomy" => "rubriche", "show_option_none" => "-", "name" => "amq_option_item[column]", "selected" => (isset($options['column']) ? $options['column'] : 0))); ?> <span class="description"><?php _e("The column of the posted question/answer", "answer-my-question");?></span></td>
				</tr>
				<tr valign="top"><th scope="row"><?php _e("Notify me of new questions", "answer-my-question");?></th>
					<td><input id="notify" style="width: 1em;" name="amq_option_item[notify]" type="checkbox" value="1" <?php checked('1', (isset($options['notify']) ? $options['notify'] : '')); ?> /></td>
				</tr>
				<tr valign="top"><th scope="row"><?php _e("Email Address", "answer-my-question");?></th>
					<td>
					<textarea id="email" style="width: 25em; height: 9em; vertical-align: top;" name="amq_option_item[email]"><?php echo (isset($options['email']) ? $options['email'] : ''); ?></textarea>
					 <span class="description"><?php _e("One email address per line", "answer-my-question");?>.</span></td>
				</tr>
				<tr valign="top"><th scope="row"><?php _e("Answer CSS", "answer-my-question");?></th>
					<td><textarea style="width: 40em; height: 15em; vertical-align: top;" name="amq_option_item[css]"><?php echo (isset($options['css']) ? $options['css'] : ''); ?></textarea> <span class="description"><?php _e("Additional style for published answers", "answer-my-question");?></span></td>
				</tr>
				<tr valign="top"><th scope="row"><?php _e("Answer post image", "answer-my-question");?></th>
					<td>
					<?php if (!isset($options['imageurl']) || strlen($options['imageurl']) == 0) {
						$display = "display: none;";
					} ?>
					<img id="amq_image" style="height: 200px;<?php echo $display; ?>" src="<?php echo $options['imageurl']; ?>" />
					<input type="button" class="amq_image_select" data-title="<?php _e("Choose featured image", "answer-my-question");?>" value="<?php _e("Browse", "answer-my-question"); ?>" />
					<input type="hidden" id="amq_imageid" name="amq_option_item[imageid]" value="<?php echo (isset($options['imageid']) ? $options['imageid'] : ''); ?>" />
					<input type="hidden" id="amq_imageurl" name="amq_option_item[imageurl]" value="<?php echo (isset($options['imageurl']) ? $options['imageurl'] : ''); ?>" />
					<span class="description"><?php _e("The featured image for the posted question/answer", "answer-my-question");?></span></td>
				</tr>
			</table>
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes', "answer-my-question"); ?>" />
			</p>
		</form>
	</div>
<?php 
}

/**
 *
 * Shortcode to show a single question/answer pair that have been answered
 *
 * @param	 Array		$attrs: Shortcode attributes (ids = q/a ids).
 * @return	 Markup for the post
 */
function post_amq($attrs){
	global $wpdb;
	$table_name = $wpdb->prefix . "answer_my_question";

	$ids = $attrs["ids"];
	$results = $wpdb->get_results("SELECT *
									FROM $table_name
									WHERE answered = 1
									AND show_on_site = 1
									AND id IN ($ids)
									ORDER BY FIELD(id, $ids);");
	
	ob_start();
	?>
	<div class="amq_post">
	<?php for ($r = 0; $r<count($results); $r++) {
		$row = $results[$r];
		$userName = $row->user_name;
		$question = $row->question;
		$subject = $row->subject;
		$userEmail = $row->user_email;
		$answer = $row->answer;
		$responseDate = $row->date_response;
		$notify = ($row->notify_user == 1 ? 1 : 0);
		?>
		<div class="amq_single">
			<h1 class="question_title"><em><?php echo $userName;?></em> asks:</h1>
			<div class="question_subject"><?php echo $subject;?></div>
			<div class="question_content"><?php echo $question;?></div>
			<h1 class="answer_title">Your Answer:</h1>
			<div class="answer_content"><?php echo $answer;?></div>
		</div>
		<?php if ($r < count($results)-1) { ?>
			<hr>
		<?php } ?>
	<?php } ?>
	</div>
	<?php
	$output = ob_get_contents();
	ob_end_clean();
	
	return $output;
	
}

/**
 *
 * Sanitize and validate input
 *
 * @param    array	$input: Array of form values
 * @return	 array	$input: Array of sanitized values for the database
 */
function amq_options_validate($input) {
	//Only valid form values can be passed
	$possible_values = array(
		$input['title'],
		$input['body'],
		$input['anstitle'],
		$input['category'],
		$input['column'],
		$input['css'],
		$input['imageid'],
		$input['imageurl']
	);
	
	if(isset($input['notify'])){
		$possible_values[] = $input['notify'];
		$input['notify'] = ($input['notify'] == 1 ? 1 : 0);
	}
	
	if(isset($input['email'])){
		$possible_values[] = $input['email'];
	}
	
	foreach($input as $key=>$value){
		if(!in_array($value, $possible_values)){
			unset($input[$key]);
		}
	}
	
	// No HTML tags
	$input['title'] =  wp_filter_nohtml_kses($input['title']);
	$input['body'] =  wp_filter_nohtml_kses($input['body']);
	$input['anstitle'] =  wp_filter_nohtml_kses($input['anstitle']);
	$input['css'] = wp_filter_nohtml_kses($input['css']);
	
	return $input;
}

load_plugin_textdomain('answer-my-question', false, basename( dirname( __FILE__ ) ) . '/languages' );

add_filter("the_content", "add_css_content");
function add_css_content($content) {
	global $wpdb;
	global $post;
	$table_name = $wpdb->prefix . "options";
	//Get the plugin options
	//$options = $wpdb->get_results("SELECT option_value FROM $table_name WHERE option_name = 'amq_option_item' LIMIT 1;");
	$options = get_option("amq_option_item");

	if (count($options) > 0) {
		foreach($options as $row) {
			$details = unserialize($row->option_value);
		}
	}
	$amq_category = (!empty($details['category']) ? $details['category'] : "");
	$category = get_the_category();
	$category = $category[0]->cat_ID;
	$amq_column = (!empty($details['column']) ? $details['column'] : "");
	$column = get_the_terms( $post->ID, 'rubriche' );
	
	if (is_single() && !is_home() && ($amq_category == $category || isset($column[$amq_column]))) {
		$css = (!empty($details['css']) ? "<style>" . $details['css'] . "</style>" : "");
		$content = $css . $content;
	}
	return $content;
}

function check_published_questions($post_ID, $post_after, $post_before) {
	global $wpdb;
	$table_name = $wpdb->prefix . "options";
	//Get the plugin options
	//$options = $wpdb->get_results("SELECT option_value FROM $table_name WHERE option_name = 'amq_option_item' LIMIT 1;");
	$options = get_option("amq_option_item");

	if (count($options) > 0) {
		foreach($options as $row) {
			$details = unserialize($row->option_value);
		}
	}
	$amq_category = (!empty($details['category']) ? $details['category'] : "");
	$category = get_the_category($post_before->ID);
	$category = $category[0]->cat_ID;
	$amq_column = (!empty($details['column']) ? $details['column'] : "");
	$column = get_the_terms( $post_before->ID, 'rubriche' );
	
	if ($amq_category == $category || isset($column[$amq_column])) {
		$content_before = $post_before->post_content;
		preg_match("/\[post_amq ids=([^\]]+)\]/", $content_before, $matches);
		$question_ids_before = explode(",", $matches[1]);
		$content_after = $post_after->post_content;
		preg_match("/\[post_amq ids=([^\]]+)\]/", $content_after, $matches);
		$question_ids_after = explode(",", $matches[1]);
		$status_before = $post_before->post_status;
		$status_after = $post_after->post_status;
		$table_name = $wpdb->prefix . "answer_my_question";
		if ($status_before == "publish" && $status_after != "publish") {
			$query = "UPDATE $table_name
					SET published = published - 1
					WHERE id IN (" . implode(",", $question_ids_before) . ")";
			$wpdb->query($query);
		} else if ($status_before != "publish" && $status_after == "publish") {
			$query = "UPDATE $table_name
					SET published = published + 1
					WHERE id IN (" . implode(",", $question_ids_after) . ")";
			$wpdb->query($query);
		} else if ($status_before == "publish" && $status_after == "publish") {
			$added_ids = array_diff($question_ids_after, $question_ids_before);
			$removed_ids = array_diff($question_ids_before, $question_ids_after);
			$query = "UPDATE $table_name
					SET published = published - 1
					WHERE id IN (" . implode(",", $removed_ids) . ")";
			$wpdb->query($query);
			$query = "UPDATE $table_name
					SET published = published + 1
					WHERE id IN (" . implode(",", $added_ids) . ")";
			$wpdb->query($query);
		}
	}
}
add_action('post_updated', 'check_published_questions', 10, 3);
?>
