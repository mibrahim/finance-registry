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

$query = "select target, sum(amount) as sum from txns $filter group by target order by target";

$result = query($query);

$Page['contents'] .= '
<br/><br/>
<center>
<table class="table table-hover" style="width:max-content;">
<thead>
    <tr class="thead-dark">
        <th>Target Account</th>
        <th>Total</th>
    </tr>
</thead>
';

$sum = array();

function chargeAccount($accountName, $amount, &$sumArray)
{
    $parts = explode(":", $accountName);
    $connected = "";
    for ($i = 0; $i != sizeof($parts); $i++) {
        if (strlen($connected) > 0) $connected .= ":";
        $connected .= trim($parts[$i]);
        if (!isset($sumArray[$connected])) $sumArray[$connected] = $amount;
        else $sumArray[$connected] += $amount;
    }
}

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $accountsString = $row['target'];

    $accounts = explode(",", $accountsString);

    foreach ($accounts as $key => $value) {
        if (strstr($value, "=")) {
            $sections = explode("=", $value);
            chargeAccount($sections[0], $sections[1], $sum);
        } else
            chargeAccount($value, $row['sum'], $sum);
    }
}

ksort($sum);

foreach ($sum as $key => $value) {
    $spaces = "";
    for ($i = 0; $i != substr_count($key, ":"); $i++) $spaces .= "&nbsp;&nbsp;&nbsp;&nbsp;";
    $splitKey = explode(":", $key);
    $keyName = $splitKey[sizeof($splitKey) - 1];
    $Page['contents'] .= "<tr><td><code>$spaces $keyName</code></td><td><code>" . formatNumber($value) . "</code></td></tr>";
}

$Page['contents'] .= "</table></center>";

$query = "select a.date, a.running_balance from txns a, (select date, max(ord) as maxord from txns $filter group by date) b where " .
    "a.date=b.date and a.ord=b.maxord";

$labels = "";
$data = "";

$result = query($query);

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    if (strlen($labels) > 0) $labels .= " , ";
    $labels .= '"' . date("m/d", $row['date']) . '"';

    if (strlen($data) > 0) $data .= " , ";
    $data .= number_format($row['running_balance'], 2, ".", "");
}

$Page['contents'] .= '
<div style="width:700px;height:400px;margin-left: auto;margin-right: auto;">
<canvas id="myChart"></canvas>
</div>

<script>
var ctx = document.getElementById("myChart");
var myChart = new Chart(ctx, {
    type: "line",
    data: {
        labels: [' . $labels . '],
        datasets: [{
            label: "Balance",
            data: [' . $data . '],
            borderWidth: 1
        }]
    },
});
</script>
';

include dirname(__FILE__) . "/templates/responsive.php";
