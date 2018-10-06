<?php

class Query
{

    function __construct()
    {

    }

    function query($sql)
    {
        return pg_query($sql);
    }

    function fetchArray($res)
    {
        return pg_fetch_array($res);
    }

}
