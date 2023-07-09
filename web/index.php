<?php

require_once dirname(__FILE__) . "/inc/conf.php";
require_once dirname(__FILE__) . "/inc/utils.php";
require_once dirname(__FILE__) . "/inc/processposts.php";

$Page['title'] = "Multidate";
$Page['sub_title'] = "A multidate, multientry accounting system";

include_once "topbar.php";

$count_rows = query_row("select count(1) as count from txns $filter");

$Page['contents'] .= "<div class='pages'>";
$endPage = ($page + 1) * 100;
if ($endPage > $count_rows['count']) $endPage = $count_rows['count'];

$Page['contents'] .= "<br/><br/>$count_rows[count] rows. Displaying " . ($page * 100 + 1) . " to $endPage<br/>";
$pageUrl = "index.php?entityaccount=" . urlencode("$entity:$account") .
    "&start=$start&end=$end&filter=" . urlencode($stringFilter) . "&page=";

$pages = ceil($count_rows['count'] / 100);

$Page['contents'] .= "<a class='btn btn-success' href='$monthBeforeUrl'>" . date("M-y", $monthBeforeStartTimeStamp) . "</a> ";

for ($pageNumber = 0; $pageNumber != $pages; $pageNumber++)
    $Page['contents'] .= "<a class='btn btn-warning' href='$pageUrl$pageNumber'>$pageNumber</a> ";

$Page['contents'] .= "<a class='btn btn-success' href='$monthAfterUrl'>" . date("M-y", $monthAfterStartTimeStamp) . "</a></div>";

$query = "select * from txns $filter order by date desc, ord desc limit 100 offset " . ($page * 100);


$result = query($query);

// Print the headings
$Page['contents'] .= '
<br/>
<table class="table table-hover">
<thead>
    <tr class="thead-dark">
        <th>Operations</th>
        <th>DOM</th>
        <th>#</th>';

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
        <th>Balance</th>';

$Page['contents'] .= '
    </tr>
</thead>
';

$lastDate = "";

$income = 0;
$expense = 0;

while ($row = $result->fetchArray()) {
    $key = $row['key'];

    $monthYear = date("F Y", $row['date']);

    if ($lastDate != $monthYear && $lastDate != "")
        $Page['contents'] .= "
        <tr>
            <td class='bg-primary reduced_padding fixed_font' colspan='50' style='color:#ffffff;font-weight: bold;text-align: center;'>
               <i class=\"fas fa-arrow-up\"></i> $lastDate <i class=\"fas fa-arrow-up\"></i>
            </td>
        </tr>";

    $lastDate = $monthYear;

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
    $Page['contents'] .= "<td $editCode>" . date("d", $row['date']) . "</td>";
    $Page['contents'] .= "<td $editCode>$row[ord]</td>";

    if ($entity == null) $Page['contents'] .= "<td $editCode>" . htmlentities($row['entity']) . '</td>';

    if ($account == null) $Page['contents'] .= "<td $editCode>" . $row['account'] . '</td>';

    $Page['contents'] .= "<td $editCode>" . $row['status'] . "</td>";

    $description = htmlentities($row['description']);
    $description = str_ireplace("todo:", "<span style='background: black; color: #FFFF00;'>TODO:</span>", $description);

    $Page['contents'] .= "<td $editCode>$description</td>";
    $Page['contents'] .= "<td $editCode>" . str_replace(",", "<br/>", $row['target']) . "</td>";
    $Page['contents'] .= "<td $editCode>" . formatNumber($row['amount']) . "</td>";
    $Page['contents'] .= "<td $editCode>" . formatNumber($row['running_balance']) . "</td>";

    $Page['contents'] .= "</tr>";

    if ($row['amount'] > 0) $income += $row['amount'];
    else $expense += -$row['amount'];
}
$Page['contents'] .= "</table>";

$Page['contents'] .= "<b>Income:</b> $" . formatNumber($income) . "<br/>";
$Page['contents'] .= "<b>Expense:</b> $" . formatNumber($expense) . "<br/>";

$Page['contents'] .= "<code>" . htmlentities($query) . "</code><br/>";

include dirname(__FILE__) . "/templates/responsive.php";
