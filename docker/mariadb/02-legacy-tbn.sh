#!/bin/bash
# Loads thietbinongong.com's dump into "legacy_tbn" for `php artisan legacy:import --site=tbn`.
set -e
if [ -f /legacy/tbn.sql ]; then
  mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" <<SQL
CREATE DATABASE IF NOT EXISTS legacy_tbn CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON legacy_tbn.* TO '$MARIADB_USER'@'%';
SQL
  mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" legacy_tbn < /legacy/tbn.sql
fi
