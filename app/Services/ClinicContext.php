<?php

namespace App\Services;

class ClinicContext
{
    /**
     * The ID of the current clinic.
     *
     * @var int|null
     */
    protected $clinicId;

    /**
     * Flag to indicate if we are in global mode (view all clinics).
     *
     * @var bool
     */
    protected $isGlobalMode = false;

    /**
     * Set the current clinic ID.
     *
     * @param int|string|null $id
     * @return void
     */
    public function setClinic($id): void
    {
        if ($id === 'all' || is_null($id)) {
            $this->isGlobalMode = true;
            $this->clinicId = null;
        } else {
            $this->isGlobalMode = false;
            $this->clinicId = $id;
        }
    }

    /**
     * Get the current clinic ID.
     *
     * @return int|null
     */
    public function getClinic(): ?int
    {
        return $this->clinicId;
    }

    /**
     * Check if a clinic context is set (specific clinic).
     *
     * @return bool
     */
    public function hasClinic(): bool
    {
        return !is_null($this->clinicId);
    }

    /**
     * Check if we are in global mode.
     *
     * @return bool
     */
    public function isGlobal(): bool
    {
        return $this->isGlobalMode;
    }
}