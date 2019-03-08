<?php

require_once dirname(__FILE__) . "/inc/conf.php";
require_once dirname(__FILE__) . "/inc/utils.php";


$todo = filter_input(INPUT_POST, "todo");
if ($todo == 'deletetxn') {
    $key = strtoupper(trim(filter_input(INPUT_POST, "key")));

    query("delete from txns where key=$key");
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


    $date = strtotime($newDate);

    // Find the max ord in that day
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

$entity = filter_input(INPUT_GET, 'entity');
$account = filter_input(INPUT_GET, 'account');
$page = filter_input(INPUT_GET, 'page');

if ($page == null) $page = 0;
$page = ($page + 1) - 1;

$query = "select * from txns ";

if ($entity != null || $account != null) $query .= " where ";

if ($entity != null) {
    $query .= " entity='" . se($entity) . "' ";
    if ($account != null) $query .= " and ";
}

if ($account != null) $query .= " account='" . se($account) . "'";

$query .= " order by date desc, ord desc limit 100 offset " . ($page * 100);

try {
    $result = query($query);
} catch (Exception $e) {
    echo "Error while executing query: <code>$query</code><br>";
    die($e);
}

// Add transaction button

$allEntities = getEntities();
$allEntitiesOptions = implode("</option><option>", $allEntities);

$allAccounts = getAccounts($entity);
$allAccountsOptions = implode("</option><option>", $allAccounts);

$allStatuses = getAllStatuses();
$allStatusOptions = implode("</option><option>", $allStatuses);

$allTargets = getAllTargets($entity);
$allTargetsOptions = implode("</option><option>", $allTargets);

$Page['contents'] .= '
<br/>
<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addNewModal">
    <i class="fas fa-plus-circle"></i>TXN
</button>

<!-- Modal -->
<div class="modal fade" id="addNewModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Add Transaction</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      
      <form method="post">
          <div class="modal-body">
            <input type="hidden" name="todo" value="addtxn"/>

            <b>Entity:</b> <input class="form-control" type="text"
                                list="entityoptions" name="entity" value="' . $entity . '" autocomplete="off">
                                
            <datalist id="entityoptions">
                <option>' . $allEntitiesOptions . '</option>
            </datalist>                    
            
            <b>Account:</b> <input class="form-control" type="text"
                                list="accountsoptions" name="account" value="' . $account . '" autocomplete="off">
            
            <datalist id="accountsoptions">
                <option>' . $allAccountsOptions . '</option>
            </datalist>                    
            
            <b>Status:</b> <input class="form-control" type="text"
                                list="statusesoptions" name="status" autocomplete="off">
            
            <datalist id="statusesoptions">
                <option>' . $allStatusOptions . '</option>
            </datalist>                    
            
            <b>Target:</b> <input class="form-control" type="text"
                                list="targetsoptions" name="target" autocomplete="off">
            
            <datalist id="targetsoptions">
                <option>' . $allTargetsOptions . '</option>
            </datalist>                    
            
            <b>Amount:</b> <input class="form-control" type="text" name="amount">

            <b>Description:</b> <input class="form-control" type="text" name="description">

            <b>Date:</b> <input class="form-control" type="date" name="date">

            <b>Notes:</b> <input class="form-control" type="text" name="notes">

            <b>URL:</b> <input class="form-control" type="text" name="url">
            
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save changes</button>
          </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Edit Transaction</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      
      <form method="post">
          <div class="modal-body">
            <input type="hidden" name="todo" value="updatetxn"/>

            <b>Txn Key:</b> <input id="frmtxnkey" class="form-control" type="text"
                                name="key" value="" autocomplete="off" readonly>

            <b>Entity:</b> <input id="frmentity" class="form-control" type="text"
                                list="entityoptions2" name="entity" value="' . $entity . '" autocomplete="off">
                                
            <datalist id="entityoptions2">
                <option>' . $allEntitiesOptions . '</option>
            </datalist>                    
            
            <b>Account:</b> <input id="frmaccount" class="form-control" type="text"
                                list="accountsoptions2" name="account" value="' . $account . '" autocomplete="off">
            
            <datalist id="accountsoptions2">
                <option>' . $allAccountsOptions . '</option>
            </datalist>                    
            
            <b>Status:</b> <input id="frmstatus" class="form-control" type="text"
                                list="statusesoptions2" name="status" autocomplete="off">
            
            <datalist id="statusesoptions2">
                <option>' . $allStatusOptions . '</option>
            </datalist>                    
            
            <b>Target:</b> <input id="frmtarget" class="form-control" type="text"
                                list="targetsoptions2" name="target" autocomplete="off">
            
            <datalist id="targetsoptions2">
                <option>' . $allTargetsOptions . '</option>
            </datalist>                    
            
            <b>Amount:</b> <input id="frmamount" class="form-control" type="text" name="amount">

            <b>Description:</b> <input id="frmdescription" class="form-control" type="text" name="description">

            <b>Date:</b> <input  id="frmdate" class="form-control" type="date" name="date">

            <b>Ord:</b> <input  id="frmord" class="form-control" type="text" name="ord">

            <b>Notes:</b> <input  id="frmnotes" class="form-control" type="text" name="notes">

            <b>URL:</b> <input  id="frmurl" class="form-control" type="text" name="url">
            
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save changes</button>
          </div>
      </form>
    </div>
  </div>
</div>

';

// Add filters
$Page['contents'] .= "
<form method='get' style='float:right;'>
            <b>Entity:</b> <input type=\"text\"
                                list=\"entityoptions1\" name=\"entity\" value=\"$entity\" autocomplete=\"off\">
                                
            <datalist id=\"entityoptions1\">
                <option>$allEntitiesOptions</option>
            </datalist>                    
            
            <b>Account:</b> <input type=\"text\"
                                list=\"accountsoptions1\" name=\"account\" value=\"$account\" autocomplete=\"off\">
            
            <datalist id=\"accountsoptions1\">
                <option>$allAccountsOptions</option>
            </datalist>
            
            <input class='btn btn-primary' type='submit'>                    
</form>
";

// Print the headings
$Page['contents'] .= '
<br/><br/>
<table class="table table-hover">
<thead>
    <tr class="thead-dark">
        <th>DEL</th>
        <th>Key</th>
        <th>Date</th>
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
            <td class='bg-primary reducedpadding fixedfont' colspan='50' style='color:#ffffff;font-weight: bold;text-align: center;'>
                $lastDate
            </td>
        </tr>";

    $lastDate = $monthYear;

    $editCode = " style='cursor: pointer;' onclick='fill($key)' data-toggle=\"modal\" data-target=\"#editModal\" class='reducedpadding fixedfont'";

    $rowColor = ' class="table-active"';

    if ($row['running_balance'] < 0) $rowColor = ' class="bg-danger"';
    else if ($row['status'] == 'PLANNED') $rowColor = ' class="table-danger"';
    else if ($row['status'] == 'RECONCILED') $rowColor = ' class="table-success"';
    else if ($row['status'] == 'PAID') $rowColor = ' class="table-warning"';

    $Page['contents'] .= "<tr $rowColor>";
    $Page['contents'] .= "
            <td class='reducedpadding fixedfont'>
                <form method='post'>
                    <input type='hidden' name='todo' value='deletetxn'>
                    <input type='hidden' name='key' value='$key'>
                    <button type='submit' onclick=\"return confirm('Are you sure?')\"><i class=\"fas fa-trash-alt\"></i></button>
                </form>
            </td>";
    $Page['contents'] .= "<td $editCode>" . $row['key'] . "</td>";
    $Page['contents'] .= "<td $editCode>" . date("y-m-d", $row['date']) . "</td>";
    $Page['contents'] .= "<td $editCode>" . $row['ord'] . "</td>";

    if ($entity == null) $Page['contents'] .= "<td $editCode>" . htmlentities($row['entity']) . '</td>';

    if ($account == null) $Page['contents'] .= "<td $editCode>" . $row['account'] . '</td>';

    $Page['contents'] .= "<td $editCode>" . $row['status'] . "</td>";
    $Page['contents'] .= "<td $editCode>" . $row['description'] . "</td>";
    $Page['contents'] .= "<td $editCode>" . $row['target'] . "</td>";
    $Page['contents'] .= "<td $editCode>" . formatNumber($row['amount']) . "</td>";
    $Page['contents'] .= "<td $editCode>" . formatNumber($row['running_balance']) . "</td>";

    $Page['contents'] .= "</tr>";
}
$Page['contents'] .= "</table>";

$Page['contents'] .= "<code>$query</code><br/>";

include dirname(__FILE__) . "/templates/responsive.php";
