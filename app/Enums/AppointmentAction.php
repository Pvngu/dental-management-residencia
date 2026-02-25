<?php
namespace App\Enums;

enum AppointmentAction: string
{
    case BOOKED = 'booked';
    case RESCHEDULED = 'rescheduled';
    case DOCTOR_CHANGED = 'doctor_changed';
    case STATUS_CHANGED = 'status_changed';
    case CANCELLED = 'cancelled';
}