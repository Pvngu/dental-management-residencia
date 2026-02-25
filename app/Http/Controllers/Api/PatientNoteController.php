<?php

namespace App\Http\Controllers\Api;

use App\Models\PatientNote;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Http\Requests\Api\PatientNote\IndexRequest;
use App\Http\Requests\Api\PatientNote\StoreRequest;
use App\Http\Requests\Api\PatientNote\UpdateRequest;
use App\Http\Requests\Api\PatientNote\DeleteRequest;
use Illuminate\Http\Request;
use App\Classes\Common;
use App\Models\Company;

class PatientNoteController extends ApiBaseController
{
    use CompanyTraits;

    protected $model = PatientNote::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query) {
        $request = request();
        return $query;
    }

    public function storing($note)
    {
        $company = company();
        $user = user();
        $note->user_id = $user->id;
        $note->company_id = $company->id;
        return $note;
    }

    public function highlight($xid)
    {
        $id = Common::getIdFromHash($xid);
        $note = PatientNote::withoutGlobalScopes()->where('id', $id)->firstOrFail();
        $note->is_highlighted = !$note->is_highlighted; // Toggle highlight status
        $note->save();
        
        $message = $note->is_highlighted ? 'Note highlighted successfully.' : 'Note highlight removed successfully.';
        return $this->respondSuccess(['message' => $message]);
    }
}
