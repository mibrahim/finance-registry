<?php

$entity = filter_input(INPUT_GET, 'entity');
$account = filter_input(INPUT_GET, 'account');
$page = filter_input(INPUT_GET, 'page');
$start = filter_input(INPUT_GET, 'start');
$startDate = 0;
if ($start != null) $startDate = strtotime($start);
$end = filter_input(INPUT_GET, 'end');
$endDate = 9e9;
if ($end != null) $endDate = strtotime($end);
$stringFilter = filter_input(INPUT_GET, 'filter');


$start = date("Y-m-d", $startDate);
$end = date("Y-m-d", $endDate);

// Add transaction button

$allEntities = getEntities();
$allEntitiesOptions = implode("</option><option>", $allEntities);

$allAccounts = getAccounts($entity);
$allAccountsOptions = implode("</option><option>", $allAccounts);

$allStatuses = getAllStatuses();
$allStatusOptions = implode("</option><option>", $allStatuses);

$allTargets = getAllTargets($entity);
$allTargetsOptions = implode("</option><option>", $allTargets);

$urlSuffix = "?entity=" . urlencode($entity) . "&account=" . urlencode($account) .
  "&start=$start&end=$end&filter=" . urlencode($stringFilter);

if ($page == null) $page = 0;
$page = ($page + 1) - 1;

$filter = "";
$datesFilter = "";

if ($entity != null) {
  $filter .= " entity='" . se($entity) . "' ";
  $datesFilter .= " entity='" . se($entity) . "' ";
}

if ($account != null) {
  if ($filter != "") {
    $filter .= " and ";
    $datesFilter .= " and ";
  }

  $filter .= " account='" . se($account) . "'";
  $datesFilter .= " account='" . se($account) . "'";
}

if ($filter != "") $filter .= " and ";
$filter .= " date>=$startDate and date<=$endDate";

if (strlen($stringFilter) > 0) {
  if ($filter != "") $filter .= " and ";
  $filter .= " (description like '%" . se($stringFilter) . "%' COLLATE NOCASE or " .
    "target like '%" . se($stringFilter) . "%' COLLATE NOCASE) ";
}

if ($filter != "") $filter = " where $filter ";
if ($datesFilter != "") $datesFilter = " where $datesFilter ";

// Find min and max dates
$minMaxDatesRow = query_row("select min(date) as mindate, max(date) as maxdate from txns $datesFilter");

// Compile quick buttons
$date = $minMaxDatesRow['mindate'];
$url = "?entity=" . urlencode($entity) . "&account=" . urlencode($account);
$buttons = "<table style='font-family:Monospace;'><tr><td colspan='5' style='text-align: center;'><a href='$url'>Reset</a></td></tr>";
$counter = 0;

$monthBeforeStartDate = date("M-01-Y", strtotime("-1 month", $startDate));
$monthBeforeStartTimeStamp = strtotime($monthBeforeStartDate);
$monthBeforeEndDate = date("M-d-Y", strtotime("-1 day", strtotime("+1 month", $monthBeforeStartTimeStamp)));
$monthBeforeEndTimeStamp = strtotime($monthBeforeEndDate);

$monthBeforeUrl = "?entity=" . urlencode($entity) . "&account=" . urlencode($account) .
  "&start=$monthBeforeStartDate&end=$monthBeforeEndDate&filter=" . urlencode($stringFilter);

$monthAfterStartDate = date("M-01-Y", strtotime("+1 month", $startDate));
$monthAfterStartTimeStamp = strtotime($monthAfterStartDate);
$monthAfterEndDate = date("M-d-Y", strtotime("-1 day", strtotime("+1 month", $monthAfterStartTimeStamp)));
$monthAfterEndTimeStamp = strtotime($monthAfterEndDate);

$monthAfterUrl = "?entity=" . urlencode($entity) . "&account=" . urlencode($account) .
  "&start=$monthAfterStartDate&end=$monthAfterEndDate&filter=" . urlencode($stringFilter);

$currentStartMonth = date("M-01-Y", $startDate);
$currentEndMonth = date("M-01-Y", $endDate);

$currentMonthStart = date("M-01-Y", time(0));
$currentMonthEnd = date("M-d-Y", strtotime(
  "-1 day",
  strtotime(
    "+1 month",
    strtotime($currentMonthStart)
  )
));

while ($date <= $minMaxDatesRow['maxdate']) {
  $currentDateStart = date("M-01-Y", $date);
  $monthStartTimeStamp = strtotime($currentDateStart);
  $currentDateEnd = date("M-d-Y", strtotime("-1 day", strtotime("+1 month", $monthStartTimeStamp)));
  $monthEndTimeStamp = strtotime($currentDateStart);
  $date = strtotime("+1 month", $monthStartTimeStamp);

  $style = "";
  if ($currentDateStart == $currentStartMonth) {
    $style = "style='background-color: #DDD;text-align: center;'";
  }

  $url = "?entity=" . urlencode($entity) . "&account=" . urlencode($account) .
    "&start=$currentDateStart&end=$currentDateEnd&filter=" . urlencode($stringFilter);

  $text = date("My", $monthStartTimeStamp);
  $buttons .= "<td $style style='text-align: center;'><a href='$url'>$text</a></td>";

  if ($counter++ == 4) {
    $counter = 0;
    $buttons .= "</tr><tr>";
  }
}

$buttons .= "</tr></table>";

