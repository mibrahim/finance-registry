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

$Page['contents'] .= '
<br/>
<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addNewModal">
    <i class="fas fa-plus-circle"></i> TXN
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
$Page['contents'] .= '
<a class="btn btn-primary" href="index.php">
    <i class="fas fa-file-invoice-dollar"></i> Register
</a>
<a class="btn btn-primary" href="p_and_l.php">
    <i class="fas fa-file-invoice-dollar"></i> P&L report
</a>
';

// Add filters
$Page['contents'] .= "
<form method='get' style='float:right;'>
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
";
