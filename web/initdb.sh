#!/usr/bin/env bash

echo "DROP SCHEMA public CASCADE" | psql -h mdpsql mddb
echo "CREATE SCHEMA public" | psql -h mdpsql mddb