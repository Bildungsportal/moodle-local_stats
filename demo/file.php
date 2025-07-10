<?php

require_once(__DIR__ . '/../inc.php');

require_login();

// $task = new \local_docverify\task\publish_files();
// $task->execute();

$context = \context_system::instance();
require_capability('moodle/site:config', $context);

$reportid = required_param('reportid', PARAM_INT);
$report = \local_stats\reportlib::get($reportid);
$chart = \local_stats\reportlib::get_chart_filled($report);

$pngFilePath = tempnam($CFG->tempdir, 'chart').'.png';

\local_stats\reportinglib::render_chart_to_file($chart, $pngFilePath);

// Read the PNG file and encode it as base64
$pngData = file_get_contents($pngFilePath);
$base64Image = base64_encode($pngData);

unlink($pngFilePath);

// Generate the HTML with the embedded image
echo '<img src="data:image/png;base64,' . $base64Image . '" alt="Embedded PNG Image">';

echo '<h2>JSON:</h2>';
echo "<pre>";

echo json_encode($chart, JSON_PRETTY_PRINT);
