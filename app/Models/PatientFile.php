<?php

namespace App\Models;

use App\Casts\Hash;
use App\Classes\Common;
use App\Scopes\CompanyScope;
use App\Models\BaseModel;

class PatientFile extends BaseModel
{
    protected $table = 'patient_files';

    protected $hidden = ['id'];
    
    protected $default = ["xid", "name", "file", "file_type", "file_size", "created_by_id", "updated_by_id"];

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $appends = ['xid', 'x_patient_id', 'x_created_by_id', 'file_url'];

    protected $filterable = ['patient_id',];

    protected $hashableGetterFunctions = [
        'getXPatientIdAttribute' => 'patient_id',
        'getXCreatedByIdAttribute' => 'created_by_id',
    ];

    protected $casts = [
        'patient_id' => Hash::class . ':hash',
        'user_id' => Hash::class . ':hash',
        'created_by_id' => Hash::class . ':hash',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function getFileUrlAttribute()
    {
        $patientFilesPath = Common::getFolderPath('patientFilesPath');

        return $this->file == null ? null : Common::getFileUrl($patientFilesPath, $this->file);
    }

    public function patient() {
        return $this->belongsTo(patient::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_id', 'id')->withoutGlobalScopes();
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by_id', 'id')->withoutGlobalScopes();
    }
}