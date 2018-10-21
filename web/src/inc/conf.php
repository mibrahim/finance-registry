<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$Page = ['contents' => '', 'title' => '', 'sub_title' => '', 'sub_head' => ''];

$dbuser = "md";
$dbhost = "mdpsql";
$dbpass = "psql1234";
$host = "mdpsql";
$db = "mddb";
$port = "5432";

$dbh = pg_connect("host=$host port=$port dbname=$db user=$dbuser password=$dbpass");

global $Query;

if (!isset($Query)) {
    include "Query.php";
    $Query = new Query();
}

function query($query, $DIE = TRUE)
{
    global $Query;

    if ($DIE == FALSE) {
        $result = $Query->query($query);
    } else {
        $result = $Query->query($query);
    }

    if ($result) {
        return $result;
    } else if ($DIE) {
        header('Content-language: en');
        header('Cache-Control: no-cache');

        http_response_code(500);
        echo "<pre>";
        print_r(debug_backtrace());
        die("PgSQL ERROR: " . pg_last_error() . " SQL is: " . $query);
    } else {
        return false;
    }
}

function fetch_array($res)
{
    global $Query;

    $result = $Query->fetchArray($res);

    return $result;
}

function query_row($query, $DIE = TRUE)
{
    $res = query($query, $DIE);

    if (!$res) {
        return FALSE;
    }

    return fetch_array($res);
}

function pe($s)
{
    return pg_escape_string($s);
}

function pes($s)
{
    return pg_escape_string(stripslashes($s));
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

// Generic global variables
$webdir = str_replace("inc/conf.php", "", __FILE__);

// Check the db version
$sysversion = "0001";
$dbver = getvar("sysversion");
if ($dbver === FALSE) {
    $dbver = "0000";
}

if ($dbver != $sysversion) {
    include "$webdir/inc/upgrade.php";
}
