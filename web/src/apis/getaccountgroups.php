<?php
require_once dirname(__FILE__) . "/../inc/conf.php";

function addAccountToArray($row, &$accountsArray, $indent)
{
    $row['indent'] = $indent;
    $accountsArray[] = [
        "id" => $row['id'],
        "name" => $row["name"],
        "description" => $row["description"],
        "parent_id" => $row["parent_id"],
        "indent" => $indent
    ];

    // Find all the children
    $res = query("select * from account_groups where parent_id=$row[id] order by name");

    while ($row2 = fetch_array($res))
        addAccountToArray($row2, $accountsArray, $indent + 1);
}

// Find all the children
$res = query("select * from account_groups where parent_id is null order by name");

$accountsArray = [];

while ($row2 = fetch_array($res))
    addAccountToArray($row2, $accountsArray, 0);


echo json_encode($accountsArray, JSON_PRETTY_PRINT);
