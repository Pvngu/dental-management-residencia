<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\SuperAdmin\Models\QuestionnaireTemplate;
use App\SuperAdmin\Models\QuestionnaireSection;
use App\SuperAdmin\Models\Question;
use App\SuperAdmin\Models\QuestionOption;
use Illuminate\Support\Facades\DB;

class QuestionnaireSampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->createCustomerServiceTemplate();
            $this->createEmployeeSatisfactionTemplate();
            $this->createPatientExperienceTemplate();
        });
    }

    /**
     * Create Customer Service Satisfaction Template
     */
    private function createCustomerServiceTemplate()
    {
        $this->command->info('Creating Customer Service Satisfaction Template...');
        
        $template = QuestionnaireTemplate::create([
            'code' => 'CUSTOMER_SERVICE_V2',
            'version' => '2.0',
            'name' => 'Customer Service Excellence Survey',
            'description' => 'Enhanced customer service satisfaction questionnaire focusing on service quality, staff performance, and customer experience improvement.',
            'is_active' => true,
            'is_evergreen' => true,
            'normative_ref' => 'ISO 9001:2015',
            'target_population' => 'ALL',
            'estimated_duration' => 12,
            'config_json' => json_encode([
                'allow_partial_submission' => true,
                'randomize_questions' => false,
                'show_progress_bar' => true,
                'required_completion_rate' => 75,
                'theme' => 'customer_service'
            ]),
            'created_by' => 1,
            'company_id' => null
        ]);

        // Service Quality Section
        $section = QuestionnaireSection::create([
            'template_id' => $template->id,
            'code' => 'SERVICE_QUALITY',
            'name' => 'Service Quality Assessment',
            'description' => 'Rate the quality of service you received',
            'instructions' => 'Please rate your experience with various aspects of our service.',
            'position' => 1,
            'is_required' => true,
            'company_id' => null
        ]);

        $this->createRatingQuestion($section, 'OVERALL_EXPERIENCE', 'How would you rate your overall experience?', 1, 30.0, 'satisfaction');
        $this->createRatingQuestion($section, 'STAFF_KNOWLEDGE', 'How knowledgeable was our staff?', 2, 25.0, 'staff');
        $this->createRatingQuestion($section, 'PROBLEM_RESOLUTION', 'How effectively were your issues resolved?', 3, 25.0, 'resolution');
        $this->createNPSQuestion($section, 'RECOMMEND_SERVICE', 'How likely are you to recommend our service?', 4, 20.0);

        $this->command->info('✅ Customer Service Template created');
    }

    /**
     * Create Employee Satisfaction Template
     */
    private function createEmployeeSatisfactionTemplate()
    {
        $this->command->info('Creating Employee Satisfaction Template...');
        
        $template = QuestionnaireTemplate::create([
            'code' => 'EMPLOYEE_SAT_V1',
            'version' => '1.0',
            'name' => 'Employee Satisfaction Survey',
            'description' => 'Annual employee satisfaction and engagement survey to measure workplace satisfaction, job fulfillment, and organizational culture.',
            'is_active' => true,
            'is_evergreen' => false,
            'normative_ref' => 'HR-001',
            'target_population' => 'EMPLOYEES',
            'estimated_duration' => 20,
            'config_json' => json_encode([
                'allow_partial_submission' => true,
                'randomize_questions' => false,
                'show_progress_bar' => true,
                'required_completion_rate' => 80,
                'anonymous' => true,
                'theme' => 'employee_satisfaction'
            ]),
            'created_by' => 1,
            'company_id' => null
        ]);

        // Work Environment Section
        $section1 = QuestionnaireSection::create([
            'template_id' => $template->id,
            'code' => 'WORK_ENVIRONMENT',
            'name' => 'Work Environment',
            'description' => 'Your workplace environment and conditions',
            'instructions' => 'Please rate various aspects of your work environment.',
            'position' => 1,
            'is_required' => true,
            'company_id' => null
        ]);

        $this->createRatingQuestion($section1, 'WORKSPACE_COMFORT', 'How comfortable is your workspace?', 1, 15.0, 'environment');
        $this->createRatingQuestion($section1, 'WORK_LIFE_BALANCE', 'How satisfied are you with your work-life balance?', 2, 20.0, 'balance');
        $this->createRatingQuestion($section1, 'MANAGEMENT_SUPPORT', 'How supportive is your immediate manager?', 3, 25.0, 'management');

        // Career Development Section
        $section2 = QuestionnaireSection::create([
            'template_id' => $template->id,
            'code' => 'CAREER_DEVELOPMENT',
            'name' => 'Career Development',
            'description' => 'Professional growth and development opportunities',
            'instructions' => 'Please evaluate the career development opportunities available to you.',
            'position' => 2,
            'is_required' => true,
            'company_id' => null
        ]);

        $this->createRatingQuestion($section2, 'GROWTH_OPPORTUNITIES', 'How satisfied are you with growth opportunities?', 1, 20.0, 'growth');
        $this->createRatingQuestion($section2, 'TRAINING_QUALITY', 'How would you rate the quality of training provided?', 2, 15.0, 'training');
        $this->createNPSQuestion($section2, 'RECOMMEND_EMPLOYER', 'How likely are you to recommend this company as a place to work?', 3, 25.0);

        // Add Workplace Issues Section with skip_logic examples
        $this->createWorkplaceIssuesSection($template);

        $this->command->info('✅ Employee Satisfaction Template created');
    }

    /**
     * Create Patient Experience Template
     */
    private function createPatientExperienceTemplate()
    {
        $this->command->info('Creating Patient Experience Template...');
        
        $template = QuestionnaireTemplate::create([
            'code' => 'PATIENT_EXP_V1',
            'version' => '1.0',
            'name' => 'Patient Experience Survey',
            'description' => 'Comprehensive patient experience survey focusing on clinical care, communication, and overall satisfaction with medical services.',
            'is_active' => true,
            'is_evergreen' => true,
            'normative_ref' => 'HCAHPS',
            'target_population' => 'PATIENTS',
            'estimated_duration' => 18,
            'config_json' => json_encode([
                'allow_partial_submission' => true,
                'randomize_questions' => false,
                'show_progress_bar' => true,
                'required_completion_rate' => 85,
                'theme' => 'healthcare'
            ]),
            'created_by' => 1,
            'company_id' => null
        ]);

        // Clinical Care Section
        $section1 = QuestionnaireSection::create([
            'template_id' => $template->id,
            'code' => 'CLINICAL_CARE',
            'name' => 'Clinical Care Quality',
            'description' => 'Quality of medical care and treatment received',
            'instructions' => 'Please rate the quality of clinical care you received.',
            'position' => 1,
            'is_required' => true,
            'company_id' => null
        ]);

        $this->createRatingQuestion($section1, 'TREATMENT_EFFECTIVENESS', 'How effective was your treatment?', 1, 30.0, 'clinical');
        $this->createRatingQuestion($section1, 'DOCTOR_EXPERTISE', 'How would you rate your doctor\'s expertise?', 2, 25.0, 'clinical');
        $this->createRatingQuestion($section1, 'PAIN_MANAGEMENT', 'How well was your pain managed?', 3, 20.0, 'clinical');

        // Communication Section
        $section2 = QuestionnaireSection::create([
            'template_id' => $template->id,
            'code' => 'COMMUNICATION',
            'name' => 'Communication with Staff',
            'description' => 'Quality of communication with medical staff',
            'instructions' => 'Please evaluate how well our staff communicated with you.',
            'position' => 2,
            'is_required' => true,
            'company_id' => null
        ]);

        $this->createRatingQuestion($section2, 'DOCTOR_COMMUNICATION', 'How well did doctors communicate with you?', 1, 15.0, 'communication');
        $this->createRatingQuestion($section2, 'NURSE_COMMUNICATION', 'How well did nurses communicate with you?', 2, 10.0, 'communication');

        $this->command->info('✅ Patient Experience Template created');
    }

    /**
     * Helper method to create rating questions (1-5 scale)
     */
    private function createRatingQuestion($section, $code, $prompt, $position, $weight, $category)
    {
        $question = Question::create([
            'section_id' => $section->id,
            'code' => $code,
            'prompt' => $prompt,
            'response_type' => 'SINGLE_CHOICE',
            'position' => $position,
            'is_required' => true,
            'weight' => $weight,
            'metadata' => json_encode([
                'category' => $category,
                'analytics_track' => true
            ]),
            'company_id' => null
        ]);

        $options = [
            ['VERY_POOR', 'Very Poor', 1, 'negative'],
            ['POOR', 'Poor', 2, 'negative'],
            ['AVERAGE', 'Average', 3, 'neutral'],
            ['GOOD', 'Good', 4, 'positive'],
            ['EXCELLENT', 'Excellent', 5, 'positive']
        ];

        foreach ($options as $index => $option) {
            QuestionOption::create([
                'question_id' => $question->id,
                'code' => $option[0],
                'label' => $option[1],
                'value_numeric' => $option[2],
                'position' => $index + 1,
                'response_tags' => json_encode([$category, 'rating', $option[3]]),
                'company_id' => null
            ]);
        }

        return $question;
    }

    /**
     * Helper method to create NPS questions (0-10 scale)
     */
    private function createNPSQuestion($section, $code, $prompt, $position, $weight)
    {
        $question = Question::create([
            'section_id' => $section->id,
            'code' => $code,
            'prompt' => $prompt,
            'response_type' => 'SINGLE_CHOICE',
            'position' => $position,
            'is_required' => true,
            'weight' => $weight,
            'metadata' => json_encode([
                'category' => 'nps',
                'analytics_track' => true,
                'key_metric' => true
            ]),
            'company_id' => null
        ]);

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
                'question_id' => $question->id,
                'code' => 'NPS_' . $i,
                'label' => (string) $i,
                'value_numeric' => $i,
                'position' => $i + 1,
                'response_tags' => json_encode($tags),
                'company_id' => null
            ]);
        }

        return $question;
    }

    /**
     * Create Workplace Issues section with complex skip_logic examples
     */
    private function createWorkplaceIssuesSection($template)
    {
        $this->command->info('Adding Workplace Issues section with skip_logic...');
        
        $section = QuestionnaireSection::create([
            'template_id' => $template->id,
            'code' => 'WORKPLACE_ISSUES',
            'name' => 'Workplace Issues and Concerns',
            'description' => 'Confidential reporting of workplace issues',
            'instructions' => 'This section allows you to confidentially report any workplace issues. Your responses will help us improve the work environment.',
            'position' => 3,
            'is_required' => false,
            'company_id' => null
        ]);

        // Question 1: Has experienced workplace issues
        $question1 = Question::create([
            'section_id' => $section->id,
            'code' => 'Q_HAS_WORKPLACE_ISSUES',
            'prompt' => 'Have you experienced any workplace issues in the past 12 months?',
            'response_type' => 'BOOL',
            'position' => 1,
            'is_required' => true,
            'weight' => null,
            'skip_logic' => null,
            'metadata' => json_encode([
                'category' => 'workplace_issues',
                'confidential' => true,
                'trigger_question' => true
            ]),
            'company_id' => null
        ]);

        // Question 2: Types of workplace issues (conditional multi-choice)
        $question2 = Question::create([
            'section_id' => $section->id,
            'code' => 'Q_ISSUE_TYPES',
            'prompt' => 'What types of workplace issues have you experienced? (Select all that apply)',
            'response_type' => 'MULTI_CHOICE',
            'position' => 2,
            'is_required' => false,
            'weight' => null,
            'skip_logic' => json_encode([
                'show_if_any' => [
                    ['question_code' => 'Q_HAS_WORKPLACE_ISSUES', 'value' => true]
                ]
            ]),
            'validation_rules' => json_encode([
                'min_selections' => 1,
                'max_selections' => 8
            ]),
            'metadata' => json_encode([
                'category' => 'workplace_issues',
                'subcategory' => 'types',
                'confidential' => true,
                'conditional' => true
            ]),
            'company_id' => null
        ]);

        // Issue type options
        $issueTypes = [
            ['Q_ISSUE_HARASSMENT', 'Sexual harassment'],
            ['Q_ISSUE_DISCRIMINATION', 'Discrimination (race, gender, age, etc.)'],
            ['Q_ISSUE_BULLYING', 'Workplace bullying'],
            ['Q_ISSUE_UNSAFE_CONDITIONS', 'Unsafe working conditions'],
            ['Q_ISSUE_WAGE_THEFT', 'Wage theft or payment issues'],
            ['Q_ISSUE_OVERWORK', 'Excessive workload or overtime'],
            ['Q_ISSUE_RETALIATION', 'Retaliation for complaints'],
            ['Q_ISSUE_OTHER', 'Other workplace issue']
        ];

        foreach ($issueTypes as $index => $issue) {
            QuestionOption::create([
                'question_id' => $question2->id,
                'code' => $issue[0],
                'label' => $issue[1],
                'value_numeric' => $index + 1,
                'position' => $index + 1,
                'response_tags' => json_encode(['workplace_issues', 'type', strtolower(str_replace(['Q_ISSUE_', '_'], ['', ''], $issue[0]))]),
                'company_id' => null
            ]);
        }

        // Question 3: Severity assessment (conditional on serious issues)
        $question3 = Question::create([
            'section_id' => $section->id,
            'code' => 'Q_ISSUE_SEVERITY',
            'prompt' => 'How would you rate the severity of these workplace issues?',
            'response_type' => 'SINGLE_CHOICE',
            'position' => 3,
            'is_required' => false,
            'weight' => null,
            'skip_logic' => json_encode([
                'show_if_any' => [
                    ['question_code' => 'Q_ISSUE_HARASSMENT', 'value' => true],
                    ['question_code' => 'Q_ISSUE_DISCRIMINATION', 'value' => true],
                    ['question_code' => 'Q_ISSUE_BULLYING', 'value' => true],
                    ['question_code' => 'Q_ISSUE_UNSAFE_CONDITIONS', 'value' => true],
                    ['question_code' => 'Q_ISSUE_RETALIATION', 'value' => true]
                ]
            ]),
            'metadata' => json_encode([
                'category' => 'workplace_issues',
                'subcategory' => 'severity',
                'confidential' => true,
                'conditional' => true
            ]),
            'company_id' => null
        ]);

        // Severity options for workplace issues
        $severityOptions = [
            ['MINOR_ANNOYANCE', 'Minor annoyance - occasional occurrence', 1],
            ['MODERATE_CONCERN', 'Moderate concern - affects work performance', 2],
            ['SERIOUS_PROBLEM', 'Serious problem - significantly impacts well-being', 3],
            ['SEVERE_ISSUE', 'Severe issue - creates hostile work environment', 4],
            ['CRITICAL_SAFETY', 'Critical - threatens safety or legal violations', 5]
        ];

        foreach ($severityOptions as $index => $severity) {
            QuestionOption::create([
                'question_id' => $question3->id,
                'code' => $severity[0],
                'label' => $severity[1],
                'value_numeric' => $severity[2],
                'position' => $index + 1,
                'response_tags' => json_encode(['workplace_issues', 'severity', strtolower(str_replace('_', '', $severity[0]))]),
                'company_id' => null
            ]);
        }

        // Question 4: Other issue description (conditional and required)
        $question4 = Question::create([
            'section_id' => $section->id,
            'code' => 'Q_OTHER_ISSUE_DESC',
            'prompt' => 'Please describe the other workplace issue in detail:',
            'response_type' => 'TEXT',
            'position' => 4,
            'is_required' => false,
            'weight' => null,
            'skip_logic' => json_encode([
                'show_if_any' => [
                    ['question_code' => 'Q_ISSUE_OTHER', 'value' => true]
                ],
                'required_if' => [
                    ['question_code' => 'Q_ISSUE_OTHER', 'value' => true]
                ]
            ]),
            'validation_rules' => json_encode([
                'min_length' => 20,
                'max_length' => 1500
            ]),
            'metadata' => json_encode([
                'category' => 'workplace_issues',
                'subcategory' => 'description',
                'confidential' => true,
                'conditional' => true,
                'text_analysis' => true
            ]),
            'company_id' => null
        ]);

        // Question 5: Reported to management (complex conditional)
        $question5 = Question::create([
            'section_id' => $section->id,
            'code' => 'Q_REPORTED_TO_MGMT',
            'prompt' => 'Have you reported these issues to management?',
            'response_type' => 'SINGLE_CHOICE',
            'position' => 5,
            'is_required' => false,
            'weight' => null,
            'skip_logic' => json_encode([
                'hide_if_all' => [
                    ['question_code' => 'Q_ISSUE_HARASSMENT', 'value' => false],
                    ['question_code' => 'Q_ISSUE_DISCRIMINATION', 'value' => false],
                    ['question_code' => 'Q_ISSUE_BULLYING', 'value' => false],
                    ['question_code' => 'Q_ISSUE_UNSAFE_CONDITIONS', 'value' => false],
                    ['question_code' => 'Q_ISSUE_WAGE_THEFT', 'value' => false],
                    ['question_code' => 'Q_ISSUE_OVERWORK', 'value' => false],
                    ['question_code' => 'Q_ISSUE_RETALIATION', 'value' => false],
                    ['question_code' => 'Q_ISSUE_OTHER', 'value' => false]
                ]
            ]),
            'metadata' => json_encode([
                'category' => 'workplace_issues',
                'subcategory' => 'reporting',
                'confidential' => true,
                'conditional' => true
            ]),
            'company_id' => null
        ]);

        // Reporting status options
        $reportingOptions = [
            ['NOT_REPORTED', 'No, I have not reported these issues', 1],
            ['REPORTED_NO_ACTION', 'Yes, but no action was taken', 2],
            ['REPORTED_INSUFFICIENT', 'Yes, but the response was insufficient', 3],
            ['REPORTED_SATISFIED', 'Yes, and I was satisfied with the response', 4],
            ['AFRAID_TO_REPORT', 'I was afraid to report due to potential retaliation', 5]
        ];

        foreach ($reportingOptions as $index => $option) {
            QuestionOption::create([
                'question_id' => $question5->id,
                'code' => $option[0],
                'label' => $option[1],
                'value_numeric' => $option[2],
                'position' => $index + 1,
                'response_tags' => json_encode(['workplace_issues', 'reporting', strtolower(str_replace('_', '', $option[0]))]),
                'company_id' => null
            ]);
        }

        // Question 6: Need support (conditional with complex logic)
        $question6 = Question::create([
            'section_id' => $section->id,
            'code' => 'Q_NEED_SUPPORT',
            'prompt' => 'Do you need support or resources to address these workplace issues?',
            'response_type' => 'BOOL',
            'position' => 6,
            'is_required' => false,
            'weight' => null,
            'skip_logic' => json_encode([
                'show_if_any' => [
                    ['question_code' => 'Q_ISSUE_HARASSMENT', 'value' => true],
                    ['question_code' => 'Q_ISSUE_DISCRIMINATION', 'value' => true],
                    ['question_code' => 'Q_ISSUE_BULLYING', 'value' => true],
                    ['question_code' => 'Q_ISSUE_RETALIATION', 'value' => true]
                ],
                'hide_if_all' => [
                    ['question_code' => 'Q_REPORTED_TO_MGMT', 'value' => 'REPORTED_SATISFIED']
                ]
            ]),
            'metadata' => json_encode([
                'category' => 'workplace_issues',
                'subcategory' => 'support',
                'confidential' => true,
                'conditional' => true,
                'priority_flag' => true
            ]),
            'company_id' => null
        ]);

        $this->command->info('✅ Workplace Issues section with skip_logic added');
    }
}
