<?php
namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Database\Eloquent\Model;
use Kubis\Filtering\Searchable;

abstract class TestCase extends BaseTestCase
{
    public function setUp()
    {
        $capsule = new Manager;
        $capsule->addConnection([
           "driver" => "sqlite",
           "database" => __DIR__ . "/db.sqlite"
        ]);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        Manager::schema()->dropIfExists('products');
        Manager::schema()->create('products', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->string('description');
            $table->timestamps();
        });

        $factory = new Factory(\Faker\Factory::create());
        $factory->define(Product::class, function (\Faker\Generator $faker) {
            return [
                'name' => $faker->name,
                'description' => $faker->sentence,
            ];
        });

        $this->products = $factory->of(Product::class)->times(10)->create();
    }
}


class Product extends Model {}

class ProductSearch extends Searchable
{
    protected function getModel(): String
    {
        return Product::class;
    }

    protected function getFilters(): Array
    {
        return ['name'];
    }

    protected function getDates(): Array
    {
        return [
            'created_at',
            'updated_at',
        ];
    }
}
