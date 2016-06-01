<?php
require_once('../../../wp-load.php');

header('Content-type: application/json');

global $wpdb;
$table_name = $wpdb->prefix . "options";
//Get the plugin options
$options = $wpdb->get_results("SELECT option_value FROM $table_name WHERE option_name = 'amq_option_item' LIMIT 1;");

if (count($options) > 0) {
	foreach($options as $row) {
		$details = unserialize($row->option_value);
	}
}

$ansTitle = (!empty($details['anstitle']) ? sprintf( __('%s'), $details['anstitle']) : __('Question and Answer'));
$category = (!empty($details['category']) ? array($details['category']) : array());
$column = (!empty($details['column']) ? $details['column'] : array());
$thumbID = (!empty($details['imageid']) ? $details['imageid'] : 0);

$column = get_term($column, "rubriche");
$column = $column->name;

$qPostContent = "[post_amq ids=" . $_POST["ids"] . "]";
$qPostId = $_POST["postId"];

$qPost = array(
	'post_content' => $qPostContent,
	'post_title' => $ansTitle,
	'post_category' => $category,
	'tax_input' => array("rubriche" => $column)
);
if ($qPostId > 0)
	$qPost['ID'] = $qPostId;

$result = wp_insert_post($qPost, true);

$content = get_post_field('post_content', $result, 'raw');
$content = str_replace(array('<p>', '</p>'), array('', ''), $content);
$wpdb->update( $wpdb->prefix . "posts", array( 'post_content' => $content ), array( 'ID' => $result ) );

set_post_thumbnail($result, $thumbID);

$dataArr = array();
if (is_wp_error($result)) {
	$dataArr['result'] = false;
} else {
	$dataArr['result'] = true;
}

echo json_encode($dataArr);
return;
?>