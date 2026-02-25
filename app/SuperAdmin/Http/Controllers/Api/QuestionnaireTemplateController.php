<?php

namespace App\SuperAdmin\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use App\SuperAdmin\Models\QuestionnaireTemplate;
use App\SuperAdmin\Models\QuestionnaireSection;
use App\Traits\CompanyTraits;
use App\SuperAdmin\Http\Requests\Api\QuestionnaireTemplate\IndexRequest;
use App\SuperAdmin\Http\Requests\Api\QuestionnaireTemplate\StoreRequest;
use App\SuperAdmin\Http\Requests\Api\QuestionnaireTemplate\UpdateRequest;
use App\SuperAdmin\Http\Requests\Api\QuestionnaireTemplate\DeleteRequest;
use Examyou\RestAPI\ApiResponse;
use Examyou\RestAPI\Exceptions\ApiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestionnaireTemplateController extends ApiBaseController
{
    use CompanyTraits;
    
    protected $model = QuestionnaireTemplate::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query) {
        $request = request();

        // dd($query->toSql() );

        // $query->get();
        // dd($query->get());

        return $query;
    }

    /**
     * Get template with all sections and questions
     */
    public function getTemplateStructure($xid)
    {
        try {
            $template = QuestionnaireTemplate::with([
                'sections' => function($query) {
                    $query->orderBy('position');
                },
                'sections.questions' => function($query) {
                    $query->with(['options' => function($optionQuery) {
                        $optionQuery->orderBy('position');
                    }])->orderBy('position');
                }
            ])->where('id',$this->getIdFromHash($xid))->first(); 

            if (!$template) {
                return ApiResponse::make("Template not found");
            }

            return ApiResponse::make("Template structure retrieved successfully", $template->toArray());

        } catch (\Exception $e) {
            throw new ApiException("An error occurred while retrieving template structure: " . $e->getMessage());
        }
    }

    /**
     * Get sections for a specific template
     */
    public function getSections($xid)
    {
        try {
            $template = QuestionnaireTemplate::where('id', $this->getIdFromHash($xid))->first();
            
            if (!$template) {
                return ApiResponse::make("Template not found", null, false, 404);
            }

            $sections = $template->sections()
                ->orderBy('position')
                ->get();

            return ApiResponse::make("Template sections retrieved successfully", $sections->toArray());
            
        } catch (\Exception $e) {
            throw new ApiException("An error occurred while retrieving sections: " . $e->getMessage());
        }
    }

    /**
     * Add section to template
     */
    public function addSection($xid, Request $request)
    {
        try {
            $template = QuestionnaireTemplate::where('id', $this->getIdFromHash($xid))->first();

            if (!$template) {
                return ApiResponse::make("Template not found", null, false, 404); 
               
            }

            $request->validate([
                'code' => 'required|string|max:50',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'instructions' => 'nullable|string',
                'position' => 'required|integer|min:1',
                'is_required' => 'boolean',
                'skip_logic' => 'nullable|json',
            ]);

            // Check if code is unique within the template
            $existingSection = $template->sections()->where('code', $request->code)->first();
            if ($existingSection) {
                throw new ApiException("Section code must be unique within the template", [], 422);
            }

            DB::beginTransaction();

            // Adjust positions of existing sections
            $template->sections()
                ->where('position', '>=', $request->position)
                ->increment('position');

            // Create new section
            $company = company();
            $section = new QuestionnaireSection();
            $section->template_id = $template->id;
            $section->code = $request->code;
            $section->name = $request->name;
            $section->description = $request->description;
            $section->instructions = $request->instructions;
            $section->position = $request->position;
            $section->is_required = $request->boolean('is_required', false);
            $section->skip_logic = $request->skip_logic ? json_decode($request->skip_logic, true) : null;
            $section->company_id = $company->id;
            $section->save();

            DB::commit();

            return ApiResponse::make("Section added successfully", $section->toArray());

        } catch (\Exception $e) {
            DB::rollBack();
            throw new ApiException("An error occurred while retrieving sections: " . $e->getMessage());
        }
    }

    /**
     * Reorder sections within a template
     */
    public function reorderSections($xid, Request $request)
    {
        try {
            $template = QuestionnaireTemplate::where('id', $this->getIdFromHash($xid))->first();

            if (!$template) {
                return ApiResponse::make("Template not found", null, false, 404);
            }

            $request->validate([
                'sections' => 'required|array',
                'sections.*.xid' => 'required|string',
                'sections.*.position' => 'required|integer|min:1',
            ]);

            DB::beginTransaction();

            // Update positions for each section
            foreach ($request->sections as $sectionData) {
                $section = QuestionnaireSection::where(
                    QuestionnaireSection::getHashableColumn('xid'), 
                    $sectionData['xid']
                )->where('template_id', $template->id)->first();

                if ($section) {
                    $section->position = $sectionData['position'];
                    $section->save();
                }
            }

            DB::commit();

            $updatedSections = $template->sections()->orderBy('position')->get();

            return ApiResponse::make("Sections reordered successfully", $updatedSections->toArray());
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ApiException("An error occurred while retrieving sections: " . $e->getMessage());
        }
    }

    /**
     * Clone template with all sections and questions
     */
    public function cloneTemplate($xid, Request $request)
    {
        try {
            $originalTemplate = QuestionnaireTemplate::with([
                'sections.questions.options'
            ])->where('id', $this->getIdFromHash($xid))->first();

            if (!$originalTemplate) {
                return ApiResponse::make("Template not found", null, false, 404);
            }

            $request->validate([
                'code' => 'required|string|max:50|unique:questionnaire_templates,code',
                'version' => 'required|string|max:20',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            DB::beginTransaction();

            $company = company();
            $user = user();

            // Clone template
            $newTemplate = $originalTemplate->replicate();
            $newTemplate->code = $request->code;
            $newTemplate->version = $request->version;
            $newTemplate->name = $request->name;
            $newTemplate->description = $request->description ?? $originalTemplate->description;
            $newTemplate->created_by = $user->id;
            $newTemplate->company_id = $company->id;
            $newTemplate->save();

            // Clone sections and questions
            foreach ($originalTemplate->sections as $section) {
                $newSection = $section->replicate();
                $newSection->template_id = $newTemplate->id;
                $newSection->company_id = $company->id;
                $newSection->save();

                // Clone questions for this section
                foreach ($section->questions as $question) {
                    $newQuestion = $question->replicate();
                    $newQuestion->section_id = $newSection->id;
                    $newQuestion->company_id = $company->id;
                    $newQuestion->save();

                    // Clone question options
                    foreach ($question->options as $option) {
                        $newOption = $option->replicate();
                        $newOption->question_id = $newQuestion->id;
                        $newOption->company_id = $company->id;
                        $newOption->save();
                    }
                }
            }

            DB::commit();

            // Load the complete cloned template
            $clonedTemplate = QuestionnaireTemplate::with([
                'sections.questions.options'
            ])->find($newTemplate->id);

            return ApiResponse::make("Template cloned successfully", $clonedTemplate->toArray());
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ApiException("An error occurred while retrieving sections: " . $e->getMessage());
        }
    }
}