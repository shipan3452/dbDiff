<?php
use DbDiff\ConsoleOutput;
use DbDiff\DbDiff;
require_once 'vendor/autoload.php';

$db1 = new \PDO('mysql:dbname=test;host=127.0.0.1', 'user_name', 'password');
$db2 = new \PDO('mysql:dbname=test;host=127.0.0.1', 'user_name', 'password');
$diff = new DbDiff($db1, $db2);

ConsoleOutput::getNotExistIndex($diff);exit;
var_dump($diff->getNotExistIndex());exit;
//outputNotExistTableCreateSql($diff, $db1, $db2);

ConsoleOutput::notExistTable($diff);
ConsoleOutput::notExistFields($diff);
exit;
var_dump($diff->getNotExistFields());

/**
 *将新增表sql写入文件
 * @author shipanpan
 * @param  DbDiff $diff [description]
 * @param  [type] $db1  [description]
 * @param  [type] $db2  [description]
 * @return [type]       [description]
 */
function outputNotExistTableCreateSql(DbDiff $diff, $db1, $db2, $outFile = null)
{
    if (is_null($outFile)) {
        $outFile = date('Y-m-d') . '.sql';
    }
    foreach ($diff->getNotExistTables() as $table) {
        $sql = $db1->query("show create table {$table}")->fetch()['Create Table'];
        $sql = preg_replace('/AUTO_INCREMENT=\d*/', '', $sql);
        file_put_contents($outFile, $sql . "\r\n\r\n", FILE_APPEND);
    }
}

function outNotExistFields(DbDiff $diff, $db1, $db2, $outFile = null)
{
    if (is_null($outFile)) {
        $outFile = date('Y-m-d') . '_field.sql';
    }

    foreach ($diff->getNotExistFields() as $table => $fields) {
        # code...
    }
}
