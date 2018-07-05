<?php
namespace Kubis\Filtering;

use Carbon\Carbon;

trait FilterBehavior {

    /**
     * Apply filters to query
     * @param  Array $filters
     * @return Searchable
     */
    public function filter(Array $filters = [])
    {
        $this->applyFiltersToQuery($filters);

        return $this;
    }

    /**
     * Apply filters to query by using decorator methods.
     * @param  Array $filters
     * @return  null
     */
    private function applyFiltersToQuery(Array $filters)
    {
        $filters = $this->removeEmptyValues($filters);

        foreach ($filters as $filterName => $value) {

            $method = $this->getFilterDecorator($filterName);

            if ($this->isValidDecorator($method)) {
                $this->query = $this->$method($value);
            }

            else if ($this->isValidFilter($filterName)) {
                $this->query = $this->applyDefaultFilter($filterName, $value);
            }

            else if ($this->isDateFilter($filterName)) {
                $this->query = $this->applyDefaultDateFilter($filterName, $value);
            }
        }
    }

    /**
     * Apply default query implementation
     * @param  String  $filterName
     * @param  Array|String  $value
     * @return Builder
     */
    private function applyDefaultFilter(String $filterName, $value)
    {
        if (is_array($value)) {
            return $this->query->whereIn($filterName, $value);
        } else {
            return $this->query->where($filterName, $value);
        }
    }

    /**
     * Apply default date filter to query
     * @param  String $filterName
     * @param  Carbon|String|Array $value
     * @return Builder
     */
    private function applyDefaultDateFilter(String $filterName, $value)
    {
        [$from, $until] = $this->sanitizeDateInput($value);

        $from = $this->parseDate($from);
        $until = $this->parseDate($until);

        if ($from && $until === false) {
            $until = $from->copy()->addDay()->subSecond();
        }

        if ($from && $until === null) {
            return $this->query->where($filterName, '>=', $from->toDateTimeString());
        }

        if ($until && $from === null) {
            return $this->query->where($filterName, '<=', $until->toDateTimeString());
        }

        if ($from && $until) {
            return $this->query
                ->where($filterName, '>=', $from->toDateTimeString())
                ->where($filterName, '<=', $until->toDateTimeString());
        }

        return $this->query;
    }

    /**
     * Get decorator name for filters
     * @param  String $filterName
     * @return String
     */
    private function getFilterDecorator($filterName)
    {
        return 'get' . studly_case($filterName) . 'Filter';
    }

    /**
     * Check if the decorator method exists
     * @param  String  $method
     * @return boolean
     */
    private function isValidDecorator($method) {
        return method_exists(get_class($this), $method);
    }

    /**
     * Check if the filter name is valid
     * @param  String  $method
     * @return boolean
     */
    private function isValidFilter($filterName) {
        return in_array($filterName, $this->filters);
    }

    /**
     * Remove empty values from filters
     * @param  Array  $filters
     * @return Array
     */
    private function removeEmptyValues(Array $filters)
    {
        return array_filter($filters, function ($value, $key) {
            return (bool) $value;
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Check if the filter name is a date filter
     * @param  String  $filterName
     * @return boolean
     */
    private function isDateFilter($filterName)
    {
        return in_array($filterName, $this->dates);
    }

    /**
     * Check if date is a Carbon instance or a string, if it is a string convert it to a Carbon instance.
     * @param  String|Carbon $date
     * @return Carbon
     */
    protected function parseDate($date)
    {
        if ($date && !$date instanceof Carbon) {
            $date = Carbon::parse($date);
        }
        return $date;
    }

    /**
     * Prepare data for date filter. It checks for many forms to input data.
     * @param  String|Array $value
     * @return Array
     */
    protected function sanitizeDateInput($value) {
        $from = $value;
        $until = false;

        if (is_array($value) && count($value) >= 2) {
            $from = $value[0];
            $until = $value[1];
        } else if (is_array($value) && count($value) === 1){
            $from = $value[0];
            $until = null;
        }

        return [$from, $until];
    }
}
