<?php
require_once dirname(__FILE__) . "/../inc/conf.php";

// Favorite
$favorite = filter_input(INPUT_GET, 'favorite');

if ($favorite != null) {
    $row = query_row("select * from account where id=$favorite");

    $is_favorite = 0;
    if ($row['is_favorite'] != null) $is_favorite = 1 - $row['is_favorite'];
    else $is_favorite = 1;

    query_row("update account set is_favorite=$is_favorite where id=$favorite");

    echo "SUCCESS";
} else {
    echo "FAIL";
}