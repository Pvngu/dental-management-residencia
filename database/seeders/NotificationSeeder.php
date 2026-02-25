<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;
use App\Models\Appointment;
use Carbon\Carbon;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first user with doctor role or any user
        $user = User::whereHas('roles', function ($q) {
            $q->where('name', 'doctor');
        })->first();

        // Fallback to any user if no doctor found
        if (!$user) {
            $user = User::first();
        }

        if (!$user) {
            $this->command->error('No users found. Please create users first.');
            return;
        }

        $companyId = $user->company_id;

        $this->command->info('Creating sample notifications for user: ' . $user->name);

        // Sample notification 1: Appointment Created
        Notification::create([
            'user_id' => $user->id,
            'type' => 'appointment_created',
            'data' => [
                'message' => "Nueva cita programada: 'Limpieza dental para María López' el " . Carbon::now()->addDays(1)->format('d/m/Y') . " a las 09:00",
                'url' => url("/admin/appointments"),
                'appointment_id' => 1,
                'appointment_xid' => 'sample001',
                'titulo' => "Limpieza dental para María López",
                'fecha' => Carbon::now()->addDays(1)->format('Y-m-d'),
                'hora_inicio' => "09:00",
                'hora_fin' => "09:45",
                'tipo' => "Consulta General",
                'paciente_id' => 1,
                'paciente_nombre' => "María López",
                'doctor_nombre' => $user->name,
                'ubicacion' => "Consultorio 1"
            ],
            'is_read' => false,
            'is_important' => false,
            'company_id' => $companyId,
            'created_at' => Carbon::now()->subHours(2),
        ]);

        // Sample notification 2: Appointment Cancelled (Important)
        Notification::create([
            'user_id' => $user->id,
            'type' => 'appointment_cancelled',
            'data' => [
                'message' => "Cita cancelada: 'Extracción de muela para Carlos Méndez' originalmente el " . Carbon::now()->addDays(2)->format('d/m/Y') . " a las 11:00",
                'url' => url("/admin/appointments"),
                'appointment_id' => 2,
                'appointment_xid' => 'sample002',
                'titulo' => "Extracción de muela para Carlos Méndez",
                'fecha' => Carbon::now()->addDays(2)->format('Y-m-d'),
                'hora_inicio' => "11:00",
                'hora_fin' => "11:30",
                'tipo' => "Urgencia",
                'paciente_id' => 2,
                'paciente_nombre' => "Carlos Méndez",
                'doctor_nombre' => $user->name,
                'ubicacion' => "Consultorio 3"
            ],
            'is_read' => false,
            'is_important' => true,
            'company_id' => $companyId,
            'created_at' => Carbon::now()->subHours(1),
        ]);

        // Sample notification 3: Follow-up Reminder
        Notification::create([
            'user_id' => $user->id,
            'type' => 'followup_reminder',
            'data' => [
                'message' => "Recordatorio: 'Revisión post-tratamiento para Ana García' el " . Carbon::now()->addDays(3)->format('d/m/Y') . " a las 10:00",
                'url' => url("/admin/appointments"),
                'appointment_id' => 3,
                'appointment_xid' => 'sample003',
                'titulo' => "Revisión post-tratamiento para Ana García",
                'fecha' => Carbon::now()->addDays(3)->format('Y-m-d'),
                'hora_inicio' => "10:00",
                'hora_fin' => "10:30",
                'tipo' => "Seguimiento",
                'paciente_id' => 3,
                'paciente_nombre' => "Ana García",
                'doctor_nombre' => $user->name,
                'ubicacion' => "Consultorio 2"
            ],
            'is_read' => false,
            'is_important' => false,
            'company_id' => $companyId,
            'created_at' => Carbon::now()->subMinutes(30),
        ]);

        // Sample notification 4: Patient Checked In (Important)
        Notification::create([
            'user_id' => $user->id,
            'type' => 'patient_checked_in',
            'data' => [
                'message' => "El paciente 'Diego Ramírez Torres' ha llegado para su cita de 'Implante Dental' programada a las 19:30",
                'url' => url("/admin/appointments"),
                'appointment_id' => 4,
                'appointment_xid' => 'sample004',
                'titulo' => "Implante Dental para Diego Ramírez Torres",
                'fecha' => Carbon::now()->format('Y-m-d'),
                'hora_inicio' => "19:30",
                'hora_fin' => "21:00",
                'tipo' => "Cirugía Oral",
                'paciente_id' => 4,
                'paciente_nombre' => "Diego Ramírez Torres",
                'doctor_nombre' => $user->name,
                'ubicacion' => "Consultorio 5"
            ],
            'is_read' => false,
            'is_important' => true,
            'company_id' => $companyId,
            'created_at' => Carbon::now()->subMinutes(15),
        ]);

        // Sample notification 5: Appointment Completed (Already Read)
        Notification::create([
            'user_id' => $user->id,
            'type' => 'appointment_completed',
            'data' => [
                'message' => "Cita completada: 'Retirada de Brackets para Diego Ramírez Torres' finalizada a las 15:30",
                'url' => url("/admin/appointments"),
                'appointment_id' => 5,
                'appointment_xid' => 'sample005',
                'titulo' => "Retirada de Brackets para Diego Ramírez Torres",
                'fecha' => Carbon::now()->subDays(1)->format('Y-m-d'),
                'hora_inicio' => "14:30",
                'hora_fin' => "15:30",
                'tipo' => "Ortodoncia",
                'paciente_id' => 5,
                'paciente_nombre' => "Diego Ramírez Torres",
                'doctor_nombre' => $user->name,
                'ubicacion' => "Consultorio 5"
            ],
            'is_read' => true,
            'is_important' => false,
            'read_at' => Carbon::now()->subHours(3),
            'company_id' => $companyId,
            'created_at' => Carbon::now()->subDays(1),
        ]);

        // Sample notification 6: Appointment Rescheduled
        Notification::create([
            'user_id' => $user->id,
            'type' => 'appointment_rescheduled',
            'data' => [
                'message' => "La cita de 'Incrustación para Ana Isabel Martínez Herrera' ha sido reprogramada al " . Carbon::now()->addDays(5)->format('d/m/Y') . " a las 18:00",
                'url' => url("/admin/appointments"),
                'appointment_id' => 6,
                'appointment_xid' => 'sample006',
                'titulo' => "Incrustación para Ana Isabel Martínez Herrera",
                'fecha' => Carbon::now()->addDays(5)->format('Y-m-d'),
                'hora_inicio' => "18:00",
                'hora_fin' => "19:30",
                'tipo' => "Odontología General",
                'paciente_id' => 6,
                'paciente_nombre' => "Ana Isabel Martínez Herrera",
                'doctor_nombre' => $user->name,
                'ubicacion' => "Consultorio 2"
            ],
            'is_read' => false,
            'is_important' => false,
            'company_id' => $companyId,
            'created_at' => Carbon::now()->subMinutes(45),
        ]);

        // Create more notifications for pagination testing
        for ($i = 7; $i <= 20; $i++) {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'appointment_created',
                'data' => [
                    'message' => "Nueva cita programada: 'Consulta general para Paciente $i' el " . Carbon::now()->addDays($i)->format('d/m/Y') . " a las " . sprintf('%02d:00', 9 + ($i % 8)),
                    'url' => url("/admin/appointments"),
                    'appointment_id' => $i,
                    'appointment_xid' => 'sample' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'titulo' => "Consulta general para Paciente $i",
                    'fecha' => Carbon::now()->addDays($i)->format('Y-m-d'),
                    'hora_inicio' => sprintf('%02d:00', 9 + ($i % 8)),
                    'hora_fin' => sprintf('%02d:45', 9 + ($i % 8)),
                    'tipo' => "Consulta General",
                    'paciente_id' => $i,
                    'paciente_nombre' => "Paciente $i",
                    'doctor_nombre' => $user->name,
                    'ubicacion' => "Consultorio " . (($i % 5) + 1)
                ],
                'is_read' => $i > 15 ? true : false,
                'is_important' => false,
                'read_at' => $i > 15 ? Carbon::now()->subDays(rand(1, 3)) : null,
                'company_id' => $companyId,
                'created_at' => Carbon::now()->subHours(rand(1, 48)),
            ]);
        }

        $this->command->info('Successfully created 20 sample notifications!');
        $this->command->info('5 unread notifications (including 2 important)');
        $this->command->info('15 read notifications');
    }
}

