<?php
namespace DbDiff;

use MathieuViossat\Util\ArrayToTextTable;

class ConsoleOutput
{

    public static function notExistTable($dbDiff)
    {
        $notTables = array_chunk($dbDiff->getNotExistTables(), 1);
        $notTableFormat = [];
        foreach ($notTables as $table) {
            $notTableFormat[] = ['not exist table' => $table[0]];
        }
        $renderer = new ArrayToTextTable($notTableFormat);
        $renderer->setDecorator(new \Zend\Text\Table\Decorator\Ascii());
        echo $renderer->getTable();
    }

    public static function notExistFields($dbDiff)
    {
        $diffFields = $dbDiff->getNotExistFields();
        foreach ($diffFields as $fieldTable => $fields) {
            $tableF = array_chunk($fields, 1);
            foreach ($tableF as &$value) {
                $value[$fieldTable] = $value[0];
                unset($value[0]);
            }

            $renderer = new ArrayToTextTable($tableF);
            $renderer->setUpperKeys(false);
            $renderer->setDecorator(new \Zend\Text\Table\Decorator\Ascii());
            echo $renderer->getTable();
        }
    }

    public static function getNotExistIndex($dbDiff)
    {
        $diffIndexs = $dbDiff->getNotExistIndex();
        foreach ($diffIndexs as $table => $indexs) {
            $index = array_chunk($indexs, 1);
            foreach ($index as &$value) {
                $value[$table] = $value[0];
                unset($value[0]);
            }

            $renderer = new ArrayToTextTable($index);
            $renderer->setUpperKeys(false);
            $renderer->setDecorator(new \Zend\Text\Table\Decorator\Ascii());
            echo $renderer->getTable();
        }
    }

}
