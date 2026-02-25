<?php

namespace App\Traits;

use App\Classes\Common;

trait SelectOptionsTraits
{
    public function updated()
    {
        $company = company();
        cache()->forget("company_{$company->id}_{$this->model}");
    }

    public function stored()
    {
        $company = company();
        cache()->forget("company_{$company->id}_{$this->model}");
    }

    public function destroyed()
    {
        $company = company();
        cache()->forget("company_{$company->id}_{$this->model}");
    }

    public function allOptions()
    {
        $company = company();
        $model = $this->model;
        
        $defaultColumns = (new $model)->default ?? ['id'];

        $data = cache()->remember("company_{$company->id}_{$model}", 3600, function () use ($model, $defaultColumns) {
            return $model::select($defaultColumns)->limit(200)->get();
        });

        return response()->json([
            'data' => $data,
        ]);
    }

    public function updateStatus()
    {
        $company = company();
        $model = $this->model;

        $request = request();
        $id = Common::getIdFromHash($request->xid);

        $item = $model::where('company_id', $company->id)->findOrFail($id);
        $item->is_active = !$item->is_active;
        $item->save();

        cache()->forget("company_{$company->id}_{$this->model}");

        return response()->json([
            'data' => $item,
        ]);
    }
}