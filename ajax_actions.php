<?php 
require_once('../../../wp-load.php');
global $wpdb;
$table_name = $wpdb->prefix . "answer_my_question";

$dataArr = array();
$dataArr['result'] = false;
header('Content-type: application/json');

//Toggle the show on site value for this question
if(isset($_POST['display'])){
	if(!is_numeric($_POST['display']) || !is_numeric($_POST['questionID'])){
		die('invalid data was passed!');
	}
	
	$data['show_on_site'] = (int)$_POST['display'];
	
	$wpdb->update(
	  $table_name,
	  $data,
	  array('id' => $_POST['questionID'])
	);
	
	if($wpdb->rows_affected > 0){
		$dataArr['result'] = true;
		$dataArr['display'] = $data['show_on_site'];
	}
}

echo json_encode($dataArr);
return;
?>
