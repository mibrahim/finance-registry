<?php

function getEntities()
{
    $sql = "select distinct(entity) from txns order by entity";

    $result = query($sql);

    $entities = array();

    while ($row = $result->fetchArray(SQLITE3_ASSOC))
        $entities[] = $row['entity'];

    return $entities;
}

function getAccounts($entity)
{
    if ($entity != null)
        $sql = "select distinct(account) from txns where entity = '" . se($entity) . "' order by account";
    else
        $sql = "select distinct(account) from txns order by account";

    $result = query($sql);

    $accounts = array();

    while ($row = $result->fetchArray(SQLITE3_ASSOC))
        $accounts[] = $row['account'];

    return $accounts;
}

function getAllStatuses()
{
    $sql = "select distinct(status) from txns order by status";

    $result = query($sql);

    $statuses = array();

    while ($row = $result->fetchArray(SQLITE3_ASSOC))
        $statuses[] = $row['status'];

    return $statuses;
}

function getAllTargets($entity)
{
    if ($entity != null)
        $sql = "select distinct(target) from txns where entity='" . se($entity) . "' order by target";
    else
        $sql = "select distinct(target) from txns order by target";

    $result = query($sql);

    $targets = array();

    while ($row = $result->fetchArray(SQLITE3_ASSOC))
        $targets[] = $row['target'];

    return $targets;
}

function updateBalances($entity, $account, $date)
{
    // Recompute the whole thing
    // TODO: Optimize using the date
    $result = query("select key, amount, running_balance from txns where entity='" . se($entity)
        . "' and account='" . se($account) . "' order by date, ord asc");

    $balance = 0;

    query("begin transaction");
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $balance += $row['amount'];

        if ($balance != $row['running_balance']) {
            $updateSql = "update txns set running_balance=$balance where key = $row[key];\n";
            query($updateSql);
        }
    }
    query("commit");
}

function renumber($entity, $account, $date)
{
    // Recompute the whole thing
    // TODO: Optimize using the date
    $result = query("select key from txns where entity='" . se($entity)
        . "' and account='" . se($account) . "' and date=$date order by ord asc");

    $keys = [];
    while ($row = $result->fetchArray()) $keys[] = $row['key'];

    query("begin transaction");
    $ord = 10;
    foreach ($keys as $key) {
        $updateSql = "update txns set ord=$ord where key = $key";
        query($updateSql);
        $ord += 10;
    }
    query("commit");
}

function formatNumber($number)
{
    $string = sprintf("% 10s", number_format($number, 2, ".", ","));

    return str_replace(" ", "&nbsp;", $string);
}

function findMonthOpeningBalance($entity, $account, $date)
{
    // Find the first second of the month
    $monthStartDate = strtotime(date("M-01-Y", $date));

    // Find the last transaction in that account before that time

    // Recompute the whole thing
    // TODO: Optimize using the date
    $result = query_row("select running_balance from txns where entity='" . se($entity)
        . "' and account='" . se($account) . "' and date < $monthStartDate order by date desc, ord desc limit 1");

    return $result['running_balance'];
}
