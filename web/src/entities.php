<?php
require_once dirname(__FILE__) . "/inc/conf.php";

$todo = filter_input(INPUT_POST, 'todo');

if ($todo == 'addentity') {
    $entityName = filter_input(INPUT_POST, 'entityname');

    query("insert into entity(name) values ('" . pe($entityName) . "')");
}

$Page['contents'] .= "<form method='post'>
Add entity: New entity name: <input type='hidden' name='todo' value='addentity'/>
<input type='text' name='entityname'/> 
<button type=\"submit\" id=\"completed-task\">
    <i class=\"fas fa-calendar-plus\"></i>
</button>
</form><br/>";

$Page['contents'] .= "<table><tr style='background-color:#000;color:#fff;font-weight: bold;'>
<td style='min-width: 100px; text-align: center;'>Name</td>
<td style='min-width: 100px; text-align: center;'>Description</td>
<td style='min-width: 100px; text-align: center;'>Operations</td>
</tr>";

$entityRes = query("select * from entity");
while ($row = fetch_array($entityRes)) {
    $Page['contents'] .= "<tr><td>$row[name]</td><td>$row[description]</td></tr>";
}
$Page['contents'] .= "</table>";

$Page['title'] = "Entities";

include dirname(__FILE__) . "/templates/responsive.php";