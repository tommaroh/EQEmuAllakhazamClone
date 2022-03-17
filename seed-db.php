<?php

function errHandle($errNo, $errStr, $errFile, $errLine) {
    $msg = "$errStr in $errFile on line $errLine";
    if ($errNo == E_NOTICE || $errNo == E_WARNING) {
        throw new ErrorException($msg, $errNo);
    } else {
        echo $msg;
    }
}

set_error_handler('errHandle');

$origin_directory = getcwd();
$tmp_dir = "/tmp/db_source";
$tmp_path = "$tmp_dir/peq-db.zip";

echo "Downloading PEQ DB...\n";
exec("rm -rf $tmp_dir");
mkdir($tmp_dir);
$peq_dump = file_get_contents('http://db.projecteq.net/latest');
file_put_contents($tmp_path, $peq_dump);

echo "Installing mysql-client...\n";
exec("apt-get update && apt-get -y install unzip mysql-client");

echo "Unzipping $tmp_path...\n";
exec("unzip -o $tmp_path -d $tmp_dir");

echo "Creating database PEQ...\n";
exec('mysql -h mariadb -uroot -proot -e "CREATE DATABASE peq"  2>&1 | grep -v \'Warning\'');
echo "Sourcing data...\n";
chdir("$tmp_dir/peq-dump");
exec("mysql -h mariadb -uroot -proot peq < create_all_tables.sql  2>&1 | grep -v 'Warning'");
echo "Seeding complete!\n";

// Cleanup
chdir($origin_directory);
array_map('unlink', glob($tmp_dir . "/*.*"));