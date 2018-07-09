<?php
namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Database\Eloquent\Model;
use Kubis\Filtering\Searchable;

abstract class TestCase extends BaseTestCase
{

    protected $factory;

    public function setUp()
    {
        $capsule = new Manager;
        $capsule->addConnection([
           "driver" => "sqlite",
           "database" => __DIR__ . "/db.sqlite"
        ]);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        $this->factory = new Factory(\Faker\Factory::create());

        $this->migrate();
        $this->factories();
    }

    private function migrate()
    {
        Manager::schema()->dropIfExists('products');
        Manager::schema()->dropIfExists('categories');
        Manager::schema()->create('categories', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });
        Manager::schema()->create('products', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->string('description');
            $table->integer('category_id');
            $table->timestamps();
        });
    }

    private function factories()
    {
        $this->factory->define(Category::class, function (\Faker\Generator $faker) {
            return [
                'name' => $faker->name,
            ];
        });
        $this->factory->define(Product::class, function (\Faker\Generator $faker) {
            return [
                'name' => $faker->name,
                'description' => $faker->sentence,
                'category_id' => $this->factory->of(Category::class)->create()->id
            ];
        });
    }
}


class Product extends Model {
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
class Category extends Model {}

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
