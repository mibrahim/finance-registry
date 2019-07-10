<?php
require_once dirname(__FILE__) . "/inc/conf.php";
require_once dirname(__FILE__) . "/inc/utils.php";

include "topbar.php";

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

$query = "select * from txns $filter";

$result = query($query);

$data = [];

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $monthYear = date("M/Y", $row['date']);

    if (!isset($data[$monthYear]['assets'])) $data[$monthYear]['assets'] = 0;
    if (!isset($data[$monthYear]['liabilities'])) $data[$monthYear]['liabilities'] = 0;

    if ($row['amount'] >= 0) $data[$monthYear]['assets'] += $row['amount'];
    if ($row['amount'] < 0) $data[$monthYear]['liabilities'] -= $row['amount'];
}

$Page['contents'] .= '
<br/><br/>
';

foreach ($data as $key => $value) {
    $startDate = str_replace('/', '-01-', $key);
    $monthStart = strtotime($startDate);
    $openingBalance = findMonthOpeningBalance($entity, $account, $monthStart);
    $Page['contents'] .= "
<b>Date: $key</b><br/>
<b>Assets: " . ($value['assets'] + $openingBalance) . "</b><br/>
<b>Liabilities: $value[liabilities]</b><br/>
<br/>
";
}

include dirname(__FILE__) . "/templates/responsive.php";
