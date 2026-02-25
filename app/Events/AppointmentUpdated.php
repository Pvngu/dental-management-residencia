<?php

namespace App\Events;

use App\Models\Appointment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AppointmentUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $appointment;
    public $action;

    /**
     * Create a new event instance.
     *
     * @param Appointment $appointment
     * @param string $action (created, updated, deleted, status_changed)
     */
    public function __construct(Appointment $appointment, string $action = 'updated')
    {
        $this->appointment = $appointment->load([
            'patient.user',
            'doctor.user',
            'room',
            'treatmentType',
            'prescription.prescriptionItems.medicine'
        ]);
        $this->action = $action;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('company.' . $this->appointment->company_id),
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'appointment.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'appointment' => $this->appointment->toArray(),
            'action' => $this->action,
            'timestamp' => now()->toISOString(),
        ];
    }
}
