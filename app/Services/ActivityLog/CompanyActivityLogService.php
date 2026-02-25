<?php

namespace App\Services\ActivityLog;

use App\Models\Company;

class CompanyActivityLogService extends BaseActivityLogService
{
    /**
     * Log company creation
     */
    public function logCompanyCreated(Company $company)
    {
        return $this->logActivity(
            'CREATED',
            'companies',
            ActivityLogMessageProvider::getLocalizedMessage('company_created_detailed', [
                'companyName' => $company->name
            ]),
            $company->id
        );
    }

    /**
     * Log company update
     */
    public function logCompanyUpdated(Company $company)
    {
        $changes = $this->formatChanges($company);
        
        if (!empty($changes)) {
            return $this->logActivity(
                'UPDATED',
                'companies',
                ActivityLogMessageProvider::getLocalizedMessage('company_updated_detailed', [
                    'companyName' => $company->name,
                    'changes' => $changes
                ]),
                $company->id
            );
        }
        
        return null;
    }

    /**
     * Log company deletion
     */
    public function logCompanyDeleted(Company $company)
    {
        return $this->logActivity(
            'DELETED',
            'companies',
            ActivityLogMessageProvider::getLocalizedMessage('company_deleted_detailed', [
                'companyName' => $company->name
            ]),
            $company->id
        );
    }

    /**
     * Log company restoration
     */
    public function logCompanyRestored(Company $company)
    {
        return $this->logActivity(
            'RESTORED',
            'companies',
            ActivityLogMessageProvider::getLocalizedMessage('company_restored_detailed', [
                'companyName' => $company->name
            ]),
            $company->id
        );
    }

    /**
     * Log company force deletion
     */
    public function logCompanyForceDeleted(Company $company)
    {
        return $this->logActivity(
            'FORCE_DELETED',
            'companies',
            ActivityLogMessageProvider::getLocalizedMessage('company_force_deleted_detailed', [
                'companyName' => $company->name
            ]),
            $company->id
        );
    }
}