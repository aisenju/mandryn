<?php

/**
 * <p>Class to facilate database command operation such as insert,delete and update.</p>
 *  
 * @version 1.1.0
 * @category Database
 * @author Mohd Ilhammuddin Bin Mohd Fuead <ilham.fuead@gmail.com>
 * @copyright Copyright(c) 2017, Mandryn Team 
 */
class DBCommand {

    private $SQLQueryObj;
    private $DBQueryObj;
    private $fieldArray;
    private $conditionFieldArray;
    private $operationType;

    public function __construct(DBQuery $DBQueryObj) {
        $this->DBQueryObj = $DBQueryObj;
        $this->SQLQueryObj = new SQLQuery();
        $this->operationType = '';
        $this->resetAllFieldArray();
    }

    public function enableTransaction() {
        $this->DBQueryObj->enableTransaction();
    }

    public function disableTransaction() {
        $this->DBQueryObj->disableTransaction();
    }

    public function commitTransaction() {
        return $this->DBQueryObj->commitTransaction();
    }

    private function resetAllFieldArray() {
        $this->fieldArray = [];
        $this->conditionFieldArray = [];
    }

    public function setINSERTintoTable($tableName) {
        $this->SQLQueryObj->setINSERTQuery($tableName);
        $this->resetAllFieldArray();
        $this->operationType = 'insert';
    }

    public function setUPDATEtoTable($tableName) {
        $this->SQLQueryObj->setUPDATEQuery($tableName);
        $this->resetAllFieldArray();
        $this->operationType = 'update';
    }

    public function setDELETEfromTable($tableName) {
        $this->SQLQueryObj->setDELETEQuery($tableName);
        $this->resetAllFieldArray();
        $this->operationType = 'delete';
    }

    private function addFieldArray($fieldName, $fieldValue, $IFieldType) {
        $field = new MagicObject();
        $field->name = $fieldName;
        $field->value = $fieldValue;
        $field->type = $IFieldType;
        $this->fieldArray[] = $field;
    }

    public function addUPDATEcolumn($fieldName, $fieldValue, $IFieldType) {
        $this->addFieldArray($fieldName, $fieldValue, $IFieldType);
        $this->SQLQueryObj->addUpdateField($fieldName, $fieldValue, $IFieldType);
    }

    public function addINSERTcolumn($fieldName, $fieldValue, $IFieldType) {
        $this->addFieldArray($fieldName, $fieldValue, $IFieldType);
        $this->SQLQueryObj->addInsertField($fieldName, $fieldValue, $IFieldType);
    }

    public function integrateInputColumns(MagicInput $magicInputObj, array $excludeList = []) {
        $inputs = $magicInputObj->toArray();

        if (count($inputs) > 0) {
            foreach ($magicInputObj->getInputsDefinition() as $def) {


                $name = $def['name'];
                $alias = $def['alias'];

                $type = $this->typeIntegrationMapper($def['type']);
                $fieldName = $this->fieldNameSolver($name, $alias);

                if (in_array($name, $excludeList)) {
                    continue;
                }

                if (!$def['required']) {
                    if (!isset($inputs[$name])) {
                        continue;
                    }
                }

                if ($this->operationType == 'update') {
                    $this->addUPDATEcolumn($fieldName, $inputs[$name], $type);
                } elseif ($this->operationType == 'insert') {
                    $this->addINSERTcolumn($fieldName, $inputs[$name], $type);
                }
            }
        }
    }

    private function fieldNameSolver($name, $alias) {
        if ($alias == '') {
            return $name;
        } else {
            return $alias;
        }
    }

    private function typeIntegrationMapper($type) {

        switch ($type) {
            case 'd':
            case 'dt':
                return 'datetime';
            case 'n':
            case 'f':
                return 'float';
            case 'i':
                return 'integer';
            case 's':
            case 'e':
                return 'string';
            case 'u':
            case '':
                return 'unknown';
        }
    }

    public function addConditionStatement($fieldName, $fieldValue, $IFieldType, $IConditionOperator = "", $IComparisonType = "=", $IWildcardPosition = "") {
        $this->SQLQueryObj->addConditionField($fieldName, $fieldValue, $IFieldType, $IConditionOperator, $IComparisonType, $IWildcardPosition);
    }

    public function addInConditionStatement($fieldName, $fieldValue, $IFieldType, $IConditionOperator) {
        $this->SQLQueryObj->addInConditionField($fieldName, $fieldValue, $IFieldType, $IConditionOperator);
    }

    public function addNotEqualConditionStatement($fieldName, $fieldValue, $IFieldType, $IConditionOperator) {
        $this->SQLQueryObj->addNotEqualConditionField($fieldName, $fieldValue, $IFieldType, $IConditionOperator);
    }

    public function executeQueryCommand() {
        $this->DBQueryObj->setSQL_Statement($this->SQLQueryObj->getSQLQuery());
        $this->DBQueryObj->executeNon_Query();
    }

    public function executeCustomQueryCommand($sql) {
        $this->DBQueryObj->setSQL_Statement($sql);
        $this->DBQueryObj->executeNon_Query();
    }

    public function getExecutionStatus() {
        return $this->DBQueryObj->getCommandStatus();
    }

    public function getAffectedRowCount() {
        return mysqli_affected_rows($this->DBQueryObj->getLink());
    }

    public function getInsertID() {
        return mysqli_insert_id($this->DBQueryObj->getLink());
    }

    public function getSQLstring() {
        return $this->SQLQueryObj->getSQLQuery();
    }

    public function getErrno() {
        return mysqli_errno($this->DBQueryObj->getLink());
    }

    public function getError() {
        return mysqli_error($this->DBQueryObj->getLink());
    }

    public function __destruct() {
        unset($this->fieldArray);
        unset($this->conditionFieldArray);
    }

}