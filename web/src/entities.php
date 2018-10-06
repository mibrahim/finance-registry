<?php
require_once dirname(__FILE__) . "/inc/conf.php";

$entityRes = query("select * from entity");

$Page['contents'] .= "<table><tr style='background-color:#000;color:#fff;font-weight: bold;'>
<td style='min-width: 100px; text-align: center;'>Name</td>
<td style='min-width: 100px; text-align: center;'>Description</td>
</tr>";
while ($row = fetch_array($entityRes)) {
    $Page['contents'] .= "<tr><td>$row[name]</td><td>$row[description]</td></tr>";
}
$Page['contents'] .= "</table>";

$Page['title'] = "Entities";

include dirname(__FILE__) . "/templates/responsive.php";