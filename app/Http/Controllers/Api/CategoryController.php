<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;

class CategoryController extends BasicCrudController
{
    private $rules =  [
        'name' => 'required|max:255',
        'is_active' => 'boolean',
        'description' => 'nullable'
    ];


    protected function model()
    {
        return Category::class;
    }

    protected function rulesUpdate()
    {
        return $this->rules;
    }

    protected function rulesStore()
    {
        return $this->rules;
    }
}
