<?php

namespace App\Http\Requests\Api\Auth;

use App\Http\Requests\Api\BaseRequest;

class UploadFileRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $folder = $this->folder;

        $rules = [
            'folder' => 'required'
        ];

        if ($this->has('image')) {
            $rules['image'] = 'required|image|max:20000';
        }

        if ($this->has('file')) {
            $rules['file'] = 'required|image|max:20';

            if ($folder == 'expenses') {
                $rules['file'] = 'required|mimes:csv,txt,xlx,xls,pdf,docx,txt,jpg,jpeg,svg,png|max:20000';
            }

            if ($folder == 'models' || $folder == 'patient-files') {
                $rules['file'] = 'required|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,svg,gif,bmp,webp,txt,csv|max:20000';
            }

            if ($folder == 'faxes') {
                $rules['file'] = 'required|mimes:pdf,tiff,tif|max:10240';
            }
        }



        return $rules;
    }
}
