<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;
    private $category;

    protected function setUp(): void {

        parent::setUp (); // TODO: Change the autogenerated stub
        $this->category = factory(Category::class)->create();
    }

    public function testIndex()
    {
        $response = $this->get(route('categories.index'));
        $response
            ->assertStatus(200)
            ->assertJson ([$this->category->toArray ()]);
    }

    public function testShow()
    {
        $response = $this->get(route ('categories.show', ['category' => $this->category->id]));
        $response
            ->assertStatus(200)
            ->assertJson($this->category->toArrays());
    }

    //ver se aparece erro de validacao dos dados
    public function testInvalidationData()
    {
        $data = ['name' => ''];
        $this->assertInvalidationInUpdateAction($data, 'required');
        $this->assertInvalidationInStoreAction($data, 'required');

        $data = ['name' => str_repeat ('a', 256)];
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);

        $data = ['is_active' => 'a'];
        $this->assertInvalidationInUpdateAction($data, 'boolean');
        $this->assertInvalidationInStoreAction($data, 'boolean');
    }

    public function testStore()
    {
        $data = ['name' => 'teste'];
        $response = $this->assertStore($data, $data + ['description' => null, 'is_active' => true, 'deleted_at' => null]);
        $response->assertJsonStructure([
            'created_at', 'updated_at'
        ]);

        $data = [
            'name' => 'test',
            'is_active' => false,
            'description' => 'teste description'
        ];

        $this->assertStore($data, $data + ['description' => 'teste description', 'is_active' => false]);

    }

    public function testUpdate()
    {
        $data = [
            'name' => 'test',
            'description' => 'test',
            'is_active' => true
        ];

        $response = $this->assertUpdate($data, $data + ['deleted_at' => null]);
        $response->assertJsonStructure([
            'created_at', 'updated_at'
        ]);

        $data = [
            'name' => 'test',
            'description' => '',
        ];

        $this->assertUpdate($data, array_merge($data, ['description' => null]));

        $data['description'] = 'test';
        $this->assertUpdate($data, array_merge($data, ['description' => 'test']));

        $data['description'] = null;
        $this->assertUpdate($data, array_merge($data, ['description' => null]));

    }

    public function testDestroy()
    {
        $response = $this->json ('DELETE', route ('categories.destroy', ['category' => $this->category->id]));
        $response->assertStatus (204);
        $this->assertNull (Category::find ($this->category->id));
        $this->assertNotNull (Category::withTrashed()->find($this->category->id));

    }

    protected function assertInvalidationRequired(TestResponse $response) {

        $this->assertInvalidationFields ($response, ['name'], 'required');
        $response->assertJsonMissingValidationErrors (['is_active']);
//            ->assertStatus(422)
//            ->assertJsonValidationErrors (['name'])
//            ->assertJsonMissingValidationErrors (['is_active'])
//            ->assertJsonFragment ([
//                \Lang::get('validation.required', ['attribute' => 'name'])
//        ]);

    }

    protected function assertInvalidationMax(TestResponse $response) {
        $this->assertInvalidationFields ($response, ['name'], 'max.string', ['max' => 255]);
    }

    protected function assertInvalidationBoolean(TestResponse $response) {
        $this->assertInvalidationFields ($response, ['is_active'], 'boolean');
    }

    protected function routeStore()
    {
        return route('categories.store');
    }
    protected function routeUpdate()
    {
        return route('categories.update', ['category' => $this->category->id]);
    }

    protected function model()
    {
        return Category::class;
    }

}
