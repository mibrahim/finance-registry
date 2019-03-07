<?php

require_once dirname(__FILE__) . "/conf.php";
require_once dirname(__FILE__) . "/utils.php";

$q = filter_input(INPUT_GET, 'q');

$row = query_row("select * from txns where key=$q");

$date = $row['date'];

$row['date'] = date("Y-m-d", $date);

echo json_encode($row, JSON_PRETTY_PRINT);