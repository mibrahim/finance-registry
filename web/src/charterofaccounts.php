<?php
require_once dirname(__FILE__) . "/inc/conf.php";

$Page['title'] = "Charter of accounts";

$entity = filter_input(INPUT_GET, 'entity');

if ($entity == null) {
    $Page['contents'] .= "<h1 style='color:#F44;'>ERROR: Click on an entity from the <a href='entities.php'>entities</a></h1>";
} else {

    $entityRow = query_row("select * from entity where id = $entity");
    $Page['sub_title'] = "Entity: " . htmlentities($entityRow['name']);

    // Favorite
    $favorite = filter_input(INPUT_GET, 'favorite');

    if ($favorite != null) {
        $row = query_row("select * from accounts where id=$favorite");

        $is_favorite = 0;
        if ($row['is_favorite'] != null) $is_favorite = 1 - $is_favorite;
        else $is_favorite = 1;

        query_row("update account set is_favorite=$is_favorite where id=$favorite");
    }

    $todo = filter_input(INPUT_POST, 'todo');
    if ($todo == 'addaccount') {
    }

    $Page['contents'] .= "<h2>Add an account <button onclick='addAccount($entity)'><i class=\"fas fa-plus-square\"></i></button> </h2>";

    function listAccounts($res, &$Page)
    {
        $Page['contents'] .= "<table>";
        while ($row = fetch_array($res)) {
            $Page['contents'] .= "<tr>";
            $Page['contents'] .= "<td><a href='register.php?id=$row[id]'>" . htmlentities($row['name']) . "</a></td>";
            $favStyle = "color: #888";
            if ($row['is_favorite'] == 1)
                $favStyle = "color: #F88";
            $Page['contents'] .= "<td><a href='?favorite=$row[id]'><i style='$favStyle' class=\"fas fa-heart\"></i></a></td>";
            $Page['contents'] .= "</tr>";
        }
    }

    $Page['contents'] .= "<h1><i class=\"fas fa-heart\"></i> Favorite accounts</h1>";

    $res = query("select * from account where is_favorite = 1 order by name");
    listAccounts($res, $Page);

    $Page['contents'] .= "<h1><i class=\"fas fa-university\"></i> All accounts</h1>";

    $res = query("select * from account order by name");
    listAccounts($res, $Page);
}

include dirname(__FILE__) . "/templates/responsive.php";