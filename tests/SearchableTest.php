<?php
namespace Tests;

use Tests\ProductSearch;

class SearchableTest extends TestCase
{

    /** @test */
    function it_can_get_all() {
        $all = ProductSearch::make()->get();

        $this->assertEquals($this->products->count(), $all->count());
    }

    /** @test */
    function it_can_filter_by_name() {
        $product = $this->products->last();

        $all = ProductSearch::make()->filter(['name' => $product->name])->get();

        $this->assertEquals(1, $all->count());
        $this->assertEquals($product->name, $all->first()->name);
    }
}
