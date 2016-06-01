<?php 
require_once('../../../wp-load.php');
global $wpdb;
$table_name = $wpdb->prefix . "answer_my_question";

$dataArr = array();
$dataArr['result'] = false;
header('Content-type: application/json');

//Only valid form values can be passed
$possible_values = array(
	$_POST['name'],
	$_POST['email'],
	$_POST['subject'],
	$_POST['question'],
);

if(isset($_POST['url'])){
	$possible_values['url'] = $_POST['url'];
}

if(isset($_POST['notify'])){
	$possible_values['notify'] = $_POST['notify'];
}

//Unset the rest of these values
foreach($_POST as $key=>$value){
	if(!in_array($value, $possible_values)){
		unset($_POST[$key]);
	}
}

//Record this new question in the database
$data = array(
	"date_asked" => date('Y-m-d H:i:s'),
	"user_name" => strip_tags(stripslashes($_POST['name'])),
	"subject" => strip_tags(stripslashes($_POST['subject'])), 
	"question" => strip_tags(stripslashes($_POST['question'])) 
);

foreach($data as $item){
	if(empty($item)){
		$dataArr['result'] = false;
		echo json_encode($dataArr);
		return;
	}
}

if(isset($_POST['email'])){
	$data['user_email'] = $_POST['email'];
}

if(isset($_POST['url'])){
	$data['url'] = $_POST['url'];
}

if(isset($_POST['notify'])){
	$data['notify_user'] = $_POST['notify'];
}

//DB row has been inserted. Send email notifications to the admin or admins if they requested it
if($wpdb->insert($table_name, $data) === 1){
	$table_name = $wpdb->prefix . "options";
	$result = $wpdb->get_results("SELECT option_value FROM $table_name WHERE option_name = 'amq_option_item' LIMIT 1;");

	if(count($result) > 0){
		foreach($result as $row){
			$modalDetails = unserialize($row->option_value);
		}
	}
	
	if($modalDetails['notify'] === 1 && !empty($modalDetails['email'])){
		//The message
		$to      = str_replace("\n", ",", $modalDetails['email']);
		$subject = $_POST['subject'];
		$message = "Hello,\n".$_POST['name']." asks the following question:\n-----------------------------------------------------------\n".$_POST['question']."";
		$headers = 'From: '.$_POST['email'].'' . "\r\n" .
		'X-Mailer: PHP/' . phpversion();

		//Send
		mail($to, $subject, $message, $headers);
	}
	$dataArr['result'] = true;
}
echo json_encode($dataArr);
return;
?>
