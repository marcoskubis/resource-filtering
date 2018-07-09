<?php
namespace Tests;

use Tests\Category;
use Tests\Product;
use Tests\ProductSearch;

class SearchableTest extends TestCase
{

    /** @test */
    function it_can_get_all() {

        $products = $this->factory->of(Product::class)->times(10)->create();

        $all = ProductSearch::make()->get();

        $this->assertEquals($products->count(), $all->count());
    }

    /** @test */
    function it_can_filter_by_name() {
        $products = $this->factory->of(Product::class)->times(10)->create();
        $product = $products->last();

        $all = ProductSearch::make()->filter(['name' => $product->name])->get();

        $this->assertEquals(1, $all->count());
        $this->assertEquals($product->name, $all->first()->name);
    }

    /** @test */
    function it_can_sort_by_name() {
        $products = $this->factory->of(Product::class)->times(10)->create();
        $firstProduct = $products->sortBy('name')->first();
        $lastProduct = $products->sortByDesc('name')->first();


        $all = ProductSearch::make()->sorting(['name' => 'asc'])->get();

        $this->assertEquals($firstProduct->name, $all->first()->name);
        $this->assertEquals($lastProduct->name, $all->last()->name);
        $this->assertEquals(10, $all->count());
    }

    /** @test */
    function it_can_sort_relation_column_by_asc() {
        $products = $this->factory->of(Product::class)->times(10)->create();
        $firstCategory = Category::orderBy('name', 'asc')->first();

        $all = ProductSearch::make()->sorting(['category.name' => 'asc'])->get();

        $this->assertEquals($firstCategory->name, $all->first()->category->name);
    }

    /** @test */
    function it_can_sort_relation_column_by_desc() {
        $products = $this->factory->of(Product::class)->times(10)->create();
        $firstCategory = Category::orderBy('name', 'desc')->first();

        $all = ProductSearch::make()->sorting(['category.name' => 'desc'])->get();

        $this->assertEquals($firstCategory->name, $all->first()->category->name);
    }
}
