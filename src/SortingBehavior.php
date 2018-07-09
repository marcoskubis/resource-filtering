<?php
namespace Kubis\Filtering;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait SortingBehavior {

    /**
     * @var array
     */
    protected $sorting = [];

    /**
     * Add sorting behavior
     * @param  String $field
     * @param  String $sort
     * @return Searchable
     */
    public function sorting($field = null, $direction = null)
    {
        [$field, $direction] = $this->getSortingFieldAndDirection($field, $direction);

        if (!$direction) {
            return $this;
        }

        $this->applySortingToQuery($field, $direction);

        return $this;
    }

    /**
     * Extracts sorting field and direction
     * @param  String|Array $field
     * @param  String $direction
     * @return Array
     */
    private function getSortingFieldAndDirection($field, String $direction = null)
    {
        if (is_array($field)) {
            $direction  = array_values($field)[0] ?? null;
            $field      = key($field);
        }
        return [$field, $direction];
    }

    /**
     * Get table name
     * @return String
     */
    private function getTableName() {
        return (new $this->model)->getTable();
    }


    /**
     * Apply Sorting method to query by decorators
     * @param  String   $sortingName
     * @param  String   $sort
     * @return Searchable
     */
    private function applySortingToQuery(String $sortingName, String $sort)
    {
        if ($this->isValidSorting($sortingName)) {
            $this->query = $this->applyDefaultSorting($sortingName, $sort);
        }

        return $this;
    }

    /**
     * Apply default sorting implementation to query
     * @param  String $sortingName
     * @param  String $sort
     * @return Builder
     */
    private function applyDefaultSorting(String $sortingName, String $sort)
    {
        if (strpos($sortingName, '.') !== false) {
            $query = $this->applyRelationSorting($sortingName, $sort);
        } else {
            $query = $this->query->orderBy($sortingName, $sort);
        }

        return $query;
    }

    /**
     * Apply relation sorting
     * @param  String $sortingName
     * @param  String $sort
     * @return Builder
     */
    private function applyRelationSorting(String $sortingName, String $sort)
    {
        list($relationName, $relationField) = explode('.', $sortingName);

        $relation = $this->query->getRelation($relationName);

        if ($relation instanceof BelongsTo) {
            $query = $this->joinBelongsTo($relation);
        }elseif ($relation instanceof HasOne) {
            $query = $this->joinHasOne($relation);
        }

        $table = $this->getTableName();

        return $query->select("{$table}.*")->orderBy('r.'.$relationField, $sort);
    }

    /**
     * Join belongsTo relation for sorting
     * @param  BelongsTo $relation
     * @return Builder
     */
    private function joinBelongsTo(BelongsTo $relation)
    {
        $table = $relation->getRelated()->getTable();
        $foreignKey = $relation->getQualifiedForeignKey();
        $ownerKey = str_replace($table, 'r', $relation->getQualifiedOwnerKeyName());
        return $this->query->join("{$table} AS r", "{$foreignKey}", '=', "{$ownerKey}");
    }

    /**
     * Join HasOne relation for sorting
     * @param  HasOne $relation
     * @return Builder
     */
    public function joinHasOne(HasOne $relation)
    {
        $table = $relation->getRelated()->getTable();
        $parentKey = $relation->getQualifiedParentKeyName();
        $foreignKey = str_replace($table, 'r', $relation->getQualifiedForeignKeyName());
        return $this->query->join("{$table} AS r", "{$parentKey}", '=', "{$foreignKey}");
    }

    /**
     * Check if the sorting field is valid
     * @param  String  $method
     * @return boolean
     */
    private function isValidSorting($sortingName) {
        return count($this->sorting) == 0 || in_array($sortingName, $this->sorting);
    }
}
