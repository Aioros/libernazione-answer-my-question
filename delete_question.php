<?php
require_once('../../../wp-load.php');
global $wpdb;
$table_name = $wpdb->prefix . "answer_my_question";

header('Content-type: application/json');

$wpdb->query("
	DELETE FROM $table_name
	WHERE id IN ({$_POST['ids']})"
);

if($wpdb->rows_affected > 0){
	$dataArr['result'] = true;
}else{
	$dataArr['result'] = false;
}
echo json_encode($dataArr);
return;
?>