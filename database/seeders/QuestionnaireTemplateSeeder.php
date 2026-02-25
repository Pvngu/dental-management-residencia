<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\SuperAdmin\Models\QuestionnaireTemplate;
use App\SuperAdmin\Models\QuestionnaireSection;
use App\SuperAdmin\Models\Question;
use App\SuperAdmin\Models\QuestionOption;
use Illuminate\Support\Facades\DB;

class QuestionnaireTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Create Customer Service Questionnaire Template
            $template = QuestionnaireTemplate::create([
                'code' => 'CUSTOMER_SERVICE_V1',
                'version' => '1.0',
                'name' => 'Customer Service Satisfaction Survey',
                'description' => 'Comprehensive customer service satisfaction questionnaire to evaluate service quality, staff performance, and overall customer experience.',
                'is_active' => true,
                'is_evergreen' => true,
                'normative_ref' => 'ISO 9001:2015',
                'target_population' => 'ALL',
                'estimated_duration' => 15, // 15 minutes
                'config_json' => json_encode([
                    'allow_partial_submission' => true,
                    'randomize_questions' => false,
                    'show_progress_bar' => true,
                    'required_completion_rate' => 80
                ]),
                'created_by' => 1,
                'company_id' => null // SuperAdmin template
            ]);

            // Section 1: General Information
            $section1 = QuestionnaireSection::create([
                'template_id' => $template->id,
                'code' => 'GENERAL_INFO',
                'name' => 'General Information',
                'description' => 'Basic information about your visit and demographics',
                'instructions' => 'Please provide some basic information about your recent visit to help us better understand your experience.',
                'position' => 1,
                'is_required' => true,
                'skip_logic' => null,
                'company_id' => null
            ]);

            // Questions for Section 1
            $question1_1 = Question::create([
                'section_id' => $section1->id,
                'code' => 'VISIT_DATE',
                'prompt' => 'When did you visit our facility?',
                'response_type' => 'DATE',
                'position' => 1,
                'is_required' => true,
                'weight' => null,
                'skip_logic' => null,
                'validation_rules' => json_encode([
                    'max_date' => 'today',
                    'min_date' => '-30 days'
                ]),
                'metadata' => json_encode([
                    'category' => 'demographics',
                    'analytics_track' => true
                ]),
                'company_id' => null
            ]);

            $question1_2 = Question::create([
                'section_id' => $section1->id,
                'code' => 'SERVICE_TYPE',
                'prompt' => 'What type of service did you receive?',
                'response_type' => 'SINGLE_CHOICE',
                'position' => 2,
                'is_required' => true,
                'weight' => null,
                'skip_logic' => null,
                'validation_rules' => null,
                'metadata' => json_encode([
                    'category' => 'service_classification',
                    'analytics_track' => true
                ]),
                'company_id' => null
            ]);

            // Options for Service Type
            QuestionOption::create([
                'question_id' => $question1_2->id,
                'code' => 'CONSULTATION',
                'label' => 'Consultation',
                'value_numeric' => 1,
                'position' => 1,
                'response_tags' => json_encode(['service_type', 'consultation']),
                'company_id' => null
            ]);

            QuestionOption::create([
                'question_id' => $question1_2->id,
                'code' => 'TREATMENT',
                'label' => 'Treatment/Procedure',
                'value_numeric' => 2,
                'position' => 2,
                'response_tags' => json_encode(['service_type', 'treatment']),
                'company_id' => null
            ]);

            QuestionOption::create([
                'question_id' => $question1_2->id,
                'code' => 'EMERGENCY',
                'label' => 'Emergency Care',
                'value_numeric' => 3,
                'position' => 3,
                'response_tags' => json_encode(['service_type', 'emergency']),
                'company_id' => null
            ]);

            QuestionOption::create([
                'question_id' => $question1_2->id,
                'code' => 'FOLLOWUP',
                'label' => 'Follow-up Visit',
                'value_numeric' => 4,
                'position' => 4,
                'response_tags' => json_encode(['service_type', 'followup']),
                'company_id' => null
            ]);

            QuestionOption::create([
                'question_id' => $question1_2->id,
                'code' => 'OTHER',
                'label' => 'Other',
                'value_numeric' => 5,
                'position' => 5,
                'response_tags' => json_encode(['service_type', 'other']),
                'company_id' => null
            ]);

            // Section 2: Service Quality
            $section2 = QuestionnaireSection::create([
                'template_id' => $template->id,
                'code' => 'SERVICE_QUALITY',
                'name' => 'Service Quality Evaluation',
                'description' => 'Evaluation of the quality of service received',
                'instructions' => 'Please rate various aspects of the service you received during your visit.',
                'position' => 2,
                'is_required' => true,
                'skip_logic' => null,
                'company_id' => null
            ]);

            // Questions for Section 2
            $question2_1 = Question::create([
                'section_id' => $section2->id,
                'code' => 'OVERALL_SATISFACTION',
                'prompt' => 'How would you rate your overall satisfaction with the service received?',
                'response_type' => 'SINGLE_CHOICE',
                'position' => 1,
                'is_required' => true,
                'weight' => 25.0,
                'skip_logic' => null,
                'validation_rules' => null,
                'metadata' => json_encode([
                    'category' => 'satisfaction',
                    'analytics_track' => true,
                    'key_metric' => true
                ]),
                'company_id' => null
            ]);

            // Options for Overall Satisfaction (1-5 scale)
            QuestionOption::create([
                'question_id' => $question2_1->id,
                'code' => 'VERY_DISSATISFIED',
                'label' => 'Very Dissatisfied',
                'value_numeric' => 1,
                'position' => 1,
                'response_tags' => json_encode(['satisfaction', 'negative', 'critical']),
                'company_id' => null
            ]);

            QuestionOption::create([
                'question_id' => $question2_1->id,
                'code' => 'DISSATISFIED',
                'label' => 'Dissatisfied',
                'value_numeric' => 2,
                'position' => 2,
                'response_tags' => json_encode(['satisfaction', 'negative']),
                'company_id' => null
            ]);

            QuestionOption::create([
                'question_id' => $question2_1->id,
                'code' => 'NEUTRAL',
                'label' => 'Neutral',
                'value_numeric' => 3,
                'position' => 3,
                'response_tags' => json_encode(['satisfaction', 'neutral']),
                'company_id' => null
            ]);

            QuestionOption::create([
                'question_id' => $question2_1->id,
                'code' => 'SATISFIED',
                'label' => 'Satisfied',
                'value_numeric' => 4,
                'position' => 4,
                'response_tags' => json_encode(['satisfaction', 'positive']),
                'company_id' => null
            ]);

            QuestionOption::create([
                'question_id' => $question2_1->id,
                'code' => 'VERY_SATISFIED',
                'label' => 'Very Satisfied',
                'value_numeric' => 5,
                'position' => 5,
                'response_tags' => json_encode(['satisfaction', 'positive', 'excellent']),
                'company_id' => null
            ]);

            $question2_2 = Question::create([
                'section_id' => $section2->id,
                'code' => 'STAFF_PROFESSIONALISM',
                'prompt' => 'How would you rate the professionalism of our staff?',
                'response_type' => 'SINGLE_CHOICE',
                'position' => 2,
                'is_required' => true,
                'weight' => 20.0,
                'skip_logic' => null,
                'validation_rules' => null,
                'metadata' => json_encode([
                    'category' => 'staff_evaluation',
                    'analytics_track' => true
                ]),
                'company_id' => null
            ]);

            // Copy satisfaction scale options for staff professionalism
            foreach (['VERY_POOR', 'POOR', 'AVERAGE', 'GOOD', 'EXCELLENT'] as $index => $code) {
                $labels = ['Very Poor', 'Poor', 'Average', 'Good', 'Excellent'];
                $tags = [
                    ['staff', 'professionalism', 'negative', 'critical'],
                    ['staff', 'professionalism', 'negative'],
                    ['staff', 'professionalism', 'neutral'],
                    ['staff', 'professionalism', 'positive'],
                    ['staff', 'professionalism', 'positive', 'excellent']
                ];

                QuestionOption::create([
                    'question_id' => $question2_2->id,
                    'code' => $code,
                    'label' => $labels[$index],
                    'value_numeric' => $index + 1,
                    'position' => $index + 1,
                    'response_tags' => json_encode($tags[$index]),
                    'company_id' => null
                ]);
            }

            $question2_3 = Question::create([
                'section_id' => $section2->id,
                'code' => 'WAIT_TIME_SATISFACTION',
                'prompt' => 'How satisfied were you with the wait time?',
                'response_type' => 'SINGLE_CHOICE',
                'position' => 3,
                'is_required' => true,
                'weight' => 15.0,
                'skip_logic' => null,
                'validation_rules' => null,
                'metadata' => json_encode([
                    'category' => 'wait_time',
                    'analytics_track' => true
                ]),
                'company_id' => null
            ]);

            // Copy satisfaction scale options for wait time
            foreach (['VERY_DISSATISFIED', 'DISSATISFIED', 'NEUTRAL', 'SATISFIED', 'VERY_SATISFIED'] as $index => $code) {
                $labels = ['Very Dissatisfied', 'Dissatisfied', 'Neutral', 'Satisfied', 'Very Satisfied'];
                $tags = [
                    ['wait_time', 'negative', 'critical'],
                    ['wait_time', 'negative'],
                    ['wait_time', 'neutral'],
                    ['wait_time', 'positive'],
                    ['wait_time', 'positive', 'excellent']
                ];

                QuestionOption::create([
                    'question_id' => $question2_3->id,
                    'code' => $code,
                    'label' => $labels[$index],
                    'value_numeric' => $index + 1,
                    'position' => $index + 1,
                    'response_tags' => json_encode($tags[$index]),
                    'company_id' => null
                ]);
            }

            // Section 3: Communication
            $section3 = QuestionnaireSection::create([
                'template_id' => $template->id,
                'code' => 'COMMUNICATION',
                'name' => 'Communication Assessment',
                'description' => 'Evaluation of communication effectiveness',
                'instructions' => 'Please evaluate how well our staff communicated with you during your visit.',
                'position' => 3,
                'is_required' => true,
                'skip_logic' => null,
                'company_id' => null
            ]);

            $question3_1 = Question::create([
                'section_id' => $section3->id,
                'code' => 'COMMUNICATION_CLARITY',
                'prompt' => 'How clearly did the staff explain procedures and answer your questions?',
                'response_type' => 'SINGLE_CHOICE',
                'position' => 1,
                'is_required' => true,
                'weight' => 20.0,
                'skip_logic' => null,
                'validation_rules' => null,
                'metadata' => json_encode([
                    'category' => 'communication',
                    'analytics_track' => true
                ]),
                'company_id' => null
            ]);

            // Copy satisfaction scale for communication clarity
            foreach (['VERY_UNCLEAR', 'UNCLEAR', 'SOMEWHAT_CLEAR', 'CLEAR', 'VERY_CLEAR'] as $index => $code) {
                $labels = ['Very Unclear', 'Unclear', 'Somewhat Clear', 'Clear', 'Very Clear'];
                $tags = [
                    ['communication', 'clarity', 'negative', 'critical'],
                    ['communication', 'clarity', 'negative'],
                    ['communication', 'clarity', 'neutral'],
                    ['communication', 'clarity', 'positive'],
                    ['communication', 'clarity', 'positive', 'excellent']
                ];

                QuestionOption::create([
                    'question_id' => $question3_1->id,
                    'code' => $code,
                    'label' => $labels[$index],
                    'value_numeric' => $index + 1,
                    'position' => $index + 1,
                    'response_tags' => json_encode($tags[$index]),
                    'company_id' => null
                ]);
            }

            $question3_2 = Question::create([
                'section_id' => $section3->id,
                'code' => 'LANGUAGE_PREFERENCE',
                'prompt' => 'Was service provided in your preferred language?',
                'response_type' => 'BOOL',
                'position' => 2,
                'is_required' => true,
                'weight' => 10.0,
                'skip_logic' => null,
                'validation_rules' => null,
                'metadata' => json_encode([
                    'category' => 'communication',
                    'accessibility' => true
                ]),
                'company_id' => null
            ]);

            // Section 4: Facilities and Environment
            $section4 = QuestionnaireSection::create([
                'template_id' => $template->id,
                'code' => 'FACILITIES',
                'name' => 'Facilities and Environment',
                'description' => 'Assessment of physical facilities and environment',
                'instructions' => 'Please rate the physical environment and facilities where you received service.',
                'position' => 4,
                'is_required' => true,
                'skip_logic' => null,
                'company_id' => null
            ]);

            $question4_1 = Question::create([
                'section_id' => $section4->id,
                'code' => 'CLEANLINESS',
                'prompt' => 'How would you rate the cleanliness of the facilities?',
                'response_type' => 'SINGLE_CHOICE',
                'position' => 1,
                'is_required' => true,
                'weight' => 15.0,
                'skip_logic' => null,
                'validation_rules' => null,
                'metadata' => json_encode([
                    'category' => 'facilities',
                    'health_safety' => true
                ]),
                'company_id' => null
            ]);

            // Copy satisfaction scale for cleanliness
            foreach (['VERY_POOR', 'POOR', 'AVERAGE', 'GOOD', 'EXCELLENT'] as $index => $code) {
                $labels = ['Very Poor', 'Poor', 'Average', 'Good', 'Excellent'];
                $tags = [
                    ['facilities', 'cleanliness', 'negative', 'critical'],
                    ['facilities', 'cleanliness', 'negative'],
                    ['facilities', 'cleanliness', 'neutral'],
                    ['facilities', 'cleanliness', 'positive'],
                    ['facilities', 'cleanliness', 'positive', 'excellent']
                ];

                QuestionOption::create([
                    'question_id' => $question4_1->id,
                    'code' => $code,
                    'label' => $labels[$index],
                    'value_numeric' => $index + 1,
                    'position' => $index + 1,
                    'response_tags' => json_encode($tags[$index]),
                    'company_id' => null
                ]);
            }

            // Section 5: Feedback and Recommendations
            $section5 = QuestionnaireSection::create([
                'template_id' => $template->id,
                'code' => 'FEEDBACK',
                'name' => 'Additional Feedback',
                'description' => 'Open feedback and recommendations for improvement',
                'instructions' => 'Please share any additional comments, suggestions, or concerns you may have.',
                'position' => 5,
                'is_required' => false,
                'skip_logic' => null,
                'company_id' => null
            ]);

            $question5_1 = Question::create([
                'section_id' => $section5->id,
                'code' => 'RECOMMEND_FACILITY',
                'prompt' => 'Would you recommend our facility to friends and family?',
                'response_type' => 'SINGLE_CHOICE',
                'position' => 1,
                'is_required' => true,
                'weight' => 30.0,
                'skip_logic' => null,
                'validation_rules' => null,
                'metadata' => json_encode([
                    'category' => 'nps',
                    'key_metric' => true,
                    'analytics_track' => true
                ]),
                'company_id' => null
            ]);

            // NPS Scale Options (0-10)
            for ($i = 0; $i <= 10; $i++) {
                $tags = ['nps'];
                if ($i <= 6) {
                    $tags[] = 'detractor';
                } elseif ($i <= 8) {
                    $tags[] = 'passive';
                } else {
                    $tags[] = 'promoter';
                }

                QuestionOption::create([
                    'question_id' => $question5_1->id,
                    'code' => 'NPS_' . $i,
                    'label' => (string) $i,
                    'value_numeric' => $i,
                    'position' => $i + 1,
                    'response_tags' => json_encode($tags),
                    'company_id' => null
                ]);
            }

            $question5_2 = Question::create([
                'section_id' => $section5->id,
                'code' => 'ADDITIONAL_COMMENTS',
                'prompt' => 'Please share any additional comments or suggestions for improvement:',
                'response_type' => 'TEXT',
                'position' => 2,
                'is_required' => false,
                'weight' => null,
                'skip_logic' => null,
                'validation_rules' => json_encode([
                    'max_length' => 1000,
                    'min_length' => 0
                ]),
                'metadata' => json_encode([
                    'category' => 'feedback',
                    'text_analysis' => true,
                    'sentiment_analysis' => true
                ]),
                'company_id' => null
            ]);

            $question5_3 = Question::create([
                'section_id' => $section5->id,
                'code' => 'AREAS_IMPROVEMENT',
                'prompt' => 'Which areas do you think we should focus on improving? (Select all that apply)',
                'response_type' => 'MULTI_CHOICE',
                'position' => 3,
                'is_required' => false,
                'weight' => null,
                'skip_logic' => null,
                'validation_rules' => json_encode([
                    'min_selections' => 0,
                    'max_selections' => 5
                ]),
                'metadata' => json_encode([
                    'category' => 'improvement',
                    'analytics_track' => true
                ]),
                'company_id' => null
            ]);

            // Improvement areas options
            $improvementAreas = [
                ['WAIT_TIMES', 'Reduce wait times'],
                ['STAFF_TRAINING', 'Staff training and professionalism'],
                ['COMMUNICATION', 'Communication and explanation'],
                ['FACILITIES', 'Facility cleanliness and comfort'],
                ['APPOINTMENT_SCHEDULING', 'Appointment scheduling process'],
                ['FOLLOW_UP', 'Follow-up care and communication'],
                ['TECHNOLOGY', 'Technology and digital services'],
                ['ACCESSIBILITY', 'Accessibility and accommodation']
            ];

            foreach ($improvementAreas as $index => $area) {
                QuestionOption::create([
                    'question_id' => $question5_3->id,
                    'code' => $area[0],
                    'label' => $area[1],
                    'value_numeric' => $index + 1,
                    'position' => $index + 1,
                    'response_tags' => json_encode(['improvement', strtolower(str_replace('_', '', $area[0]))]),
                    'company_id' => null
                ]);
            }

            // Section 6: Incident Reporting (with complex skip_logic examples)
            $section6 = QuestionnaireSection::create([
                'template_id' => $template->id,
                'code' => 'INCIDENT_REPORTING',
                'name' => 'Incident Reporting',
                'description' => 'Report any incidents or issues that occurred during your visit',
                'instructions' => 'Please let us know if you experienced any incidents or issues during your visit. This section demonstrates complex conditional logic.',
                'position' => 6,
                'is_required' => false,
                'skip_logic' => null,
                'company_id' => null
            ]);

            // Question 6.1: General incident occurrence
            $question6_1 = Question::create([
                'section_id' => $section6->id,
                'code' => 'Q_HAD_INCIDENT',
                'prompt' => 'Did you experience any incidents or issues during your visit?',
                'response_type' => 'BOOL',
                'position' => 1,
                'is_required' => true,
                'weight' => null,
                'skip_logic' => null,
                'validation_rules' => null,
                'metadata' => json_encode([
                    'category' => 'incident',
                    'trigger_question' => true,
                    'analytics_track' => true
                ]),
                'company_id' => null
            ]);

            // Question 6.2: Types of incidents (Multiple choice, conditional)
            $question6_2 = Question::create([
                'section_id' => $section6->id,
                'code' => 'Q_INCIDENT_TYPES',
                'prompt' => 'What type of incidents did you experience? (Select all that apply)',
                'response_type' => 'MULTI_CHOICE',
                'position' => 2,
                'is_required' => false,
                'weight' => null,
                'skip_logic' => json_encode([
                    'show_if_any' => [
                        ['question_code' => 'Q_HAD_INCIDENT', 'value' => true]
                    ]
                ]),
                'validation_rules' => json_encode([
                    'min_selections' => 1,
                    'max_selections' => 10
                ]),
                'metadata' => json_encode([
                    'category' => 'incident',
                    'subcategory' => 'types',
                    'conditional' => true
                ]),
                'company_id' => null
            ]);

            // Incident type options
            $incidentTypes = [
                ['Q_EVT_ACCIDENTE', 'Accident or injury'],
                ['Q_EVT_ASALTO', 'Assault or violence'],
                ['Q_EVT_VIOLENCIA', 'Verbal violence or harassment'],
                ['Q_EVT_SECUESTRO', 'Kidnapping or detention'],
                ['Q_EVT_AMENAZA', 'Threats or intimidation'],
                ['Q_EVT_ROBO', 'Theft or robbery'],
                ['Q_EVT_DISCRIMINACION', 'Discrimination'],
                ['Q_EVT_NEGLIGENCIA', 'Medical negligence'],
                ['Q_EVT_EQUIPO', 'Equipment malfunction'],
                ['Q_EVT_OTRO', 'Other incident']
            ];

            foreach ($incidentTypes as $index => $incident) {
                QuestionOption::create([
                    'question_id' => $question6_2->id,
                    'code' => $incident[0],
                    'label' => $incident[1],
                    'value_numeric' => $index + 1,
                    'position' => $index + 1,
                    'response_tags' => json_encode(['incident', 'type', strtolower(str_replace(['Q_EVT_', '_'], ['', ''], $incident[0]))]),
                    'company_id' => null
                ]);
            }

            // Question 6.3: Severity assessment (conditional on serious incidents)
            $question6_3 = Question::create([
                'section_id' => $section6->id,
                'code' => 'Q_INCIDENT_SEVERITY',
                'prompt' => 'How would you rate the severity of the incident(s)?',
                'response_type' => 'SINGLE_CHOICE',
                'position' => 3,
                'is_required' => false,
                'weight' => null,
                'skip_logic' => json_encode([
                    'show_if_any' => [
                        ['question_code' => 'Q_EVT_ACCIDENTE', 'value' => true],
                        ['question_code' => 'Q_EVT_ASALTO', 'value' => true],
                        ['question_code' => 'Q_EVT_VIOLENCIA', 'value' => true],
                        ['question_code' => 'Q_EVT_SECUESTRO', 'value' => true],
                        ['question_code' => 'Q_EVT_AMENAZA', 'value' => true],
                        ['question_code' => 'Q_EVT_NEGLIGENCIA', 'value' => true]
                    ]
                ]),
                'validation_rules' => null,
                'metadata' => json_encode([
                    'category' => 'incident',
                    'subcategory' => 'severity',
                    'conditional' => true,
                    'serious_incidents_only' => true
                ]),
                'company_id' => null
            ]);

            // Severity options
            $severityLevels = [
                ['MINOR', 'Minor - No significant impact', 1],
                ['MODERATE', 'Moderate - Some impact but manageable', 2],
                ['SERIOUS', 'Serious - Significant impact', 3],
                ['SEVERE', 'Severe - Major impact requiring intervention', 4],
                ['CRITICAL', 'Critical - Life-threatening or extremely serious', 5]
            ];

            foreach ($severityLevels as $index => $severity) {
                QuestionOption::create([
                    'question_id' => $question6_3->id,
                    'code' => $severity[0],
                    'label' => $severity[1],
                    'value_numeric' => $severity[2],
                    'position' => $index + 1,
                    'response_tags' => json_encode(['incident', 'severity', strtolower($severity[0])]),
                    'company_id' => null
                ]);
            }

            // Question 6.4: Other incident description (conditional and required)
            $question6_4 = Question::create([
                'section_id' => $section6->id,
                'code' => 'Q_OTHER_INCIDENT_DESC',
                'prompt' => 'Please describe the other incident in detail:',
                'response_type' => 'TEXT',
                'position' => 4,
                'is_required' => false,
                'weight' => null,
                'skip_logic' => json_encode([
                    'show_if_any' => [
                        ['question_code' => 'Q_EVT_OTRO', 'value' => true]
                    ],
                    'required_if' => [
                        ['question_code' => 'Q_EVT_OTRO', 'value' => true]
                    ]
                ]),
                'validation_rules' => json_encode([
                    'min_length' => 10,
                    'max_length' => 1000
                ]),
                'metadata' => json_encode([
                    'category' => 'incident',
                    'subcategory' => 'description',
                    'conditional' => true,
                    'text_analysis' => true
                ]),
                'company_id' => null
            ]);

            // Question 6.5: Action taken by staff (conditional)
            $question6_5 = Question::create([
                'section_id' => $section6->id,
                'code' => 'Q_STAFF_ACTION',
                'prompt' => 'What action did our staff take regarding the incident?',
                'response_type' => 'SINGLE_CHOICE',
                'position' => 5,
                'is_required' => false,
                'weight' => null,
                'skip_logic' => json_encode([
                    'hide_if_all' => [
                        ['question_code' => 'Q_EVT_ACCIDENTE', 'value' => false],
                        ['question_code' => 'Q_EVT_ASALTO', 'value' => false],
                        ['question_code' => 'Q_EVT_VIOLENCIA', 'value' => false],
                        ['question_code' => 'Q_EVT_SECUESTRO', 'value' => false],
                        ['question_code' => 'Q_EVT_AMENAZA', 'value' => false],
                        ['question_code' => 'Q_EVT_ROBO', 'value' => false],
                        ['question_code' => 'Q_EVT_DISCRIMINACION', 'value' => false],
                        ['question_code' => 'Q_EVT_NEGLIGENCIA', 'value' => false],
                        ['question_code' => 'Q_EVT_EQUIPO', 'value' => false],
                        ['question_code' => 'Q_EVT_OTRO', 'value' => false]
                    ]
                ]),
                'validation_rules' => null,
                'metadata' => json_encode([
                    'category' => 'incident',
                    'subcategory' => 'staff_response',
                    'conditional' => true
                ]),
                'company_id' => null
            ]);

            // Staff action options
            $staffActions = [
                ['NO_ACTION', 'No action was taken', 1],
                ['ACKNOWLEDGED', 'Acknowledged but no specific action', 2],
                ['IMMEDIATE_HELP', 'Provided immediate assistance', 3],
                ['ESCALATED', 'Escalated to supervisor/manager', 4],
                ['DOCUMENTED', 'Documented the incident formally', 5],
                ['MEDICAL_CARE', 'Provided or arranged medical care', 6],
                ['SECURITY_CALLED', 'Called security or authorities', 7]
            ];

            foreach ($staffActions as $index => $action) {
                QuestionOption::create([
                    'question_id' => $question6_5->id,
                    'code' => $action[0],
                    'label' => $action[1],
                    'value_numeric' => $action[2],
                    'position' => $index + 1,
                    'response_tags' => json_encode(['incident', 'staff_action', strtolower(str_replace('_', '', $action[0]))]),
                    'company_id' => null
                ]);
            }

            // Question 6.6: Follow-up required (complex conditional logic)
            $question6_6 = Question::create([
                'section_id' => $section6->id,
                'code' => 'Q_FOLLOWUP_REQUIRED',
                'prompt' => 'Do you believe this incident requires follow-up action from our organization?',
                'response_type' => 'BOOL',
                'position' => 6,
                'is_required' => false,
                'weight' => null,
                'skip_logic' => json_encode([
                    'show_if_any' => [
                        ['question_code' => 'Q_EVT_ACCIDENTE', 'value' => true],
                        ['question_code' => 'Q_EVT_ASALTO', 'value' => true],
                        ['question_code' => 'Q_EVT_VIOLENCIA', 'value' => true],
                        ['question_code' => 'Q_EVT_NEGLIGENCIA', 'value' => true],
                        ['question_code' => 'Q_EVT_DISCRIMINACION', 'value' => true]
                    ],
                    'hide_if_all' => [
                        ['question_code' => 'Q_STAFF_ACTION', 'value' => 'SECURITY_CALLED'],
                        ['question_code' => 'Q_INCIDENT_SEVERITY', 'value' => 'MINOR']
                    ]
                ]),
                'validation_rules' => null,
                'metadata' => json_encode([
                    'category' => 'incident',
                    'subcategory' => 'followup',
                    'conditional' => true,
                    'complex_logic' => true
                ]),
                'company_id' => null
            ]);

            echo "Customer Service Questionnaire Template created successfully!\n";
            echo "Template ID: {$template->id}\n";
            echo "Template Code: {$template->code}\n";
            echo "Total Sections: 6\n";
            echo "Total Questions: " . Question::where('section_id', '>=', $section1->id)->count() . "\n";
        });
    }
}
