#!/bin/bash
# Loads the old site's dump into a separate "legacy" database so `php artisan legacy:import` can read it.
set -e
if [ -f /legacy/dump.sql ]; then
  mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" <<SQL
CREATE DATABASE IF NOT EXISTS legacy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON legacy.* TO '$MARIADB_USER'@'%';
SQL
  mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" legacy < /legacy/dump.sql
fi
