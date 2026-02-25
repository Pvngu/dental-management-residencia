<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\SuperAdmin\Models\QuestionnaireTemplate;
use App\SuperAdmin\Models\QuestionnaireSection;
use App\SuperAdmin\Models\Question;
use App\SuperAdmin\Models\QuestionOption;
use Illuminate\Support\Facades\DB;

class QuestionnaireSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * This seeder can be used to add additional sections to existing templates
     */
    public function run(): void
    {
        $this->command->info('🔧 Adding additional sections to existing questionnaire templates...');
        
        DB::transaction(function () {
            // Find customer service template and add additional sections
            $customerServiceTemplate = QuestionnaireTemplate::where('code', 'CUSTOMER_SERVICE_V1')->first();
            if ($customerServiceTemplate) {
                $this->addDigitalExperienceSection($customerServiceTemplate);
                $this->addAccessibilitySection($customerServiceTemplate);
            }

            // Find employee satisfaction template and add sections
            $employeeTemplate = QuestionnaireTemplate::where('code', 'EMPLOYEE_SAT_V1')->first();
            if ($employeeTemplate) {
                $this->addCompensationSection($employeeTemplate);
                $this->addTeamworkSection($employeeTemplate);
            }
        });

        $this->command->info('✅ Additional sections added successfully!');
    }

    /**
     * Add Digital Experience section to Customer Service template
     */
    private function addDigitalExperienceSection($template)
    {
        $this->command->info('Adding Digital Experience section...');
        
        $section = QuestionnaireSection::create([
            'template_id' => $template->id,
            'code' => 'DIGITAL_EXPERIENCE',
            'name' => 'Digital Experience',
            'description' => 'Evaluation of digital services and online experience',
            'instructions' => 'Please rate your experience with our digital services and online platforms.',
            'position' => 6, // After existing sections
            'is_required' => false,
            'skip_logic' => json_encode([
                'show_if_any' => [
                    ['question_code' => 'USED_DIGITAL_SERVICES', 'value' => true]
                ]
            ]),
            'company_id' => null
        ]);

        // Digital Service Usage Question
        $question1 = Question::create([
            'section_id' => $section->id,
            'code' => 'USED_DIGITAL_SERVICES',
            'prompt' => 'Did you use any of our digital services (website, mobile app, online portal)?',
            'response_type' => 'BOOL',
            'position' => 1,
            'is_required' => true,
            'weight' => null,
            'metadata' => json_encode([
                'category' => 'digital',
                'skip_trigger' => true
            ]),
            'company_id' => null
        ]);

        // Website Experience
        $question2 = Question::create([
            'section_id' => $section->id,
            'code' => 'WEBSITE_EXPERIENCE',
            'prompt' => 'How would you rate your experience with our website?',
            'response_type' => 'SINGLE_CHOICE',
            'position' => 2,
            'is_required' => false,
            'weight' => 15.0,
            'skip_logic' => json_encode([
                'show_if_any' => [
                    ['question_code' => 'USED_DIGITAL_SERVICES', 'value' => true]
                ]
            ]),
            'metadata' => json_encode([
                'category' => 'digital',
                'subcategory' => 'website'
            ]),
            'company_id' => null
        ]);

        $this->createRatingOptions($question2, 'digital_website');

        // Mobile App Experience
        $question3 = Question::create([
            'section_id' => $section->id,
            'code' => 'MOBILE_APP_EXPERIENCE',
            'prompt' => 'How would you rate our mobile app experience?',
            'response_type' => 'SINGLE_CHOICE',
            'position' => 3,
            'is_required' => false,
            'weight' => 15.0,
            'skip_logic' => json_encode([
                'show_if_any' => [
                    ['question_code' => 'USED_DIGITAL_SERVICES', 'value' => true]
                ]
            ]),
            'metadata' => json_encode([
                'category' => 'digital',
                'subcategory' => 'mobile_app'
            ]),
            'company_id' => null
        ]);

        $this->createRatingOptions($question3, 'digital_mobile');
    }

    /**
     * Add Accessibility section to Customer Service template
     */
    private function addAccessibilitySection($template)
    {
        $this->command->info('Adding Accessibility section...');
        
        $section = QuestionnaireSection::create([
            'template_id' => $template->id,
            'code' => 'ACCESSIBILITY',
            'name' => 'Accessibility and Accommodation',
            'description' => 'Assessment of accessibility features and accommodations',
            'instructions' => 'Please evaluate how well we accommodated your accessibility needs.',
            'position' => 7,
            'is_required' => false,
            'company_id' => null
        ]);

        // Accessibility Needs Question
        $question1 = Question::create([
            'section_id' => $section->id,
            'code' => 'ACCESSIBILITY_NEEDS',
            'prompt' => 'Do you have any accessibility needs or require special accommodations?',
            'response_type' => 'BOOL',
            'position' => 1,
            'is_required' => true,
            'weight' => null,
            'metadata' => json_encode([
                'category' => 'accessibility',
                'sensitive' => true
            ]),
            'company_id' => null
        ]);

        // Accommodation Satisfaction
        $question2 = Question::create([
            'section_id' => $section->id,
            'code' => 'ACCOMMODATION_SATISFACTION',
            'prompt' => 'How well did we accommodate your accessibility needs?',
            'response_type' => 'SINGLE_CHOICE',
            'position' => 2,
            'is_required' => false,
            'weight' => 20.0,
            'skip_logic' => json_encode([
                'show_if_any' => [
                    ['question_code' => 'ACCESSIBILITY_NEEDS', 'value' => true]
                ]
            ]),
            'metadata' => json_encode([
                'category' => 'accessibility',
                'sensitive' => true
            ]),
            'company_id' => null
        ]);

        $this->createRatingOptions($question2, 'accessibility');
    }

    /**
     * Add Compensation section to Employee Satisfaction template
     */
    private function addCompensationSection($template)
    {
        $this->command->info('Adding Compensation section...');
        
        $section = QuestionnaireSection::create([
            'template_id' => $template->id,
            'code' => 'COMPENSATION',
            'name' => 'Compensation and Benefits',
            'description' => 'Satisfaction with compensation, benefits, and rewards',
            'instructions' => 'Please rate your satisfaction with various aspects of your compensation package.',
            'position' => 3,
            'is_required' => true,
            'company_id' => null
        ]);

        // Salary Satisfaction
        $question1 = Question::create([
            'section_id' => $section->id,
            'code' => 'SALARY_SATISFACTION',
            'prompt' => 'How satisfied are you with your current salary?',
            'response_type' => 'SINGLE_CHOICE',
            'position' => 1,
            'is_required' => true,
            'weight' => 25.0,
            'metadata' => json_encode([
                'category' => 'compensation',
                'sensitive' => true
            ]),
            'company_id' => null
        ]);

        $this->createRatingOptions($question1, 'compensation_salary');

        // Benefits Satisfaction
        $question2 = Question::create([
            'section_id' => $section->id,
            'code' => 'BENEFITS_SATISFACTION',
            'prompt' => 'How satisfied are you with your benefits package?',
            'response_type' => 'SINGLE_CHOICE',
            'position' => 2,
            'is_required' => true,
            'weight' => 20.0,
            'metadata' => json_encode([
                'category' => 'compensation',
                'subcategory' => 'benefits'
            ]),
            'company_id' => null
        ]);

        $this->createRatingOptions($question2, 'compensation_benefits');
    }

    /**
     * Add Teamwork section to Employee Satisfaction template
     */
    private function addTeamworkSection($template)
    {
        $this->command->info('Adding Teamwork section...');
        
        $section = QuestionnaireSection::create([
            'template_id' => $template->id,
            'code' => 'TEAMWORK',
            'name' => 'Team Collaboration',
            'description' => 'Evaluation of teamwork and collaboration',
            'instructions' => 'Please rate your experience working with your team and colleagues.',
            'position' => 4,
            'is_required' => true,
            'company_id' => null
        ]);

        // Team Collaboration
        $question1 = Question::create([
            'section_id' => $section->id,
            'code' => 'TEAM_COLLABORATION',
            'prompt' => 'How effective is collaboration within your team?',
            'response_type' => 'SINGLE_CHOICE',
            'position' => 1,
            'is_required' => true,
            'weight' => 20.0,
            'metadata' => json_encode([
                'category' => 'teamwork',
                'subcategory' => 'collaboration'
            ]),
            'company_id' => null
        ]);

        $this->createRatingOptions($question1, 'teamwork_collaboration');

        // Communication with Colleagues
        $question2 = Question::create([
            'section_id' => $section->id,
            'code' => 'COLLEAGUE_COMMUNICATION',
            'prompt' => 'How would you rate communication with your colleagues?',
            'response_type' => 'SINGLE_CHOICE',
            'position' => 2,
            'is_required' => true,
            'weight' => 15.0,
            'metadata' => json_encode([
                'category' => 'teamwork',
                'subcategory' => 'communication'
            ]),
            'company_id' => null
        ]);

        $this->createRatingOptions($question2, 'teamwork_communication');

        // Team Support
        $question3 = Question::create([
            'section_id' => $section->id,
            'code' => 'TEAM_SUPPORT',
            'prompt' => 'How supportive are your team members?',
            'response_type' => 'SINGLE_CHOICE',
            'position' => 3,
            'is_required' => true,
            'weight' => 15.0,
            'metadata' => json_encode([
                'category' => 'teamwork',
                'subcategory' => 'support'
            ]),
            'company_id' => null
        ]);

        $this->createRatingOptions($question3, 'teamwork_support');
    }

    /**
     * Helper method to create standard 1-5 rating options
     */
    private function createRatingOptions($question, $category)
    {
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
    }
}
