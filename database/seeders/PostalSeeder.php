<?php

namespace Database\Seeders;

use App\Models\Postal;
use App\Models\Company;
use App\Models\MailType;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PostalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::first();
        $user = User::first();
        $mailType = MailType::where('company_id', $company->id)->first();

        if (!$company) {
            $this->command->error('No company found. Please run CompanyTableSeeder first.');
            return;
        }

        for ($i = 1; $i <= 8; $i++) {
            $isDispatched = $i % 2 == 0;

            $data = [
                'reference_no' => 'REF-' . strtoupper(Str::random(6)),
                'date' => now()->subDays(8 - $i)->format('Y-m-d'),
                'postal_type' => $isDispatched ? 'dispatched' : 'received',
                'received_by' => $user ? $user->id : null,
                'sender_name' => 'Sender ' . $i,
                'file' => null,
                'patient_id' => $user ? $user->id : null,
                'mail_type_id' => $mailType ? $mailType->id : null,
                'courier_method' => $isDispatched ? 'DHL' : 'Local Post',
                'tracking_number' => 'TRK' . mt_rand(100000, 999999),
                'status' => 'Pending',
                'company_id' => $company->id,
            ];

            // Conditionally include created_by only if column exists
            if (Schema::hasColumn('postals', 'created_by')) {
                $data['created_by'] = $user ? $user->id : null;
            }

            // Conditionally include FK columns if they exist in the DB schema
            if (Schema::hasColumn('postals', 'sender_by')) {
                $data['sender_by'] = $user ? $user->id : null;
            }

            if ($isDispatched) {
                if (Schema::hasColumn('postals', 'dispatched_by')) {
                    $data['dispatched_by'] = $user ? $user->id : null;
                }
                if (Schema::hasColumn('postals', 'sent_by')) {
                    $data['sent_by'] = $user ? $user->id : null;
                }
            }

            Postal::create($data);
        }

        $this->command->info('Postals seeded successfully.');
    }
}
