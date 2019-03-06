<?php
$webdir = str_replace("inc/conf.php", "", __FILE__);

error_reporting(E_ALL);
ini_set('display_errors', 1);

$Page = ['contents' => '', 'title' => '', 'sub_title' => '', 'sub_head' => ''];

$db = new SQLite3($webdir.'/.db/mysqlitedb.db', SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);

function query($query, $DIE = TRUE)
{
	global $db;

	$result = $db->query($query);

	if ($result) {
        return $result;
    } else if ($DIE) {
        header('Content-language: en');
        header('Cache-Control: no-cache');

        http_response_code(500);
        echo "<pre>";
        print_r(debug_backtrace());
        die("SQLite ERROR: " . $db->lastErrorMsg() . " SQL is: " . $query);
    } else {
        return false;
    }
}

function query_row($query, $DIE = TRUE)
{
    $res = query($query, $DIE);

    if ($res === FALSE) {
        return FALSE;
    }

    return $res->fetchArray();
}

function se($s)
{
	global $db;
    return $db->escapeString($s);
}

function ses($s)
{
	global $db;
    return se(stripslashes($s));
}

function getvar($varname)
{
    $row = @query_row("select value from variables where name='$varname'", FALSE);
    if ($row === FALSE) {
        return FALSE;
    }
    return $row ['value'];
}

function setvar($varname, $value)
{
    // Check if the variable exists
    if (getvar($varname) === FALSE) {
        query("insert into variables(name,value) values ('$varname','$value');", FALSE);
    } else {
        query("update variables set value='$value' where name='$varname'", FALSE);
    }
}

// Check the db version
$sysversion = "0001";
$dbver = getvar("sysversion");
echo "dbver = $dbver";
if ($dbver === FALSE) {
    $dbver = "0000";
}
echo "dbver = $dbver";

if ($dbver != $sysversion) {
    include "$webdir/inc/upgrade.php";
}
