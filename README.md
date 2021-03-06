# Laravel resource filtering for Eloquent models

* For search by term, you must use Laravel Scout

## Instalation
`composer require marcoskubis/resource-filtering`

## How to use

Your model: Product.php
```
use Illuminate\Database\Eloquent\Model;

class Product extends Model {}

```

ProductSearch.php
```
use Kubis\Filtering\Searchable;

class ProductSearch extends Searchable
{
    /**
     * Get the model for filtering
     * @return String
     */
    protected function getModel(): String
    {
        return Product::class;
    }

    /**
     * Get the default field filters. Must be a field name in your database.
     * @return Array
     */
    protected function getFilters(): Array
    {
        return ['name'];
    }

    /**
     * Get date fields. Is use for filter between dates.
     * @return Array
     */
    protected function getDates(): Array
    {
        return [
            'created_at',
            'updated_at',
        ];
    }

    /**
     * Optional, but very useful: Add custom filter. See below the example with 'my_custom_filter'.
     * @return Builder
     */
    function getMyCustomFilterFilter ($builder, $value) {
        return $builder->where('something', $value);
    }
}

```

#### Examples
```
// To get all records
ProductSearch::make()->get();

// To paginate
ProductSearch::make()->paginate();

// To filter by field
ProductSearch::make()->filter(['name' => "My Product Name"])->get();
ProductSearch::make()->filter(['category_id' => 1)->get();
ProductSearch::make()->filter(['category_id' => 1)->paginate();

// Filter by a custom filter
ProductSearch::make()->filter(['my_custom_filter' => 'some value')->paginate();

// To sort by field
ProductSearch::make()->sorting(['name' => 'desc'])->get();

// To sort by a related field
ProductSearch::make()->sorting(['category.name' => 'desc'])->get();

// To serach by a term. Requires Laravel Scout
ProductSearch::make()->term("My search term")->get();
```
