<?php
namespace DbDiff;

class DbDiff
{
    /**
     * 源库数据库表
     * @var array
     */
    private $srcDbTables;

    /**
     * 目标库数据库表
     * @var array
     */
    private $dstDbTables;

    /**
     * 存在于源库但不在目标库的表
     * @var array
     */
    private $notExistTables;

    private $notExistIndexs;

    /**
     * 在目标库和源库都存在的表，但在目标库不存在的字段
     * @var array  ['table1'=>['目标库的table1表不存在的字段1',....]]
     */
    private $notExistFields;

    public function __construct($srcDb, $dstDb)
    {
        $this->srcDb = $srcDb;
        $this->dstDb = $dstDb;

        $this->srcTables = array_column($this->srcDb->query('show tables')->fetchAll(), 0);
        $this->dstTables = array_column($this->dstDb->query('show tables')->fetchAll(), 0);
    }

    /**
     * 获取存在于源库但不在目标库的表
     * @author shipanpan
     * @param  array  $exclusive 排除对比的表
     * @return array
     */
    public function getNotExistTables(array $exclusive = [])
    {
        if (is_null($this->notExistTables)) {
            $this->notExistTables = array_diff($this->srcTables, $this->dstTables, $exclusive);
        }
        return $this->notExistTables;
    }

    /**
     * 在目标库和源库都存在的表，但在目标库不存在的字段
     * @author shipanpan
     * @param  array  $exclusive 排除对比的字段 ['table1'=>['table1表需要排除的字段1',....]]
     * @return array  ['table1'=>['目标库的table1表不存在的字段1',....]]
     */
    public function getNotExistFields(array $exclusive = [])
    {
        if (!is_null($this->notExistFields)) {
            return $this->notExistFields;
        }
        $this->notExistFields = [];
        foreach (array_intersect($this->dstTables, $this->srcTables) as $dstTable) {
            $srcTableStruct = self::formatToCompareStruct($this->srcDb->query("desc  {$dstTable}")->fetchAll());
            $dstTableStruct = self::formatToCompareStruct($this->dstDb->query("desc  {$dstTable}")->fetchAll());

            $notExistField = array_diff(array_column($srcTableStruct, 'field_name'), array_column($dstTableStruct, 'field_name'), isset($exclusive[$dstTable]) ?: []);

            if (!$notExistField) {
                continue;
            }

            $this->notExistFields[$dstTable] = $notExistField;
        }
        return $this->notExistFields;
    }

    public function getNotExistIndex()
    {
        if (!is_null($this->notExistIndexs)) {
            return $this->notExistIndexs;
        }
        $this->notExistIndexs = [];
        foreach (array_intersect($this->dstTables, $this->srcTables) as $dstTable) {
            $srcIndexs = $this->formatTabIndex($this->dstDb, $dstTable);
            $dstIndexs = $this->formatTabIndex($this->srcDb, $dstTable);

            foreach ($srcIndexs as $srcIdxName => $srcIdxColumn) {
                $dstIdxColumn = $dstIndexs[$srcIdxName] ?? [];
                if ($srcIdxColumn != $dstIdxColumn) {
                    $this->notExistIndexs[$dstTable][] = $srcIdxName;
                }
            }
        }
        return $this->notExistIndexs;
    }

    protected function formatTabIndex($db, string $table)
    {
        $indexInfo = $db->query("show keys from  {$table}")->fetchAll();
        $re = [];
        foreach ($indexInfo as $index) {
            $key = $index['Key_name'];
            if (!isset($re[$key])) {
                $re[$key] = [];
            }
            $re[$key][] = $index['Column_name'];
        }
        return $re;
    }

    /**
     *
     */
    public static function formatToCompareStruct(array $tableStruct, $compareFieldAttr = ['Field', 'Type', 'Null'])
    {
        if (!$tableStruct) {
            return [];
        }

        $field = array_column($tableStruct, 'Field');
        $fieldType = array_column($tableStruct, 'Type');
        $fieldIsNull = array_column($tableStruct, 'Null');

        $compareInfo = [];
        foreach ($tableStruct as $_fieldIndex => $_fieldInfo) {
            $compareInfo[] = ['field_name' => $_fieldInfo['Field'], 'field_type' => $_fieldInfo['Type'], 'field_is_null' => $_fieldInfo['Null']];
        }
        return $compareInfo;

    }
}
