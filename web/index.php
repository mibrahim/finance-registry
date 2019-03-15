<?php

require_once dirname(__FILE__) . "/inc/conf.php";
require_once dirname(__FILE__) . "/inc/utils.php";


$todo = filter_input(INPUT_POST, "todo");
if ($todo == 'deletetxn') {
    $key = strtoupper(trim(filter_input(INPUT_POST, "key")));

    $row = query_row("select * from txns where key=$key");
    query("delete from txns where key=$key");
    updateBalances($row['entity'], $row['account'], $row['date']);
}

if ($todo == 'addtxn') {
    $newEntity = strtoupper(trim(filter_input(INPUT_POST, "entity")));
    $newAccount = strtoupper(trim(filter_input(INPUT_POST, "account")));
    $newStatus = strtoupper(trim(filter_input(INPUT_POST, "status")));
    $newTarget = strtoupper(trim(filter_input(INPUT_POST, "target")));
    $newDescription = (trim(filter_input(INPUT_POST, "description")));
    $newAmount = trim(filter_input(INPUT_POST, "amount"));
    $newDate = trim(filter_input(INPUT_POST, "date"));
    $newNotes = trim(filter_input(INPUT_POST, "notes"));
    $newURL = trim(filter_input(INPUT_POST, "url"));

    $newAmount = str_replace(",", "", $newAmount);

    $numberOfTimes = trim(filter_input(INPUT_POST, "numberoftimes"));
    $everyMonths = trim(filter_input(INPUT_POST, "everymonths"));
    $everyWeeks = trim(filter_input(INPUT_POST, "everyweeks"));

    $date = strtotime($newDate);

    // Find the max ord in that day
    for ($reps = 0; $reps != $numberOfTimes; $reps++) {
        $row = query_row("select max(ord) as ord from txns where date=$date");

        if ($row == false) $max = 0;
        else $max = $row['ord'];

        $max = $max + 1;

        $sql = "insert into txns(entity, account, status, target, description, amount, date, ord, notes, url) values (" .
            "'" . se($newEntity) . "'," .
            "'" . se($newAccount) . "'," .
            "'" . se($newStatus) . "'," .
            "'" . se($newTarget) . "'," .
            "'" . se($newDescription) . "'," .
            "$newAmount," .
            "$date," .
            "$max," .
            "'" . se($newNotes) . "'," .
            "'" . se($newURL) . "'" .
            ")";

        query($sql);
        updateBalances($newEntity, $newAccount, $date);

        // Update date
        $date = strtotime("+$everyMonths months", strtotime("+$everyWeeks weeks", $date));
    }
}

if ($todo == 'updatetxn') {
    $newKey = strtoupper(trim(filter_input(INPUT_POST, "key")));
    $newEntity = strtoupper(trim(filter_input(INPUT_POST, "entity")));
    $newAccount = strtoupper(trim(filter_input(INPUT_POST, "account")));
    $newStatus = strtoupper(trim(filter_input(INPUT_POST, "status")));
    $newTarget = strtoupper(trim(filter_input(INPUT_POST, "target")));
    $newDescription = (trim(filter_input(INPUT_POST, "description")));
    $newAmount = trim(filter_input(INPUT_POST, "amount"));
    $newDate = trim(filter_input(INPUT_POST, "date"));
    $newOrd = trim(filter_input(INPUT_POST, "ord"));
    $newNotes = trim(filter_input(INPUT_POST, "notes"));
    $newURL = trim(filter_input(INPUT_POST, "url"));

    $newAmount = str_replace(",", "", $newAmount);

    $date = strtotime($newDate);

    $sql = "update txns set " .
        "entity = '" . se($newEntity) . "'," .
        "account = '" . se($newAccount) . "'," .
        "status = '" . se($newStatus) . "'," .
        "target = '" . se($newTarget) . "'," .
        "description = '" . se($newDescription) . "'," .
        "amount = $newAmount," .
        "date = $date," .
        "ord = $newOrd," .
        "notes = '" . se($newNotes) . "'," .
        "url = '" . se($newURL) . "'" .
        " where key = $newKey";

    query($sql);

    updateBalances($newEntity, $newAccount, $date);
}

$Page['title'] = "Multidate";
$Page['sub_title'] = "A multidate, multientry accounting system";

include_once "topbar.php";

if ($page == null) $page = 0;
$page = ($page + 1) - 1;

$filter = "";

if ($entity != null) {
    $filter .= " entity='" . se($entity) . "' ";
}

if ($account != null) {
    if ($filter != "") $filter .= " and ";
    $filter .= " account='" . se($account) . "'";
}

if ($filter != "") $filter .= " and ";
$filter .= " date>=$startDate and date<=$endDate";

if (strlen($stringFilter) > 0) {
    if ($filter != "") $filter .= " and ";
    $filter .= " (description like '%" . se($stringFilter) . "%' COLLATE NOCASE or " .
        "target like '%" . se($stringFilter) . "%' COLLATE NOCASE) ";
}

if ($filter != "") $filter = " where $filter ";

$count_rows = query_row("select count(1) as count from txns $filter");

$Page['contents'] .= "<div class='pages'>";
$endPage = ($page +1)*100;
if ($endPage>$count_rows['count']) $endPage=$count_rows['count'];

$Page['contents'] .= "<br/><br/>$count_rows[count] rows. Displaying " . ($page * 100 + 1) . " to $endPage<br/>";
$pageUrl = "index.php?entity=" . urlencode($entity) . "&account=" . urlencode($account) .
    "&start=$start&end=$end&filter=" . urlencode($stringFilter) . "&page=";

$pages = ceil($count_rows['count'] / 100);

for ($pageNumber = 0; $pageNumber != $pages; $pageNumber++)
    $Page['contents'] .= "<a class='btn btn-warning' href='$pageUrl$pageNumber'>$pageNumber</a> ";
$Page['contents'] .= "</div>";

$query = "select * from txns $filter order by date desc, ord desc limit 100 offset " . ($page * 100);


$result = query($query);

// Print the headings
$Page['contents'] .= '
<br/>
<table class="table table-hover">
<thead>
    <tr class="thead-dark">
        <th>Operations</th>
        <th>Key</th>
        <th>DOM</th>
        <th>ORDER</th>';

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
    $Page['contents'] .= "<td $editCode>" . $row['key'] . "</td>";
    $Page['contents'] .= "<td $editCode>" . date("d", $row['date']) . "</td>";
    $Page['contents'] .= "<td $editCode>" . $row['ord'] . "</td>";

    if ($entity == null) $Page['contents'] .= "<td $editCode>" . htmlentities($row['entity']) . '</td>';

    if ($account == null) $Page['contents'] .= "<td $editCode>" . $row['account'] . '</td>';

    $Page['contents'] .= "<td $editCode>" . $row['status'] . "</td>";
    $Page['contents'] .= "<td $editCode>" . $row['description'] . "</td>";
    $Page['contents'] .= "<td $editCode>" . str_replace(",","<br/>",$row['target']) . "</td>";
    $Page['contents'] .= "<td $editCode>" . formatNumber($row['amount']) . "</td>";
    $Page['contents'] .= "<td $editCode>" . formatNumber($row['running_balance']) . "</td>";

    $Page['contents'] .= "</tr>";
}
$Page['contents'] .= "</table>";

$Page['contents'] .= "<code>".htmlentities($query)."</code><br/>";

include dirname(__FILE__) . "/templates/responsive.php";
