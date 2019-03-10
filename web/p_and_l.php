<?php
require_once dirname(__FILE__) . "/inc/conf.php";
require_once dirname(__FILE__) . "/inc/utils.php";

include "topbar.php";

$filter = "";

if ($entity != null) {
    $filter .= "entity = '" . se($entity) . "' ";
}

if ($account != null) {
    if (strlen($filter)) $filter .= " and ";
    $filter .= "account = '" . se($account) . "' ";
}

if (strlen($filter)) $filter = " where $filter ";

$result = query("select target, sum(amount) as sum from txns $filter group by target order by target");

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

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $parts = explode(":", $row['target']);
    $connected = "";
    for ($i = 0; $i != sizeof($parts); $i++) {
        if (strlen($connected) > 0) $connected .= ":";
        $connected .= $parts[$i];
        if (!isset($sum[$connected])) $sum[$connected] = $row['sum'];
        else $sum[$connected] += $row['sum'];
    }
}

foreach ($sum as $key => $value) {
    $spaces = "";
    for($i=0;$i!=substr_count($key,":");$i++) $spaces.="&nbsp;&nbsp;&nbsp;&nbsp;";
    $Page['contents'] .= "<tr><td><code>$spaces $key</code></td><td><code>" . formatNumber($value) . "</code></td></tr>";
}

$Page['contents'] .= "</table></center>";

include dirname(__FILE__) . "/templates/responsive.php";
