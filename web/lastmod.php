<?php
require_once dirname(__FILE__) . "/inc/conf.php";
require_once dirname(__FILE__) . "/inc/utils.php";
require_once dirname(__FILE__) . "/inc/processposts.php";

include "topbar.php";

$query = "select * from txns $filter order by lastmodified desc limit 300";

$result = query($query);

// Print the headings
$Page['contents'] .= '
<br/>
<br/>
<br/>
<br/>
<table class="table table-hover">
<thead>
    <tr class="thead-dark">
        <th>Operations</th>
        <th>Key</th>
        <th>DOM-ORD</th>';

if ($entity == null) $Page['contents'] .= '
        <th>Entity</th>
';

if ($account == null) $Page['contents'] .= '
        <th>Account</th>
';

$Page['contents'] .= '
        <th>Status</th>
        <th>Description</th>
        <th>Target</th>
        <th>Amount</th>
        <th>Balance</th>
        <th>Last Modified</th>
';

$Page['contents'] .= '
    </tr>
</thead>
';

$lastDate = "";

$income = 0;
$expense = 0;

while ($row = $result->fetchArray()) {
    $key = $row['key'];

    $editCode = " style='cursor: pointer;' onclick='fill($key)' data-toggle=\"modal\" data-target=\"#editModal\" class='reduced_padding fixed_font'";

    $rowColor = ' class="table-active"';

    if ($row['running_balance'] < 0) $rowColor = ' class="bg-warning"';
    else if ($row['status'] == 'PLANNED') $rowColor = ' class="table-light"';
    else if (strstr($row['target'], "INCOME:")) $rowColor = ' class="bg-success"';
    else if ($row['status'] == 'RECONCILED') $rowColor = ' class="table-success"';
    else if ($row['status'] == 'PAID') $rowColor = ' class="table-warning"';

    $Page['contents'] .= "<tr $rowColor>";
    $Page['contents'] .= "
            <td class='reduced_padding fixedfont'>
                <button onclick=\"duplicate($key)\" data-toggle=\"modal\" data-target=\"#addNewModal\"><i class=\"fas fa-copy\"></i></button>
                <form method='post' style='display:inline;'>
                    <input type='hidden' name='todo' value='deletetxn'>
                    <input type='hidden' name='key' value='$key'>
                    <button type='submit' onclick=\"return confirm('Are you sure?')\"><i class=\"fas fa-trash-alt\"></i></button>
                </form>
            </td>";
    $Page['contents'] .= "<td $editCode>" . $row['key'] . "</td>";
    $Page['contents'] .= "<td $editCode>" . date("d", $row['date']) . "-$row[ord]</td>";

    if ($entity == null) $Page['contents'] .= "<td $editCode>" . htmlentities($row['entity']) . '</td>';

    if ($account == null) $Page['contents'] .= "<td $editCode>" . $row['account'] . '</td>';

    $Page['contents'] .= "<td $editCode>" . $row['status'] . "</td>";

    $description = htmlentities($row['description']);
    $description = str_ireplace("todo:", "<span style='background: black; color: #FFFF00;'>TODO:</span>", $description);

    $Page['contents'] .= "<td $editCode>$description</td>";
    $Page['contents'] .= "<td $editCode>" . str_replace(",", "<br/>", $row['target']) . "</td>";
    $Page['contents'] .= "<td $editCode>" . formatNumber($row['amount']) . "</td>";
    $Page['contents'] .= "<td $editCode>" . formatNumber($row['running_balance']) . "</td>";
    $Page['contents'] .= "<td $editCode>" . $row['lastmodified'] . "</td>";

    $Page['contents'] .= "</tr>";

    if ($row['amount'] > 0) $income += $row['amount'];
    else $expense += -$row['amount'];
}
$Page['contents'] .= "</table>";

include dirname(__FILE__) . "/templates/responsive.php";
