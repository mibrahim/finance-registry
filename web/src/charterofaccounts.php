<?php
require_once dirname(__FILE__) . "/inc/conf.php";

$Page['title'] = "Charter of accounts";

$entity = filter_input(INPUT_GET, 'entity');

if ($entity == null) {
    $Page['contents'] .= "<h1 style='color:#F44;'>ERROR: Click on an entity from the <a href='entities.php'>entities</a></h1>";
} else {
    $entityRow = query_row("select * from entity where id = $entity");
    $Page['sub_title'] = "Entity: " . htmlentities($entityRow['name']);

    $todo = filter_input(INPUT_POST, 'todo');
    if ($todo == 'addaccount') {
        $name = pe(filter_input(INPUT_POST, "name"));
        $description = pe(filter_input(INPUT_POST, "description"));
        $group = pe(filter_input(INPUT_POST, "group"));

        query("insert into account(name,description,group_id) values ('$name','$description',$group)");
    }

    $Page['contents'] .= "<h2>Add an account <button onclick='addAccount($entity)'><i class=\"fas fa-plus-square\"></i></button> </h2>";

    function listAccounts($res, &$Page)
    {
        $Page['contents'] .= "<table style='width:500px'>";
        while ($row = fetch_array($res)) {
            $Page['contents'] .= "<tr>";
            $Page['contents'] .= "<td><a href='register.php?id=$row[id]'>" . htmlentities($row['name']) . "</a></td>";
            $favStyle = "color: #888";
            if ($row['is_favorite'] == 1)
                $favStyle = "color: #F88";
            $Page['contents'] .= "<td>$row[group_name]</td>";
            $Page['contents'] .= "<td><button onclick='favorite($row[id])'><i style='$favStyle' class=\"fas fa-heart\"></i></button></td>";
            $Page['contents'] .= "</tr>";
        }
        $Page['contents'].="</table>";
    }

    $Page['contents'] .= "<h1><i class=\"fas fa-heart\"></i> Favorite accounts</h1>";

    $res = query("select account.*, account_groups.name as group_name from account, account_groups " .
        " where " .
        " is_favorite = 1 and account.group_id=account_groups.id order by account.name");
    listAccounts($res, $Page, $entity);

    $Page['contents'] .= "<h1><i class=\"fas fa-university\"></i> All accounts</h1>";

    $res = query("select account.*, account_groups.name as group_name from account, account_groups " .
        " where account.group_id=account_groups.id order by name");
    listAccounts($res, $Page, $entity);
}

include dirname(__FILE__) . "/templates/responsive.php";