<?php
require_once dirname(__FILE__) . "/inc/conf.php";

$todo = filter_input(INPUT_POST, 'todo');

if ($todo == 'addsubgroup') {
    $parentId = filter_input(INPUT_POST, 'parentid');
    $accountName = pe(filter_input(INPUT_POST, 'name'));

    query("insert into account_groups(parent_id, name) values ($parentId,'$accountName')");
}

function addAccountRow($indent, $row, &$Page)
{
    $space = "";
    for ($i = 0; $i < $indent; $i++) $space .= "&nbsp;&nbsp;";

    $nameEscaped = htmlentities($row['name']);
    $Page['contents'] .= "<tr>
<td>$space $row[name]&nbsp;&nbsp;</td>
<td>$row[description]&nbsp;&nbsp;</td>
<td style='text-align: center;'>

<span title='Add a subgroup' onclick='addSubAccountGroup($row[id], \"$nameEscaped\")' style='cursor: pointer;'>
<i class='fas fa-plus-square'></i>
</span>

</td>
</tr>";
}

function listAccounts($parentId, $indent, &$Page)
{
    $parentIdString = " is null";
    if ($parentId != null) $parentIdString = "=$parentId";

    $entityRes = query("select * from account_groups where parent_id$parentIdString order by id");
    while ($row = fetch_array($entityRes)) {
        addAccountRow($indent, $row, $Page);
        // Look for subacounts
        $count = query_row("select count(*) from account_groups where parent_id=$row[id]");
        if ($count['count'] > 0) {
            listAccounts($row['id'], $indent + 1, $Page);
        }
    }
}

$Page['contents'] .= "<table><tr style='background-color:#000;color:#fff;font-weight: bold;'>
<td style='min-width: 100px; text-align: center;'>Name</td>
<td style='min-width: 100px; text-align: center;'>Description</td>
<td style='min-width: 100px; text-align: center;'>Operations</td>
</tr>";

listAccounts(null, 0, $Page);

$Page['contents'] .= "</table>";

$Page['title'] = "Account groups";

include dirname(__FILE__) . "/templates/responsive.php";