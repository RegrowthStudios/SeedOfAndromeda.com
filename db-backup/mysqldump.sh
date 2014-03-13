mysqldump -u root -ptest --all-databases | gzip > "../db-backup/backup/backup-$(date).sql.gz" 2> ../db-backup/dump.log

 echo "Finished mysqldump $(date)" >> ../db-backup/dump.log