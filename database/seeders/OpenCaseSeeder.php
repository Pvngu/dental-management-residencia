<?php

namespace Database\Seeders;

use App\Models\OpenCase;
use App\Models\Patient;
use App\Models\Company;
use App\Models\ClinicLocation;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Carbon\Carbon;

class OpenCaseSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $this->command->info('🚨 Seeding Open Cases...');

        $faker = Faker::create();
        $companies = Company::all();

        if ($companies->isEmpty()) {
            $this->command->warn('No companies found. Skipping OpenCase seeding.');
            return;
        }

        $totalCases = 0;

        foreach ($companies as $company) {
            $patients = Patient::where('company_id', $company->id)->get();
            $clinics = ClinicLocation::where('company_id', $company->id)->get();
            
            if ($patients->isEmpty()) {
                $this->command->warn("No patients found for company {$company->name}. Skipping.");
                continue;
            }

            if ($clinics->isEmpty()) {
                $this->command->warn("No clinics found for company {$company->name}. Will create cases without clinic assignment.");
            }

            // Generate 30-50 open cases per company (increased from 15-25)
            $casesCount = $faker->numberBetween(30, 50);

            // Define realistic dental case templates with expanded scenarios
            $caseTemplates = [
                // Critical Priority Cases (25% of cases)
                [
                    'priority' => 'critical',
                    'weight' => 25,
                    'titles' => [
                        'Severe Tooth Pain - Emergency',
                        'Dental Abscess - Requires Immediate Attention',
                        'Broken Crown with Sharp Edges',
                        'Severe Gum Bleeding',
                        'Post-Surgery Complications',
                        'Facial Swelling - Possible Cellulitis',
                        'Trauma - Knocked Out Tooth',
                        'Severe TMJ Lock - Cannot Open Mouth',
                        'Allergic Reaction to Dental Material',
                        'Uncontrolled Bleeding Post-Extraction'
                    ],
                    'descriptions' => [
                        'Patient experiencing severe, throbbing tooth pain rated 9/10. Unable to sleep or eat. Possible infection requiring immediate intervention.',
                        'Visible swelling and pus formation around tooth #{tooth}. Patient reports fever (101°F) and difficulty swallowing.',
                        'Crown fell off and remaining tooth structure has sharp edges cutting tongue and cheek. Risk of further tissue damage.',
                        'Patient reports continuous bleeding from gums for 24+ hours after routine cleaning. Unable to control with pressure.',
                        'Patient experiencing unusual swelling and severe pain 3 days post wisdom tooth extraction. Possible dry socket.',
                        'Significant facial swelling on left side extending to eye area. Patient reports difficulty opening mouth and high fever.',
                        'Sports injury resulted in avulsed central incisor. Tooth brought in milk. Time-sensitive for successful reimplantation.',
                        'Patient unable to open mouth beyond 5mm. Severe TMJ dysfunction following extensive dental work. Pain level 8/10.',
                        'Patient developed severe oral swelling and difficulty breathing after new filling placement. Possible material allergy.',
                        'Continuous bleeding 6 hours post multiple extractions. Patient taking blood thinners. May need hospital evaluation.'
                    ]
                ],
                // High Priority Cases (30% of cases)
                [
                    'priority' => 'high',
                    'weight' => 30,
                    'titles' => [
                        'Failed Root Canal - Needs Retreatment',
                        'Loose Filling - Risk of Loss',
                        'Orthodontic Emergency - Wire Breakage',
                        'Persistent Jaw Pain - TMJ Issues',
                        'Chipped Front Tooth - Cosmetic Concern',
                        'Crown Preparation Exposed - Temporary Lost',
                        'Periodontal Abscess Formation',
                        'Denture Broke - Unable to Eat',
                        'Implant Loosening - Needs Assessment',
                        'Bridge Failure - Abutment Issues'
                    ],
                    'descriptions' => [
                        'Root canal performed 6 months ago showing signs of failure. X-ray reveals persistent apical infection. Retreatment recommended.',
                        'Large amalgam filling in molar #{tooth} is loose and causing discomfort during chewing. Risk of aspiration if lost.',
                        'Orthodontic archwire has snapped and is causing significant irritation to inner cheek. Multiple ulcerations present.',
                        'Patient reports TMJ-like symptoms with audible clicking and pain when opening mouth wide. Affecting daily activities.',
                        'Cosmetic concern - front tooth chipped during sports activity. Patient very self-conscious, affecting work confidence.',
                        'Temporary crown lost on prepared tooth #{tooth}. Tooth sensitivity extreme and patient unable to chew on that side.',
                        'Localized gum abscess with pus discharge. Patient reports metallic taste and localized throbbing pain.',
                        'Upper denture fractured in half during eating. Patient unable to eat solid foods and speech is affected.',
                        'Dental implant #{tooth} showing mobility and patient reports pain when chewing. May need immediate attention.',
                        '3-unit bridge on lower left showing mobility of abutment tooth. Patient reports food impaction and bad taste.'
                    ]
                ],
                // Medium Priority Cases (35% of cases) 
                [
                    'priority' => 'medium',
                    'weight' => 35,
                    'titles' => [
                        'Routine Cleaning Overdue',
                        'Small Cavity Detected - Needs Filling',
                        'Teeth Whitening Consultation',
                        'Mild Gum Recession Monitoring',
                        'Wisdom Tooth Evaluation',
                        'Night Guard Adjustment Needed',
                        'Fluoride Treatment Recommended',
                        'Minor Crown Sensitivity',
                        'Orthodontic Progress Check',
                        'Sealant Replacement Required',
                        'Dental X-rays Due for Update',
                        'Gum Disease Follow-up'
                    ],
                    'descriptions' => [
                        'Patient is 8 months overdue for routine cleaning and examination. No immediate pain reported but plaque buildup evident.',
                        'Small cavity detected on tooth #{tooth} during routine examination. Schedule filling before progression to larger restoration.',
                        'Patient interested in professional teeth whitening options. Needs consultation to discuss in-office vs take-home procedures.',
                        'Mild gum recession observed on lower front teeth. Monitor progression and discuss treatment options including grafting.',
                        'Wisdom teeth are partially erupted and causing mild discomfort. Monitor for impaction or need for prophylactic extraction.',
                        'Custom night guard causing pressure points and patient discomfort. Needs adjustment for better fit and comfort.',
                        'Patient at high risk for caries due to dry mouth medication. Recommend professional fluoride treatment regimen.',
                        'Patient reports mild sensitivity in crowned tooth #{tooth} when drinking cold beverages. Monitor for complications.',
                        'Orthodontic patient due for routine progress evaluation and wire adjustment. Treatment proceeding as planned.',
                        'Sealant on molar #{tooth} appears to be partially missing. Replacement recommended to prevent caries formation.',
                        'Patient overdue for routine radiographs. Last full mouth series taken over 3 years ago. Update needed for diagnosis.',
                        'Patient with history of periodontal disease due for 3-month maintenance cleaning and pocket depth evaluation.'
                    ]
                ],
                // Low Priority Cases (10% of cases)
                [
                    'priority' => 'low',
                    'weight' => 10,
                    'titles' => [
                        'Dental Health Education Needed',
                        'Preventive Care Discussion',
                        'Insurance Coverage Review',
                        'Oral Hygiene Improvement Plan',
                        'Diet Counseling for Dental Health',
                        'Tobacco Cessation Counseling',
                        'Special Needs Patient Care Plan',
                        'Geriatric Dental Care Assessment',
                        'Pediatric Dental Education',
                        'Pregnancy Dental Care Guidance'
                    ],
                    'descriptions' => [
                        'Patient shows signs of poor oral hygiene with significant plaque accumulation. Needs comprehensive education on proper brushing and flossing techniques.',
                        'Discuss comprehensive preventive measures to avoid future dental problems based on patient\'s specific risk factors and history.',
                        'Review patient insurance coverage and explain benefits for upcoming extensive treatments. Prior authorization may be needed.',
                        'Patient has significant plaque buildup and early signs of gingivitis. Develop personalized home care routine improvement plan.',
                        'Patient consumes high-sugar diet significantly affecting dental health. Provide comprehensive nutritional counseling for oral health.',
                        'Long-term smoker interested in quitting. Discuss oral health benefits of tobacco cessation and available resources.',
                        'Special needs patient requires modified care approach. Develop individualized care plan considering medical history and limitations.',
                        'Elderly patient with multiple medical conditions needs geriatric-focused dental care plan considering medication interactions.',
                        'Young patient (age 6) needs age-appropriate dental education and introduction to preventive care concepts.',
                        'Pregnant patient in second trimester needs guidance on safe dental care during pregnancy and post-delivery oral health.'
                    ]
                ]
            ];

            $statuses = ['open', 'in_progress', 'resolved', 'closed'];
            $statusWeights = [45, 35, 15, 5]; // More open cases, fewer resolved/closed

            for ($i = 0; $i < $casesCount; $i++) {
                $patient = $patients->random();
                
                // Select priority template based on weights
                $totalWeight = array_sum(array_column($caseTemplates, 'weight'));
                $randomWeight = $faker->numberBetween(1, $totalWeight);
                $currentWeight = 0;
                $selectedTemplate = $caseTemplates[0]; // fallback
                
                foreach ($caseTemplates as $template) {
                    $currentWeight += $template['weight'];
                    if ($randomWeight <= $currentWeight) {
                        $selectedTemplate = $template;
                        break;
                    }
                }
                
                $title = $faker->randomElement($selectedTemplate['titles']);
                $description = $faker->randomElement($selectedTemplate['descriptions']);
                
                // Replace {tooth} placeholder with random tooth number (1-32)
                $description = str_replace('{tooth}', $faker->numberBetween(1, 32), $description);
                
                // Select status with weights favoring open/in_progress for more realism
                $status = $faker->randomElement($statuses, $statusWeights);
                
                // Assign clinic randomly if available
                $selectedClinic = $clinics->isNotEmpty() ? $clinics->random() : null;
                
                // Adjust creation date based on status for realism
                $createdDate = match($status) {
                    'closed' => $faker->dateTimeBetween('-6 months', '-2 weeks'),
                    'resolved' => $faker->dateTimeBetween('-3 months', '-1 week'), 
                    'in_progress' => $faker->dateTimeBetween('-2 months', '-1 day'),
                    'open' => $faker->dateTimeBetween('-1 month', 'now'),
                };

                $openCase = OpenCase::create([
                    'title' => $title,
                    'description' => $description,
                    'priority' => $selectedTemplate['priority'],
                    'status' => $status,
                    'patient_id' => $patient->id,
                    'company_id' => $company->id,
                    'clinic_id' => $selectedClinic ? $selectedClinic->id : null,
                    'created_at' => $createdDate,
                    'updated_at' => $faker->dateTimeBetween($createdDate, 'now'),
                ]);

                $totalCases++;

                // Add specialized emergency cases occasionally
                if ($i % 8 == 0 && $faker->boolean(60)) { // 60% chance every 8th case
                    $emergencyCases = [
                        [
                            'title' => 'After-Hours Emergency Call - Tooth #{tooth} Pain',
                            'description' => 'Patient called after-hours emergency line reporting severe tooth pain that started suddenly. Unable to sleep and over-the-counter pain medication ineffective.',
                            'priority' => 'critical',
                            'status' => 'open'
                        ],
                        [
                            'title' => 'Walk-in Patient - Dental Trauma',
                            'description' => 'Walk-in patient with dental trauma from motor vehicle accident. Multiple teeth affected, possible jaw fracture.',
                            'priority' => 'critical', 
                            'status' => 'in_progress'
                        ],
                        [
                            'title' => 'Pediatric Dental Emergency - Age {age}',
                            'description' => 'Child fell at playground and injured front teeth. Parent very concerned about permanent damage to developing teeth.',
                            'priority' => 'high',
                            'status' => 'open'
                        ],
                        [
                            'title' => 'Post-Op Complication - Dry Socket',
                            'description' => 'Patient reports severe pain 3 days after wisdom tooth extraction. Likely dry socket formation requiring immediate attention.',
                            'priority' => 'high',
                            'status' => 'in_progress'
                        ]
                    ];
                    
                    $emergencyCase = $faker->randomElement($emergencyCases);
                    
                    // Replace placeholders
                    $emergencyTitle = str_replace('{tooth}', $faker->numberBetween(1, 32), $emergencyCase['title']);
                    $emergencyTitle = str_replace('{age}', $faker->numberBetween(4, 12), $emergencyTitle);
                    $emergencyDescription = str_replace('{age}', $faker->numberBetween(4, 12), $emergencyCase['description']);
                    
                    OpenCase::create([
                        'title' => $emergencyTitle,
                        'description' => $emergencyDescription,
                        'priority' => $emergencyCase['priority'],
                        'status' => $emergencyCase['status'],
                        'patient_id' => $patients->random()->id,
                        'company_id' => $company->id,
                        'clinic_id' => $selectedClinic ? $selectedClinic->id : null,
                        'created_at' => $faker->dateTimeBetween('-1 week', 'now'),
                        'updated_at' => $faker->dateTimeBetween('-1 week', 'now'),
                    ]);
                    $totalCases++;
                }
            }

            $clinicCount = $clinics->count();
            $this->command->info("   Created {$casesCount}+ open cases for company: {$company->name} across {$clinicCount} clinics");
        }

        $this->command->info("✅ OpenCase seeding completed! Total cases created: {$totalCases}");
        
        // Show comprehensive summary statistics
        $this->showSummaryStats();
    }

    /**
     * Display comprehensive summary statistics of created open cases
     */
    private function showSummaryStats()
    {
        $this->command->info("\n📊 Open Cases Summary:");
        
        $totalCases = OpenCase::count();
        $this->command->info("   Total Cases: {$totalCases}");
        
        // By Priority
        $priorities = OpenCase::selectRaw('priority, count(*) as count')
            ->groupBy('priority')
            ->orderByRaw("FIELD(priority, 'critical', 'high', 'medium', 'low')")
            ->get();
            
        $this->command->info("   By Priority:");
        foreach ($priorities as $priority) {
            $percentage = round(($priority->count / $totalCases) * 100, 1);
            $emoji = match($priority->priority) {
                'critical' => '🔴',
                'high' => '🟠', 
                'medium' => '🟡',
                'low' => '🟢',
                default => '⚪'
            };
            $this->command->info("     {$emoji} {$priority->priority}: {$priority->count} ({$percentage}%)");
        }
        
        // By Status
        $statuses = OpenCase::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->orderBy('count', 'desc')
            ->get();
            
        $this->command->info("   By Status:");
        foreach ($statuses as $status) {
            $percentage = round(($status->count / $totalCases) * 100, 1);
            $emoji = match($status->status) {
                'open' => '🔓',
                'in_progress' => '⏳',
                'resolved' => '✅',
                'closed' => '🔒',
                default => '❓'
            };
            $this->command->info("     {$emoji} {$status->status}: {$status->count} ({$percentage}%)");
        }
        
        // By Clinic Distribution
        $clinicStats = OpenCase::selectRaw('clinic_id, count(*) as count')
            ->whereNotNull('clinic_id')
            ->groupBy('clinic_id')
            ->get();
        
        if ($clinicStats->isNotEmpty()) {
            $this->command->info("   By Clinic Distribution:");
            foreach ($clinicStats as $stat) {
                $clinic = ClinicLocation::find($stat->clinic_id);
                $clinicName = $clinic ? $clinic->name : 'Unknown Clinic';
                $percentage = round(($stat->count / $totalCases) * 100, 1);
                $this->command->info("     🏥 {$clinicName}: {$stat->count} ({$percentage}%)");
            }
        }
        
        // Critical/High priority open cases (urgent attention needed)
        $urgentCases = OpenCase::whereIn('priority', ['critical', 'high'])
            ->whereIn('status', ['open', 'in_progress'])
            ->count();
            
        $this->command->info("🚨 Urgent Cases (Critical/High + Open/In Progress): {$urgentCases}");
        
        // Cases created in last 7 days
        $recentCases = OpenCase::where('created_at', '>=', Carbon::now()->subDays(7))->count();
        $this->command->info("📅 Cases Created in Last 7 Days: {$recentCases}");
    }
}
