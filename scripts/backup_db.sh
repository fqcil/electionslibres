#!bash
mysqldump -u root --add-locks --disable-keys --no-autocommit pointage |gzip >pointage.sql.gz
