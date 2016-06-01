<?php 
require_once('../../../wp-load.php');
global $wpdb;
$table_name = $wpdb->prefix . "answer_my_question";

$result = $wpdb->get_results("SELECT * FROM $table_name WHERE id=".$_POST['id']." LIMIT 1;");
?>
<div id="amq_modal">
	<form id="amq_form" action="" method="post">
		<?php 
			if(count($result) > 0){
				foreach($result as $row){
					$userName = $row->user_name;
					$question = $row->question;
					$subject = $row->subject;
					$userEmail = $row->user_email;
					$answer = $row->answer;
					$responseDate = $row->date_response;
					$notify = ($row->notify_user == 1 ? 1 : 0);
				}
			}else{
				echo '<strong>Error: could not select the question ID</strong>';
			}		
		?>
		<input type="hidden" name="id" value="<?php echo $_POST['id'];?>">
		<input type="hidden" name="posted" value="1">
		<input type="hidden" name="notify" value="<?php echo $notify;?>">
		<input type="hidden" name="user_email" value="<?php echo $userEmail;?>">
		<h1 id="question_title"><em><?php echo $userName;?></em> asks:</h1>
		<small>(tip: you may edit the subject and question)</small><br>
		<input type="text" name="subject" id="subject" value="<?php echo $subject;?>">
		<textarea class="question_text" name="question" id="question"><?php echo $question;?></textarea>
		<h1 id="answer_title"><?php echo (strlen($answer) > 0 ? 'Modify Your Answer' : 'Your Answer');?>:</h1>
		<textarea class="answer_text" name="answer" id="answer"><?php echo $answer;?></textarea>
		
		<?php if(strlen($answer) > 0):?>
			<p id="question_answered">You answered this question on <strong><?php echo date('F j, Y', strtotime($responseDate));?></strong></p>
		<?php else:?>
			<a href="mailto:<?php echo $userEmail;?>" id="email">Answer this user directly &raquo;</a>
		<?php endif;?>
		
		<button class="clean-gray" id="save">Save</button>
		<button class="clean-gray" id="cancel">Cancel &amp; Close</button>
	</form>
</div>