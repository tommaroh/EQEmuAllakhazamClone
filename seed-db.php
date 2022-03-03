<?php
/**
 * Created by PhpStorm.
 * User: cmiles
 * Date: 6/9/18
 * Time: 9:46 PM
 */

$origin_directory = getcwd();
$tmp_dir = "/tmp/db_source";
$tmp_path = "$tmp_dir/peq-db.zip";

/**
 * Download file
 */
echo "Downloading PEQ DB...\n";
if (!file_exists($tmp_dir)) {
    mkdir($tmp_dir);
}
$peq_dump = file_get_contents('http://db.projecteq.net/latest', $tmp_path);


/**
 * Source database
 */
echo "Installing mysql-client...\n";
exec("apt-get update && apt-get -y install unzip mysql-client");

$result=exec("ls $tmp_dir/peq-dump");
echo "$result\n";

echo "Creating database PEQ...\n";
exec('mysql -h mariadb -uroot -proot -e "CREATE DATABASE peq"  2>&1 | grep -v \'Warning\'');

echo "Sourcing data...\n";
chdir("$tmp_dir/peq-dump");
exec("mysql -h mariadb -uroot -proot peq < create_all_tables.sql  2>&1 | grep -v 'Warning'");
chdir($origin_directory);
echo "Seeding complete!\n";

/**
 * Unlink
 */
array_map('unlink', glob($tmp_dir . "*.*"));
rmdir($tmp_dir);