//
// http://localhost:8123/?entity=&account=&start=Jan-01-2018&end=Jan-31-2018&filter=
//
$currentMonthUrl = "?entity=$entity&account=$account&start=$currentMonthStart&end=$currentMonthEnd&filter=";

if (isset($_POST['todo']) && $_POST['todo'] == 'addnewtodo') {
  query("insert into todo(title) values ('" . se($_POST['todoitem']) . "')");
}

// Query the todo lists
$todoList = "";
$res = query("select * from todo where status != 'resolved' or status is null");

while ($row = $res->fetchArray()) {
  $todoList .= "<a href=''>
  <i class='fas fa-square'></i>
  </a>  $row[title] <br/>";
}

$todoList.="<hr/>";

$res = query("select * from todo where status is not null");

while ($row = $res->fetchArray()) {
  $todoList .= "<a href=''>
  <i class='fas fa-check-square'></i>
  </a>  $row[title] <br/>";
}

$Page['contents'] .= '
<div id="top_bar">
<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addNewModal">
    <i class="fas fa-plus-circle"></i> TXN
</button>

<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#fastFilter">
    <i class="fas fa-bolt"></i>
</button>

<a href="' . $currentMonthUrl . '" class="btn btn-primary" title="Current month">
    <i class="far fa-clock"></i>
</a>

<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#todolist">
    <i class="far fa-list-alt"></i>
</button>

<a class="btn btn-primary" href="index.php">
    <i class="fas fa-file-invoice-dollar"></i> Register
</a>
<a class="btn btn-primary" href="p_and_l.php' . $urlSuffix . '">
    <i class="fas fa-file-invoice-dollar"></i> P&L
</a>
<a class="btn btn-primary" href="balance_sheet.php' . $urlSuffix . '">
    <i class="fas fa-file-invoice-dollar"></i> BSheet
</a>
';

// Add filters
$Page['contents'] .= "
<form method='get' class='top_bar_form' style='float:right;'>
            <b>Entity:</b> <input type='text' list='entityoptions1' name='entity' value='$entity' autocomplete='off'>
                                
            <datalist id='entityoptions1'>
                <option>$allEntitiesOptions</option>
            </datalist>                    
            
            <b>Account:</b> 
            <input type='text' list='accountsoptions1' name='account' value='$account' autocomplete='off'>
            
            <datalist id='accountsoptions1'>
                <option>$allAccountsOptions</option>
            </datalist>

            <b>Start:</b> 
            <input type='date' name='start' value='$start' autocomplete='off'>
            
            <b>End:</b> 
            <input type='date' name='end' value='$end' autocomplete='off'>
            
            <b>Filter:</b> 
            <input type='text' name='filter' value='" . htmlentities($stringFilter) . "' autocomplete='off'>
            
            <input class='btn btn-primary' type='submit'>                    
</form>
</div>
";

$Page['contents'] .= '
<!-- Modal -->
<div class="modal fade" id="fastFilter" tabindex="-1" role="dialog" aria-labelledby="fastFilter" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Quick filters</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
     ' . $buttons . '
    </div>
  </div>
</div>


<div class="modal fade" id="todolist" tabindex="-1" role="dialog" aria-labelledby="todolist" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><i class="far fa-list-alt"></i> TODO List</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <form method="post">
          <input type="text" class="form-control" name="todoitem"/>
          <input type="hidden" name="todo" value="addnewtodo"/>
          <input type="submit" class="btn btn-warning" value="Add New Item"/>
        </form>
        <hr/>
      ' . $todoList . '
      </div>
    </div>
  </div>
</div>

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

            <b>Entity:</b> <input id="newentity" class="form-control" type="text"
                                list="entityoptions" name="entity" value="' . $entity . '" autocomplete="off">
                                
            <datalist id="entityoptions">
                <option>' . $allEntitiesOptions . '</option>
            </datalist>                    
            
            <b>Account:</b> <input id="newaccount" class="form-control" type="text"
                                list="accountsoptions" name="account" value="' . $account . '" autocomplete="off">
            
            <datalist id="accountsoptions">
                <option>' . $allAccountsOptions . '</option>
            </datalist>                    
            
            <b>Status:</b> <input id="newstatus" class="form-control" type="text"
                                list="statusesoptions" name="status" autocomplete="off">
            
            <datalist id="statusesoptions">
                <option>' . $allStatusOptions . '</option>
            </datalist>                    
            
            <b>Target:</b> <input id="newtarget" class="form-control" type="text"
                                list="targetsoptions" name="target" autocomplete="off">
            
            <datalist id="targetsoptions">
                <option>' . $allTargetsOptions . '</option>
            </datalist>                    
            
            <b>Amount:</b> <input id="newamount" class="form-control" type="text" name="amount">

            <b>Description:</b> <input id="newdescription" class="form-control" type="text" name="description">

            <b>Date:</b> <input id="newdate" class="form-control" type="date" name="date">

            <b>Notes:</b> <input id="newnotes" class="form-control" type="text" name="notes">

            <b>URL:</b> <input id="newurl" class="form-control" type="text" name="url">
            
            <h4>Repeat:</h4>
            <b>Number of times:</b>
             <input id="numberoftimes" class="form-control" type="number" value="1" name="numberoftimes">

            <b>Every how many weeks:</b>
             <input id="everyweeks" class="form-control" type="number"  value="0" name="everyweeks">

            <b>Every how many months:</b>
             <input id="everymonths" class="form-control" type="number"  value="0" name="everymonths">

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
