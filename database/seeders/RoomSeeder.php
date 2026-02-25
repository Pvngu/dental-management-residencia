<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\RoomType;
use App\Models\Company;
use App\Models\ClinicLocation;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::first();

        if (!$company) {
            $this->command->error('No se encontró ninguna compañía. Por favor ejecute el seeder de compañías primero.');
            return;
        }

        $clinics = ClinicLocation::where('company_id', $company->id)->get();

        if ($clinics->isEmpty()) {
            $this->command->error('No se encontró ninguna clínica. Por favor ejecute el seeder de clínicas primero.');
            return;
        }

        $roomTypes = RoomType::all();

        if ($roomTypes->isEmpty()) {
            $this->command->error('No se encontraron tipos de sala. Por favor ejecute el seeder de tipos de sala primero.');
            return;
        }

        $rooms = [
            // Consultorios Generales - Planta Baja (0)
            [
                'name' => 'Consultorio A',
                'room_type' => 'Consultorio General',
                'floor' => 0,
                'capacity' => 3,
                'status' => 'Available',
                'notes' => 'Consultorio principal con vista a la calle, ideal para primeras consultas'
            ],
            [
                'name' => 'Consultorio B',
                'room_type' => 'Consultorio General',
                'floor' => 0,
                'capacity' => 3,
                'status' => 'Available',
                'notes' => 'Consultorio equipado con sillón dental eléctrico y luz natural'
            ],
            [
                'name' => 'Consultorio C',
                'room_type' => 'Consultorio General',
                'floor' => 0,
                'capacity' => 2,
                'status' => 'Occupied',
                'notes' => 'Consultorio más pequeño, ideal para consultas de seguimiento'
            ],

            // Consultorios de Especialidades - Primer Piso (1)
            [
                'name' => 'Ortodoncia 1',
                'room_type' => 'Consultorio de Especialidades',
                'floor' => 1,
                'capacity' => 4,
                'status' => 'Available',
                'notes' => 'Equipado con microscopio y herramientas especializadas para ortodoncia'
            ],
            [
                'name' => 'Endodoncia 1',
                'room_type' => 'Consultorio de Especialidades',
                'floor' => 1,
                'capacity' => 3,
                'status' => 'Reserved',
                'notes' => 'Consultorio con equipamiento para tratamientos de conducto'
            ],
            [
                'name' => 'Periodoncia 1',
                'room_type' => 'Consultorio de Especialidades',
                'floor' => 1,
                'capacity' => 3,
                'status' => 'Available',
                'notes' => 'Especializado en tratamientos periodontales y cirugía de encías'
            ],

            // Quirófanos y Salas Especiales - Primer Piso (1)
            [
                'name' => 'Quirófano Principal',
                'room_type' => 'Quirófano Dental',
                'floor' => 1,
                'capacity' => 6,
                'status' => 'Available',
                'notes' => 'Quirófano completamente equipado para cirugías orales y maxilofaciales'
            ],
            [
                'name' => 'Quirófano Menor',
                'room_type' => 'Quirófano Dental',
                'floor' => 1,
                'capacity' => 4,
                'status' => 'Maintenance',
                'notes' => 'En mantenimiento preventivo - Disponible desde la próxima semana'
            ],

            // Radiología - Planta Baja (0)
            [
                'name' => 'Sala de Rayos X',
                'room_type' => 'Sala de Radiología',
                'floor' => 0,
                'capacity' => 2,
                'status' => 'Available',
                'notes' => 'Equipada con equipo de rayos X digital y panorámico'
            ],
            [
                'name' => 'Tomografía',
                'room_type' => 'Sala de Radiología',
                'floor' => 0,
                'capacity' => 2,
                'status' => 'Available',
                'notes' => 'Tomógrafo 3D para estudios imagenológicos avanzados'
            ],

            // Higiene y Profilaxis - Planta Baja (0)
            [
                'name' => 'Higiene 1',
                'room_type' => 'Sala de Higiene',
                'floor' => 0,
                'capacity' => 2,
                'status' => 'Available',
                'notes' => 'Sala dedicada exclusivamente a limpiezas dentales'
            ],
            [
                'name' => 'Higiene 2',
                'room_type' => 'Sala de Higiene',
                'floor' => 0,
                'capacity' => 2,
                'status' => 'Occupied',
                'notes' => 'Segunda sala de higiene con equipo de ultrasonido'
            ],

            // Pediatría - Planta Baja (0)
            [
                'name' => 'Consultorio Infantil',
                'room_type' => 'Consultorio Pediátrico',
                'floor' => 0,
                'capacity' => 4,
                'status' => 'Available',
                'notes' => 'Decorado especialmente para niños con colores alegres y juguetes'
            ],

            // Laboratorio - Segundo Piso (2)
            [
                'name' => 'Laboratorio Principal',
                'room_type' => 'Laboratorio Dental',
                'floor' => 2,
                'capacity' => 3,
                'status' => 'Available',
                'notes' => 'Laboratorio equipado para confección de prótesis y aparatos dentales'
            ],

            // Esterilización - Segundo Piso (2)
            [
                'name' => 'Esterilización Central',
                'room_type' => 'Sala de Esterilización',
                'floor' => 2,
                'capacity' => 2,
                'status' => 'Available',
                'notes' => 'Central de esterilización con autoclaves y equipos de desinfección'
            ],

            // Recuperación - Primer Piso (1)
            [
                'name' => 'Recuperación 1',
                'room_type' => 'Sala de Recuperación',
                'floor' => 1,
                'capacity' => 4,
                'status' => 'Available',
                'notes' => 'Sala cómoda para recuperación post-operatoria con camas reclinables'
            ],
            [
                'name' => 'Recuperación 2',
                'room_type' => 'Sala de Recuperación',
                'floor' => 1,
                'capacity' => 2,
                'status' => 'Available',
                'notes' => 'Sala de recuperación más privada para procedimientos menores'
            ],

            // Urgencias - Planta Baja (0)
            [
                'name' => 'Urgencias',
                'room_type' => 'Sala de Urgencias',
                'floor' => 0,
                'capacity' => 3,
                'status' => 'Available',
                'notes' => 'Sala de emergencias dentales disponible 24/7 con acceso independiente'
            ]
        ];

        foreach ($clinics as $clinic) {
            $this->command->info("Creando salas para la clínica: {$clinic->name}");

            foreach ($rooms as $roomData) {
                $roomType = $roomTypes->where('name', $roomData['room_type'])->first();

                if (!$roomType) {
                    $this->command->warn("No se encontró el tipo de sala: {$roomData['room_type']}");
                    continue;
                }

                // Check if room already exists to avoid duplicates
                $exists = Room::where('company_id', $company->id)
                    ->where('clinic_id', $clinic->id)
                    ->where('name', $roomData['name'])
                    ->exists();

                if ($exists) {
                     // $this->command->warn("La sala {$roomData['name']} ya existe en la clínica {$clinic->name}");
                    continue;
                }

                $room = new Room();
                $room->company_id = $company->id;
                $room->clinic_id = $clinic->id;
                $room->name = $roomData['name'];
                $room->room_type_id = $roomType->id;
                $room->floor = $roomData['floor'];
                $room->capacity = $roomData['capacity'];
                $room->status = $roomData['status'];
                $room->notes = $roomData['notes'];
                $room->save();

                $this->command->info("Sala creada: {$roomData['name']} - {$roomData['room_type']}");
            }
        }

        $this->command->info('¡Salas creadas correctamente!');
    }
}