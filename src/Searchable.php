<?php
namespace Kubis\Filtering;

use Illuminate\Database\Eloquent\Builder;

abstract class Searchable
{

    use FilterBehavior, SortingBehavior;

    /**
     * @var Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * @var Array
     */
    protected $filters = [];

    /**
     * @var Array
     */
    protected $dates = [];

    /**
     * @var Builder
     */
    protected $query;

    /**
     * @var String
     */
    protected $term;

    /**
     * Get eloquent model for filtering
     * @return Model
     */
    abstract protected function getModel(): String;

    /**
     * Get filter fields
     * @return Array
     */
    abstract protected function getFilters(): Array;

    /**
     * Get date filter fields
     * @return Array
     */
    abstract protected function getDates(): Array;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->model    = $this->getModel();
        $this->filters  = $this->getFilters();
        $this->dates    = $this->getDates();
    }

    /**
     * Instantiate and apply filters and sorting to query
     * @param  Array $filters
     * @return Searchable
     */
    public static function make(Array $filters = [])
    {
        $searchable = new static;
        $searchable->getQuery();

        return $searchable;
    }

    /**
     * Add search term to query
     * @param  String|null $term
     * @return Searchable
     */
    public function term(String $term = null)
    {
        $this->term = $term;

        return $this;
    }

    /**
     * Get all items from query
     * @param  Array|null $columns
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function get(Array $columns = ['*'])
    {
        return $this->getBuilderOutput()->get($columns);
    }

    /**
     * Get paginated items from query
     * @param  Int|null $perPage
     * @param  Array $columns
     * @return Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate(Int $perPage = null, Array $columns = ['*'])
    {
        return $this->getBuilderOutput()->paginate($perPage, $columns);
    }

    /**
     * Get final query output. Checks if returns Query Builder or Scout Builder.
     * @return Builder
     */
    private function getBuilderOutput() {

        $this->addGlobalQuery($this->query);

        if ($this->term) {
            return $this->model::search($this->term)->constrain($this->query);
        }
        return $this->query;
    }

    /**
     * Add relationships to query
     * @param  Array|String $relations
     * @return Searchable
     */
    public function with($relations)
    {
        if (!is_array($relations)) {
            $relations = [$relations];
        }

        $this->query->with($relations);

        return $this;
    }

    /**
     * Add only trashed scope
     * @return Searchable
     */
    public function trashed($trashed = true)
    {
        if ($trashed) {
            $this->query->onlyTrashed();
        }

        return $this;
    }

    /**
     * Get model query. The model property must be implemented by the concrete class
     * @return  null
     */
    private function getQuery()
    {
        if (!$this->query) {
            $this->query = $this->model::query();
        }

        return $this->query;
    }

    /**
     * Add query to all solicitations
     * @param Builder $query
     */
    protected function addGlobalQuery(Builder $query)
    {
        return $query;
    }

}
