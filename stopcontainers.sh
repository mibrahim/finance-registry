#!/usr/bin/env bash
docker network create pg_net

echo Stopping
docker stop application
docker stop postgresql
