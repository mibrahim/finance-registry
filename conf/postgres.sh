#!/bin/bash

echo "alter role md superuser" | psql mddb
echo "GRANT ALL on database mddb to md WITH GRANT OPTION" | psql mddb

echo "GRANT ALL on database mddb to md WITH GRANT OPTION" | psql mddb
