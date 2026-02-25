<?php

namespace App\SuperAdmin\Http\Controllers\Api;

use App\SuperAdmin\Models\Question;
use App\SuperAdmin\Models\QuestionOption;
use App\SuperAdmin\Models\QuestionnaireTemplate;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\SuperAdmin\Http\Requests\Api\Question\IndexRequest;
use App\SuperAdmin\Http\Requests\Api\Question\StoreRequest;
use App\SuperAdmin\Http\Requests\Api\Question\UpdateRequest;
use App\SuperAdmin\Http\Requests\Api\Question\DeleteRequest;
use Examyou\RestAPI\ApiResponse;
use Examyou\RestAPI\Exceptions\ApiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestionController extends ApiBaseController
{
    use CompanyTraits;
    
    protected $model = Question::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query) {
        $request = request();

        // Include section relationship
        // $query = $query->with(['section']);

        return $query;
    }

    /**
     * Get options for a specific question
     */
    public function getOptions($xid)
    {
        try {
            $question = Question::where('id', $this->getIdFromHash($xid))->first();
            
            if (!$question) {
                return ApiResponse::make("Question not found", null, false, 404);
            }

            if (!in_array($question->response_type, ['SINGLE_CHOICE', 'MULTI_CHOICE'])) {
                return ApiResponse::make("Question type does not support options", null, false, 422);
            }

            $options = $question->options()
                ->orderBy('position')
                ->get();

            return ApiResponse::make("Question options retrieved successfully", $options->toArray());

        } catch (\Exception $e) {
            throw new ApiException($e->getMessage());
        }
    }

    /**
     * Add option to question
     */
    public function addOption($xid)
    {
        $request = request();
        try {
            $question = Question::where('id', $this->getIdFromHash($xid))->first();
            
            if (!$question) {
                return ApiResponse::make("Question not found", null, false, 404);
            }

            if (!in_array($question->response_type, ['SINGLE_CHOICE', 'MULTI_CHOICE'])) {
                return ApiResponse::make("Question type does not support options", null, false, 422);
            }

            $request->validate([
                'code' => 'required|string|max:50',
                'label' => 'required|string|max:255',
                'value_numeric' => 'nullable|numeric',
                'position' => 'required|integer|min:1',
                'response_tags' => 'nullable|json',
            ]);

            // Check if code is unique within the question
            $existingOption = $question->options()->where('code', $request->code)->first();
            if ($existingOption) {
                return ApiResponse::make("Option code must be unique within the question", null, false, 422);
            }

            DB::beginTransaction();

            $company = company();

            // Adjust positions of existing options
            $question->options()
                ->where('position', '>=', $request->position)
                ->increment('position');

            // Create new option
            $option = new QuestionOption();
            $option->question_id = $question->id;
            $option->code = $request->code;
            $option->label = $request->label;
            $option->value_numeric = $request->value_numeric;
            $option->position = $request->position;
            $option->response_tags = $request->response_tags ? json_decode($request->response_tags, true) : null;
            $option->company_id = $company->id;
            $option->save();

            DB::commit();

            return ApiResponse::make("Option added successfully", $option);

        } catch (\Exception $e) {
            DB::rollBack();
            throw new ApiException($e->getMessage());
        }
    }

    /**
     * Update option
     */
    public function updateOption($questionXid, $optionXid, Request $request)
    {
        try {
            $question = Question::where('id', $this->getIdFromHash($questionXid))->first();
            
            if (!$question) {
                return ApiResponse::make("Question not found", null, false, 404);
            }

            $option = QuestionOption::where('id', $this->getIdFromHash($optionXid))->where('question_id', $question->id)->first();
            
            if (!$option) {
                return ApiResponse::make("Option not found", null, false, 404);
            }

            $request->validate([
                'code' => 'string|max:50',
                'label' => 'string|max:255',
                'value_numeric' => 'nullable|numeric',
                'position' => 'integer|min:1',
                'response_tags' => 'nullable|json',
            ]);

            // Check if code is unique within the question (excluding current option)
            if ($request->has('code') && $request->code !== $option->code) {
                $existingOption = $question->options()
                    ->where('code', $request->code)
                    ->where('option_id', '!=', $option->option_id)
                    ->first();
                if ($existingOption) {
                    return ApiResponse::make("Option code must be unique within the question", null, false, 422);
                }
            }

            DB::beginTransaction();

            // Handle position changes
            if ($request->has('position') && $request->position !== $option->position) {
                if ($request->position > $option->position) {
                    // Moving down: decrement positions between old and new position
                    $question->options()
                        ->where('position', '>', $option->position)
                        ->where('position', '<=', $request->position)
                        ->decrement('position');
                } else {
                    // Moving up: increment positions between new and old position
                    $question->options()
                        ->where('position', '>=', $request->position)
                        ->where('position', '<', $option->position)
                        ->increment('position');
                }
            }

            // Update option fields
            if ($request->has('code')) $option->code = $request->code;
            if ($request->has('label')) $option->label = $request->label;
            if ($request->has('value_numeric')) $option->value_numeric = $request->value_numeric;
            if ($request->has('position')) $option->position = $request->position;
            if ($request->has('response_tags')) {
                $option->response_tags = $request->response_tags ? json_decode($request->response_tags, true) : null;
            }

            $option->save();

            DB::commit();

            return ApiResponse::make("Option updated successfully", $option);

        } catch (\Exception $e) {
            DB::rollBack();
            throw new ApiException($e->getMessage());
        }
    }

    /**
     * Delete option
     */
    public function deleteOption($questionXid, $optionXid)
    {
        try {
            $question = Question::where('id', $this->getIdFromHash($questionXid))->first();
            
            if (!$question) {
                return ApiResponse::make("Question not found", null, false, 404);
            }

            $option = QuestionOption::where('id', $this->getIdFromHash($optionXid))->where('question_id', $question->id)->first();
            
            if (!$option) {
                return ApiResponse::make("Option not found", null, false, 404);
            }

            DB::beginTransaction();

            $deletedPosition = $option->position;

            // Delete the option
            $option->delete();

            // Adjust positions of remaining options
            $question->options()
                ->where('position', '>', $deletedPosition)
                ->decrement('position');

            DB::commit();

            return ApiResponse::make("Option deleted successfully");

        } catch (\Exception $e) {
            DB::rollBack();
            throw new ApiException($e->getMessage());
        }
    }

    /**
     * Reorder options within a question
     */
    public function reorderOptions($xid, Request $request)
    {
        try {
            $question = Question::where('id', $this->getIdFromHash($xid))->first();
            
            if (!$question) {
                return ApiResponse::make("Question not found", null, false, 404);
            }

            if (!in_array($question->response_type, ['SINGLE_CHOICE', 'MULTI_CHOICE'])) {
                return ApiResponse::make("Question type does not support options", null, false, 422);
            }

            $request->validate([
                'options' => 'required|array',
                'options.*.xid' => 'required|string',
                'options.*.position' => 'required|integer|min:1',
            ]);

            DB::beginTransaction();

            // Update positions for each option
            foreach ($request->options as $optionData) {
                $option = QuestionOption::where('id', $this->getIdFromHash($optionData['xid']))->where('question_id', $question->id)->first();

                if ($option) {
                    $option->position = $optionData['position'];
                    $option->save();
                }
            }

            DB::commit();

            $updatedOptions = $question->options()
                ->orderBy('position')
                ->get();

            return ApiResponse::make("Options reordered successfully", $updatedOptions->toArray());

        } catch (\Exception $e) {
            DB::rollBack();
            throw new ApiException($e->getMessage());
        }
    }

    /**
     * Get question with all options
     */
    public function getQuestionStructure($xid)
    {
        try {
            $question = Question::with([
                'options' => function($query) {
                    $query->orderBy('position');
                }
            ])->where('id', $this->getIdFromHash($xid))->first();

            if (!$question) {
                return ApiResponse::make("Question not found", null, false, 404);
            }

            return ApiResponse::make("Question structure retrieved successfully", $question);

        } catch (\Exception $e) {
            throw new ApiException($e->getMessage(), [], 500);
        }
    }

    public function createFull(Request $request)
    {
        $request->validate([
            // 'id' => 'nullable',
            'code' => 'required|string|max:50',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'sections' => 'required|array',
            // 'sections.*.uid' => 'required|string',
            // 'sections.*.code' => 'required|string|max:50',
            // 'sections.*.name' => 'required|string|max:255',
            // 'sections.*.description' => 'nullable|string',
            // 'sections.*.instructions' => 'nullable|string',
            // 'sections.*.position' => 'required|integer|min:1',
            // 'sections.*.is_required' => 'required|boolean',
            // 'sections.*.skip_logic' => 'nullable',
            // 'sections.*.questions' => 'required|array',
            // 'sections.*.questions.*.uid' => 'required|string',
            // 'sections.*.questions.*.code' => 'required|string|max:50',
            // 'sections.*.questions.*.prompt' => 'required|string',
            // 'sections.*.questions.*.response_type' => 'required|string|in:TEXT,SINGLE_CHOICE,MULTI_CHOICE,BOOL,NUMBER,DATE,EMAIL',
            // 'sections.*.questions.*.position' => 'required|integer|min:1',
            // 'sections.*.questions.*.is_required' => 'required|boolean',
            // 'sections.*.questions.*.weight' => 'nullable|numeric',
            // 'sections.*.questions.*.skip_logic' => 'nullable|array',
            // 'sections.*.questions.*.validation_rules' => 'nullable|array',
            // 'sections.*.questions.*.metadata' => 'nullable|array',
            // 'sections.*.questions.*.options' => 'nullable|array',
            // 'sections.*.questions.*.options.*.uid' => 'required_with:sections.*.questions.*.options|string',
            // 'sections.*.questions.*.options.*.code' => 'required_with:sections.*.questions.*.options|string|max:50',
            // 'sections.*.questions.*.options.*.label' => 'required_with:sections.*.questions.*.options|string|max:255',
            // 'sections.*.questions.*.options.*.value_numeric' => 'nullable|numeric',
            // 'sections.*.questions.*.options.*.position' => 'required_with:sections.*.questions.*.options|integer|min:1',
            // 'sections.*.questions.*.options.*.response_tags' => 'nullable|array',
        ]);

        $requestData = $request->all();

        try {
            DB::beginTransaction();

            $company = company();

            // dd($requestData['code']);

            // Create the questionnaire template
            $template = QuestionnaireTemplate::where('code', $requestData['code'])->first();
            // $template->code = $request->code;
            // $template->name = $request->name ?? '';
            // $template->description = $request->description ?? '';
            // $template->instructions = $request->instructions ?? '';
            // $template->is_active = true;
            // $template->is_evergreen = false;
            // $template->company_id = $company->id;
            // $template->save();

            // $template = $template->where('code', $request->code)->first();
            // dd($template);
            $totalQuestions = 0;

            // Process each section
            foreach ($request->sections as $sectionData) {
                // Create the section
                $section = new \App\SuperAdmin\Models\QuestionnaireSection();
                $section->template_id = $template->id;
                $section->code = $sectionData['code'];
                $section->name = $sectionData['name'];
                $section->description = $sectionData['description'] ?? '';
                $section->instructions = $sectionData['instructions'] ?? '';
                $section->position = $sectionData['position'];
                $section->is_required = $sectionData['is_required'];
                $section->skip_logic = $sectionData['skip_logic'];
                $section->company_id = $company->id;
                $section->save();

                // Process questions for this section
                foreach ($sectionData['questions'] as $questionData) {
                    // Create the question
                    $question = new Question();
                    $question->section_id = $section->id;
                    $question->code = $questionData['code'];
                    $question->prompt = $questionData['prompt'];
                    $question->response_type = $questionData['response_type'];
                    $question->position = $questionData['position'];
                    $question->is_required = $questionData['is_required'];
                    $question->weight = $questionData['weight'] ?? 0;
                    $question->skip_logic = $questionData['skip_logic'] ?? null;
                    $question->validation_rules = $questionData['validation_rules'] ?? null;
                    $question->metadata = $questionData['metadata'] ?? null;
                    $question->company_id = $company->id;
                    $question->save();

                    $totalQuestions++;

                    // Create options if they exist (for SINGLE_CHOICE and MULTI_CHOICE questions)
                    if (!empty($questionData['options'])) {
                        foreach ($questionData['options'] as $optionData) {
                            $option = new QuestionOption();
                            $option->question_id = $question->id;
                            $option->code = $optionData['code'];
                            $option->label = $optionData['label'];
                            $option->value_numeric = $optionData['value_numeric'];
                            $option->position = $optionData['position'];
                            $option->response_tags = $optionData['response_tags'] ?? null;
                            $option->company_id = $company->id;
                            $option->save();
                        }
                    }
                }
            }

            DB::commit();

            return ApiResponse::make("Questionnaire template created successfully", [
                'template_id' => $template->xid,
                'template_code' => $template->code,
                'template_name' => $template->name,
                'sections_count' => count($request->sections),
                'questions_count' => $totalQuestions
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            throw new ApiException($e->getMessage());
        }
    }
}
