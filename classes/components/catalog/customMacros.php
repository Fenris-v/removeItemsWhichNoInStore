<?php

define('HIERARCHY_TYPE_ID', 56);
define('QUANTITY_FIELD_ID', 340);

/** Класс пользовательских методов административной панели */
class CatalogCustomMacros
{
    /** @var catalog $module */
    public $module;

    /**
     * Перемещает айтемы с нулевым количеством на складе в корзину
     * @throws databaseException
     */
    public function removeItems(): void
    {
        $items = $this->getItems();

        if (!empty($items)) {
            $hierarchy = umiHierarchy::getInstance();

            foreach ($items as $item) {
                $hierarchy->delElement($item['id']);
            }
        }
    }

    /**
     * Возвращает айтемы с нулевым количеством на складе
     * @return array
     * @throws databaseException
     */
    public function getItems(): array
    {
        $typeId = HIERARCHY_TYPE_ID;
        $fieldId = QUANTITY_FIELD_ID;

        $connection = ConnectionPool::getInstance()->getConnection();

        $selectSql = <<<SQL
SELECT id FROM cms3_hierarchy
LEFT JOIN cms3_object_content ON cms3_object_content.obj_id = cms3_hierarchy.obj_id 
WHERE type_id = '{$typeId}' AND field_id = '{$fieldId}' AND int_val = 0 AND is_deleted = 0
SQL;

        return $this->getResponse($connection, (string)$selectSql);
    }

    /**
     * Выполняет sql запрос и возвращает массив с данными
     * @param $connection - подключение к БД
     * @param $sql - запрос
     * @return array
     */
    private function getResponse($connection, string $sql): array
    {
        $result = $connection->queryResult($sql);
        $result->setFetchType(IQueryResult::FETCH_ASSOC);

        $objList = [];
        if ($result->length() > 0) {
            while ($row = $result->fetch()) {
                $objList[] = $row;
            }
        }

        return $objList;
    }
}
