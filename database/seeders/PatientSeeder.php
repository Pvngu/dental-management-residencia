<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\User;
use App\Models\Company;
use App\Models\ClinicLocation;
use App\Models\Role;
use App\Models\UserAddress;
use App\Models\EmergencyContact;
use App\Models\PatientInsurance;
use App\Scopes\CompanyScope;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
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

        $patientRole = Role::withoutGlobalScope(CompanyScope::class)
            ->where('name', 'patient')->first();
            
        if (!$patientRole) {
            $this->command->error('No se encontró el rol de paciente. Por favor ejecute el seeder de roles primero.');
            return;
        }

        // Set company context for role assignment
        setPermissionsTeamId($company->id);

        $patients = [
            [
                'name' => 'María Elena',
                'last_name' => 'García López',
                'email' => 'maria.garcia@email.com',
                'phone' => '+525550001101',
                'country_code' => 'MX',
                'gender' => 'female',
                'date_of_birth' => '1985-03-15',
                'allergies' => 'Penicilina, Aspirina',
                'blood_type' => 'A+',
                'pharmacy_name' => 'Farmacia San Pablo',
                'pharmacy_phone' => '555-2001',
                'media_channels' => ['email', 'sms'],
                'address' => [
                    'address_line_1' => 'Calle Revolución 123',
                    'address_line_2' => 'Apartamento 4B',
                    'neighborhood' => 'Centro',
                    'postal_code' => '01000',
                    'city' => 'Ciudad de México',
                    'state' => 'CDMX',
                    'country_code' => 'MX',
                    'country_name' => 'México'
                ],
                'emergency_contact' => [
                    'name' => 'José García',
                    'phone' => '555-0102',
                    'relation' => 'Esposo'
                ]
            ],
            [
                'name' => 'Carlos Alberto',
                'last_name' => 'Rodríguez Mendoza',
                'email' => 'carlos.rodriguez@email.com',
                'phone' => '+15105550201',
                'country_code' => 'US',
                'gender' => 'male',
                'date_of_birth' => '1978-11-22',
                'allergies' => 'Ninguna conocida',
                'blood_type' => 'O-',
                'pharmacy_name' => 'Farmacia Guadalajara',
                'pharmacy_phone' => '555-2002',
                'media_channels' => ['email'],
                'address' => [
                    'address_line_1' => 'Avenida Insurgentes 456',
                    'address_line_2' => null,
                    'neighborhood' => 'Roma Norte',
                    'postal_code' => '01010',
                    'city' => 'Ciudad de México',
                    'state' => 'CDMX',
                    'country_code' => 'MX',
                    'country_name' => 'México'
                ],
                'emergency_contact' => [
                    'name' => 'Ana Mendoza',
                    'phone' => '555-0202',
                    'relation' => 'Esposa'
                ]
            ],
            [
                'name' => 'Ana Isabel',
                'last_name' => 'Martínez Herrera',
                'email' => 'ana.martinez@email.com',
                'phone' => '+525550000301',
                'country_code' => 'MX',
                'gender' => 'female',
                'date_of_birth' => '1992-07-08',
                'allergies' => 'Látex',
                'blood_type' => 'B+',
                'pharmacy_name' => 'Farmacia del Ahorro',
                'pharmacy_phone' => '555-2003',
                'media_channels' => ['email', 'sms', 'whatsapp'],
                'address' => [
                    'address_line_1' => 'Calle Madero 789',
                    'address_line_2' => 'Colonia Centro',
                    'neighborhood' => 'Centro Histórico',
                    'postal_code' => '01020',
                    'city' => 'Ciudad de México',
                    'state' => 'CDMX',
                    'country_code' => 'MX',
                    'country_name' => 'México'
                ],
                'emergency_contact' => [
                    'name' => 'Roberto Herrera',
                    'phone' => '555-0302',
                    'relation' => 'Padre'
                ]
            ],
            [
                'name' => 'Luis Fernando',
                'last_name' => 'Hernández Silva',
                'email' => 'luis.hernandez@email.com',
                'phone' => '+13235550401',
                'country_code' => 'US',
                'gender' => 'male',
                'date_of_birth' => '2010-12-03',
                'allergies' => 'Ninguna conocida',
                'blood_type' => 'AB+',
                'pharmacy_name' => 'Farmacia San Pablo',
                'pharmacy_phone' => '555-2001',
                'media_channels' => ['email'],
                'address' => [
                    'address_line_1' => 'Calle Hidalgo 321',
                    'address_line_2' => 'Casa 15',
                    'neighborhood' => 'Juárez',
                    'postal_code' => '01000',
                    'city' => 'Ciudad de México',
                    'state' => 'CDMX',
                    'country_code' => 'MX',
                    'country_name' => 'México'
                ],
                'emergency_contact' => [
                    'name' => 'Carmen Silva',
                    'phone' => '555-0402',
                    'relation' => 'Madre'
                ]
            ],
            [
                'name' => 'Rosa María',
                'last_name' => 'López Vázquez',
                'email' => 'rosa.lopez@email.com',
                'phone' => '+525550000501',
                'country_code' => 'MX',
                'gender' => 'female',
                'date_of_birth' => '1965-01-28',
                'allergies' => 'Sulfas, Yodo',
                'blood_type' => 'A-',
                'pharmacy_name' => 'Farmacia Benavides',
                'pharmacy_phone' => '555-2004',
                'media_channels' => ['phone'],
                'address' => [
                    'address_line_1' => 'Avenida Reforma 654',
                    'address_line_2' => 'Edificio Torre, Piso 3',
                    'neighborhood' => 'Zona Rosa',
                    'postal_code' => '01010',
                    'city' => 'Ciudad de México',
                    'state' => 'CDMX',
                    'country_code' => 'MX',
                    'country_name' => 'México'
                ],
                'emergency_contact' => [
                    'name' => 'Miguel López',
                    'phone' => '555-0502',
                    'relation' => 'Hijo'
                ]
            ],
            [
                'name' => 'Diego',
                'last_name' => 'Ramírez Torres',
                'email' => 'diego.ramirez@email.com',
                'phone' => '+12125550601',
                'country_code' => 'US',
                'gender' => 'male',
                'date_of_birth' => '1988-09-14',
                'allergies' => 'Ninguna conocida',
                'blood_type' => 'O+',
                'pharmacy_name' => 'Farmacia Guadalajara',
                'pharmacy_phone' => '555-2002',
                'media_channels' => ['email', 'sms'],
                'address' => [
                    'address_line_1' => 'Calle Juárez 987',
                    'address_line_2' => null,
                    'neighborhood' => 'Del Valle',
                    'postal_code' => '01020',
                    'city' => 'Ciudad de México',
                    'state' => 'CDMX',
                    'country_code' => 'MX',
                    'country_name' => 'México'
                ],
                'emergency_contact' => [
                    'name' => 'Laura Torres',
                    'phone' => '555-0602',
                    'relation' => 'Madre'
                ]
            ],
            [
                'name' => 'Patricia',
                'last_name' => 'Morales Castro',
                'email' => 'patricia.morales@email.com',
                'phone' => '+525550000701',
                'country_code' => 'MX',
                'gender' => 'female',
                'date_of_birth' => '1995-04-30',
                'allergies' => 'Fresas, Mariscos',
                'blood_type' => 'B-',
                'pharmacy_name' => 'Farmacia del Ahorro',
                'pharmacy_phone' => '555-2003',
                'media_channels' => ['email', 'sms', 'whatsapp'],
                'address' => [
                    'address_line_1' => 'Paseo de la Reforma 147',
                    'address_line_2' => 'Departamento 2A',
                    'neighborhood' => 'Polanco',
                    'postal_code' => '01000',
                    'city' => 'Ciudad de México',
                    'state' => 'CDMX',
                    'country_code' => 'MX',
                    'country_name' => 'México'
                ],
                'emergency_contact' => [
                    'name' => 'Ricardo Morales',
                    'phone' => '555-0702',
                    'relation' => 'Hermano'

                ]
            ],
            [
                'name' => 'Alejandro',
                'last_name' => 'Jiménez Ruiz',
                'email' => 'alejandro.jimenez@email.com',
                'phone' => '+14155550801',
                'country_code' => 'US',
                'gender' => 'male',
                'date_of_birth' => '1972-06-17',
                'allergies' => 'Anestesia local',
                'blood_type' => 'AB-',
                'pharmacy_name' => 'Farmacia San Pablo',
                'pharmacy_phone' => '555-2001',
                'media_channels' => ['email'],
                'address' => [
                    'address_line_1' => 'Calle 5 de Mayo 258',
                    'address_line_2' => 'Local B',
                    'neighborhood' => 'Centro',
                    'postal_code' => '01010',
                    'city' => 'Ciudad de México',
                    'state' => 'CDMX',
                    'country_code' => 'MX',
                    'country_name' => 'México'
                ],
                'emergency_contact' => [
                    'name' => 'Silvia Ruiz',
                    'phone' => '555-0802',
                    'relation' => 'Esposa'
                ]
            ]
        ];

        foreach ($patients as $patientData) {
            // Crear usuario para el paciente
            $user = new User();
            $user->company_id = $company->id;
            $user->name = $patientData['name'];
            $user->last_name = $patientData['last_name'];
            $user->email = $patientData['email'];
            $user->password = '12345678'; // Password por defecto
            $user->phone = $patientData['phone'];
            $user->country_code = $patientData['country_code'];
            $user->gender = $patientData['gender'];
            $user->date_of_birth = $patientData['date_of_birth'];
            $user->role_id = $patientRole->id;
            $user->user_type = "patients";
            $user->role_type = 'patient';
            $user->save();
            
            // Asignar rol
            $user->assignRole($patientRole);

            // Crear paciente
            $patient = new Patient();
            $patient->company_id = $company->id;
            // Assign random clinic
            $randomClinic = ClinicLocation::where('company_id', $company->id)->inRandomOrder()->first();
            $patient->home_clinic_id = $randomClinic ? $randomClinic->id : null;
            
            $patient->user_id = $user->id;
            $patient->allergies = $patientData['allergies'];
            $patient->blood_type = $patientData['blood_type'];
            $patient->pharmacy_name = $patientData['pharmacy_name'];
            $patient->pharmacy_phone = $patientData['pharmacy_phone'];
            $patient->media_channels = $patientData['media_channels'];
            // $patient->dental_chart = $this->generateDentalChart();
            $patient->save();

            // Crear dirección
            $this->createPatientAddress($patient, $patientData['address'], $company->id);

            // Crear contacto de emergencia
            $this->createEmergencyContact($patient, $patientData['emergency_contact'], $company->id);

            // Note: Insurance is now handled by dedicated InsuranceProviderSeeder and PatientInsuranceSeeder

            $this->command->info("Paciente creado: {$patientData['name']} {$patientData['last_name']}");
        }

        $this->command->info('¡Pacientes creados correctamente!');
    }

    /**
     * Crear dirección para un paciente
     */
    private function createPatientAddress(Patient $patient, array $addressData, int $companyId): void
    {
        $address = new \App\Models\UserAddress();
        $address->user_id = $patient->user_id;
        $address->address_line_1 = $addressData['address_line_1'];
        $address->address_line_2 = $addressData['address_line_2'];
        $address->neighborhood = $addressData['neighborhood'] ?? null;
        $address->postal_code = $addressData['postal_code'] ?? null;
        $address->city = $addressData['city'] ?? null;
        $address->state = $addressData['state'] ?? null;
        $address->country_code = $addressData['country_code'] ?? 'MX';
        $address->country_name = $addressData['country_name'] ?? 'México';
        $address->address_type = 'home';
        $address->is_default = true;
        $address->status = true;
        $address->company_id = $companyId;
        $address->save();
    }

    /**
     * Crear contacto de emergencia para un paciente
     */
    private function createEmergencyContact(Patient $patient, array $contactData, int $companyId): void
    {
        $emergencyContact = new EmergencyContact();
        $emergencyContact->patient_id = $patient->id;
        $emergencyContact->name = $contactData['name'];
        $emergencyContact->phone = $contactData['phone'];
        $emergencyContact->relation = $contactData['relation'];
        $emergencyContact->company_id = $companyId;
        $emergencyContact->save();
    }

    /**
     * Generate dental chart (simulated)
     */
    private function generateDentalChart(): array
    {
        $conditions = ['healthy', 'cavity', 'filled', 'crown', 'missing'];
        $dentalChart = [];

        // Generate status for each tooth (1-32 adult numbering)
        for ($tooth = 1; $tooth <= 32; $tooth++) {
            $dentalChart[$tooth] = [
                'condition' => $conditions[array_rand($conditions)],
                'notes' => rand(0, 3) == 0 ? 'Requires follow-up' : null,
                'last_treatment' => rand(0, 4) == 0 ? date('Y-m-d', strtotime('-' . rand(1, 365) . ' days')) : null
            ];
        }

        return $dentalChart;
    }
}