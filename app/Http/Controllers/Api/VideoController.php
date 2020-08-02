<?php

namespace App\Http\Controllers\Api;

use App\Models\Video;
use App\Rules\GendersHasCategoriesRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/*
 * - begin transaction - Marca Inicio da transascao
 * - transaction - executa todas as transacoes
 * - commit - persiste as transacoes
 * -rollback - desfaz todas as transacoes do checkpoint
 *
 * */
class VideoController extends BasicCrudController
{
    private $rules;

    public function __construct()
    {
        $this->rules = [
            'title' => 'required|max:255',
            'description' => 'required', //type text
            'year_launched' => 'required|date_format:Y', //verify year only
            'opened' => 'boolean',
            'rating' => 'required|in:' . implode(',', Video::RATING_LIST),
            'duration' => 'required|integer',
            'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL',
            'genders_id' => [
                'required',
                'array',
                'exists:genders,id,deleted_at,NULL'
            ],
            'video_file' => 'mimetypes:video/mp4|max:51200000', //max:10240 = max 10 MB.
            'thumb_file' => 'max:5120',
            'banner_file' => 'max:10240',
            'trailer_file' => 'mimetypes:video/mp4|max:1024000'
        ];
    }

    /* Relacionamentos ... Tem que Sobrescrever metodos*/

    public function store(Request $request)
    {
        $this->addRuleIfGenderHasCategories($request);
        $validateData = $this->validate($request, $this->rulesStore());
        $obj = $this->model()::create($validateData);
        $obj->refresh();
        return $obj;
    }

    public function update(Request $request, $id)
    {
        $obj = $this->findOrFail($id);
        $this->addRuleIfGenderHasCategories($request);
        $validateData = $this->validate($request, $this->rulesUpdate());
        $obj->update($validateData);
        $obj->refresh();
        return $obj;
    }

    protected function addRuleIfGenderHasCategories(Request $request)
    {
        $categoriesId = $request->get('categories_id');

        //because test invalidation
        $categoriesId = is_array($categoriesId) ? $categoriesId : [];
        $this->rules['genders_id'][] = new GendersHasCategoriesRule(
            $categoriesId
        );
    }

    protected function model()
    {
        return Video::class;
    }

    protected function rulesStore()
    {
        return $this->rules;
    }

    protected function rulesUpdate()
    {
        return $this->rules;
    }
}
