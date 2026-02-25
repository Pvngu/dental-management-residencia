<?php

namespace App\SuperAdmin\Http\Controllers\Api;

use App\SuperAdmin\Models\QuestionnaireSection;
use App\SuperAdmin\Models\Question;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\SuperAdmin\Http\Requests\Api\QuestionnaireSection\IndexRequest;
use App\SuperAdmin\Http\Requests\Api\QuestionnaireSection\StoreRequest;
use App\SuperAdmin\Http\Requests\Api\QuestionnaireSection\UpdateRequest;
use App\SuperAdmin\Http\Requests\Api\QuestionnaireSection\DeleteRequest;
use Examyou\RestAPI\ApiResponse;
use Examyou\RestAPI\Exceptions\ApiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestionnaireSectionController extends ApiBaseController
{
    use CompanyTraits;
    
    protected $model = QuestionnaireSection::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query) {
        $request = request();

        // $query = $query->with(['template:id,name']);

        return $query;
    }

    /**
     * Get questions for a specific section
     */
    public function getQuestions($xid)
    {
        try {
            $section = QuestionnaireSection::where('id',$this->getIdFromHash($xid))->first();
            
            if (!$section) {
                return ApiResponse::make("Section not found");
            }

            $questions = $section->questions()
                ->with(['options' => function($query) {
                    $query->orderBy('position');
                }])
                ->orderBy('position')
                ->get();


                return ApiResponse::make("Section questions retrieved successfully", $questions->toArray());

        } catch (\Exception $e) {
            throw new ApiException($e->getMessage(), [], 500);
        }
    }

    /**
     * Add question to section
     */
    public function addQuestion($xid, Request $request)
    {
        try {
            $section = QuestionnaireSection::where('id', $this->getIdFromHash($xid))->first();
            
            if (!$section) {
                return ApiResponse::make("Section not found", null, false, 404);
            }

            $request->validate([
                'code' => 'required|string|max:50',
                'prompt' => 'required|string',
                'response_type' => 'required|string|in:TEXT,BOOL,SINGLE_CHOICE,MULTI_CHOICE,INFO,DATE,NUMERIC,FILE',
                'position' => 'required|integer|min:1',
                'is_required' => 'boolean',
                'weight' => 'nullable|numeric|min:0',
                'skip_logic' => 'nullable|json',
                'validation_rules' => 'nullable|json',
                'metadata' => 'nullable|json',
                'options' => 'nullable|array',
                'options.*.code' => 'required_with:options|string|max:50',
                'options.*.label' => 'required_with:options|string|max:255',
                'options.*.value_numeric' => 'nullable|numeric',
                'options.*.position' => 'required_with:options|integer|min:1',
                'options.*.response_tags' => 'nullable|json',
            ]);

            // Check if code is unique within the section
            $existingQuestion = $section->questions()->where('code', $request->code)->first();
            if ($existingQuestion) {
                throw new ApiException("Question code must be unique within the section", [], 422);
            }

            // Validate that options are provided for choice questions
            if (in_array($request->response_type, ['SINGLE_CHOICE', 'MULTI_CHOICE']) && empty($request->options)) {
                throw new ApiException("Options are required for SINGLE_CHOICE and MULTI_CHOICE questions", [], 422);
            }

            DB::beginTransaction();

            $company = company();

            // Adjust positions of existing questions
            $section->questions()
                ->where('position', '>=', $request->position)
                ->increment('position');

            // Create new question
            $question = new Question();
            $question->section_id = $section->id;
            $question->code = $request->code;
            $question->prompt = $request->prompt;
            $question->response_type = $request->response_type;
            $question->position = $request->position;
            $question->is_required = $request->boolean('is_required', false);
            $question->weight = $request->weight ?? 1.0;
            $question->skip_logic = $request->skip_logic ? json_decode($request->skip_logic, true) : null;
            $question->validation_rules = $request->validation_rules ? json_decode($request->validation_rules, true) : null;
            $question->metadata = $request->metadata ? json_decode($request->metadata, true) : null;
            $question->company_id = $company->id;
            $question->save();

            // Create question options if provided
            if ($request->options && in_array($request->response_type, ['SINGLE_CHOICE', 'MULTI_CHOICE'])) {
                foreach ($request->options as $optionData) {
                    $option = new \App\SuperAdmin\Models\QuestionOption();
                    $option->question_id = $question->id;
                    $option->code = $optionData['code'];
                    $option->label = $optionData['label'];
                    $option->value_numeric = $optionData['value_numeric'] ?? null;
                    $option->position = $optionData['position'];
                    $option->response_tags = !empty($optionData['response_tags']) ? json_decode($optionData['response_tags'], true) : null;
                    $option->company_id = $company->id;
                    $option->save();
                }
            }

            DB::commit();

            // Load the question with options
            $questionWithOptions = Question::with(['options' => function($query) {
                $query->orderBy('position');
            }])->find($question->id);

            return ApiResponse::make("Question added successfully", $questionWithOptions);
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ApiException($e->getMessage(), [], 500);
        }
    }

    /**
     * Reorder questions within a section
     */
    public function reorderQuestions($xid, Request $request)
    {
        try {
            $section = QuestionnaireSection::where('id', $this->getIdFromHash($xid))->first();
            
            if (!$section) {
                return ApiResponse::make("Section not found", null, false, 404);
            }

            $request->validate([
                'questions' => 'required|array',
                'questions.*.xid' => 'required|string',
                'questions.*.position' => 'required|integer|min:1',
            ]);

            DB::beginTransaction();

            // Update positions for each question
            foreach ($request->questions as $questionData) {
                $question = Question::where(
                    Question::getHashableColumn('xid'), 
                    $questionData['xid']
                )->where('section_id', $section->id)->first();

                if ($question) {
                    $question->position = $questionData['position'];
                    $question->save();
                }
            }

            DB::commit();

            $updatedQuestions = $section->questions()
                ->with(['options' => function($query) {
                    $query->orderBy('position');
                }])
                ->orderBy('position')
                ->get();

            return ApiResponse::make("Questions reordered successfully", $updatedQuestions->toArray());
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ApiException($e->getMessage(), [], 500);
        }
    }

    /**
     * Get section with all questions and options
     */
    public function getSectionStructure($xid)
    {
        try {
            $section = QuestionnaireSection::with([
                'questions' => function($query) {
                    $query->with(['options' => function($optionQuery) {
                        $optionQuery->orderBy('position');
                    }])->orderBy('position');
                }
            ])->where('id', $this->getIdFromHash($xid))->first();

            if (!$section) {
                return ApiResponse::make("Section not found", null, false, 404);
            }

            return ApiResponse::make("Section structure retrieved successfully", $section);
            
        } catch (\Exception $e) {
            throw new ApiException($e->getMessage(), [], 500);
        }
    }
}
