<?php 
require_once('../../../wp-load.php');
global $wpdb;
$table_name = $wpdb->prefix . "answer_my_question";

$result = $wpdb->get_results("SELECT * FROM $table_name WHERE id=".$_REQUEST['id']." LIMIT 1;");
echo json_encode($result[0]);
?>