<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$webdir = str_replace("inc/conf.php", "", __FILE__);

error_reporting(E_ALL);
ini_set('display_errors', 1);

$Page = ['contents' => '', 'title' => '', 'sub_title' => '', 'debug' => ''];

$dbPath = $webdir . '.db/mysqlitedb.db';

try {
    $db = new SQLite3($dbPath, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
} catch (Exception $e) {
    echo "DB Path is: $dbPath<br/>";
    die($e);
}

$queries = 0;
$debug = filter_input(INPUT_GET, 'debug');
$totalQueryTime = 0;
$sqlLog = "";
function query($query, $DIE = TRUE)
{
    global $db, $queries, $debug, $totalQueryTime, $sqlLog;

    $queries++;

    $totalTime = 0;

    if ($debug == '1') $totalTime = -microtime(true);

    $sqlLog .= $query . "<br/>";
    $result = $db->query($query);

    if ($debug == '1') {
        $totalTime = $totalTime + microtime(true);
        global $Page;
        $Page['debug'] .= "<div><b>$queries - Time:$totalTime</b><br/><code>$query</code></div>";
        $totalQueryTime += $totalTime;
    }

    if ($result) {
        return $result;
    } else if ($DIE) {
        header('Content-language: en');
        header('Cache-Control: no-cache');

        http_response_code(500);
        echo "<pre>";
        print_r(debug_backtrace());
        die("SQLite ERROR: " . $db->lastErrorMsg() . " SQL is: " . $query."<br/>".$sqlLog);
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

    return $res->fetchArray(SQLITE3_ASSOC);
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
    return $row['value'];
}

function setvar($varname, $value)
{
    // Check if the variable exists
    if (getVar($varname) === FALSE) {
        query("insert into variables(name,value) values ('$varname','$value');", FALSE);
    } else {
        query("update variables set value='$value' where name='$varname'", FALSE);
    }
}

// Check the db version
$sysversion = "0004";
$dbver = getVar("sysversion");
if ($dbver === FALSE) {
    $dbver = "0000";
}

if ($dbver != $sysversion) {
    include "$webdir/inc/upgrade.php";
}
