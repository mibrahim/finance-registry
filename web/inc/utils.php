<?php

function getEntities()
{
    global $db;

    $sql = "select distinct(entity) from txns order by entity";

    $result = $db->query($sql);

    $entities = array();

    while ($row = $result->fetchArray(SQLITE3_ASSOC))
        $entities[] = $row['entity'];

    return $entities;
}

function getAccounts($entity)
{
    global $db;

    if ($entity != null)
        $sql = "select distinct(account) from txns where entity = '".se($entity)."' order by account";
    else
        $sql = "select distinct(account) from txns order by account";

    $result = $db->query($sql);

    $accounts = array();

    while ($row = $result->fetchArray(SQLITE3_ASSOC))
        $accounts[] = $row['account'];

    return $accounts;
}

function getAllStatuses()
{
    global $db;

    $sql = "select distinct(status) from txns order by status";

    $result = $db->query($sql);

    $statuses = array();

    while ($row = $result->fetchArray(SQLITE3_ASSOC))
        $statuses[] = $row['status'];

    return $statuses;
}

function getAllTargets($entity)
{
    global $db;

    if ($entity != null)
        $sql = "select distinct(target) from txns where entity='".se($entity)."' order by target";
    else
        $sql = "select distinct(target) from txns order by target";

    $result = $db->query($sql);

    $targets = array();

    while ($row = $result->fetchArray(SQLITE3_ASSOC))
        $targets[] = $row['target'];

    return $targets;
}

function updateBalances($entity, $account, $date)
{
    // Recompute the whole thing
    // TODO: Optimize using the date
    $result = query("select key, amount from txns where entity='".se($entity)."' and account='".se($account)."' order by date, ord asc");

    $balance = 0;

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $balance += $row['amount'];
        $updateSql = "update txns set running_balance=$balance where key = $row[key];\n";
        query($updateSql);
    }
}