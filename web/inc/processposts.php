<?php

beginTransaction();

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

        $sql = "insert into txns(entity, account, status, target, description, amount, date, ord, notes, url, lastmodified) values (" .
            "'" . se($newEntity) . "'," .
            "'" . se($newAccount) . "'," .
            "'" . se($newStatus) . "'," .
            "'" . se($newTarget) . "'," .
            "'" . se($newDescription) . "'," .
            "$newAmount," .
            "$date," .
            "$max," .
            "'" . se($newNotes) . "'," .
            "'" . se($newURL) . "'," .
            "'" . time() . "'" .
            ")";

        query($sql);
        updateBalances($newEntity, $newAccount, $date);

        // Update date
        $date = strtotime("+$everyMonths months", strtotime("+$everyWeeks weeks", $date));
        renumber($newEntity, $newAccount, $date);
    }
}

if ($todo == 'updatetxn') {
    $newKey = strtoupper(trim(filter_input(INPUT_POST, "key")));
    $oldRow = query_row("select * from txns where key=$newKey limit 1");
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

    $recalcDate = $oldRow['date'];
    if ($date<$recalcDate) $recalcDate = $date;

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
        "lastmodified = " . time() . "," .
        "url = '" . se($newURL) . "'" .
        " where key = $newKey";

    query($sql);

    updateBalances($newEntity, $newAccount, $recalcDate);
    renumber($newEntity, $newAccount, $date);
    renumber($newEntity, $newAccount, $oldRow['date']);
}

commitTransaction();
