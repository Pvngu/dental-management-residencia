<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\CalendarEvent;
use Illuminate\Support\Facades\DB;
use App\Classes\Common;
use Carbon\Carbon;

class CalendarEventController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required', // hash
            'patient_id' => 'nullable', // hash
            'title' => 'required|string|max:255',
            'event_date' => 'required|date_format:Y-m-d',
            'event_time' => 'required|date_format:H:i',
            'duration' => 'required|integer|min:5',
            'color' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $doctorId = Common::getIdFromHash($request->doctor_id);
            $companyId = company()->id;
            
            // Clinic ID optional
            $clinicId = $request->clinic_id ? Common::getIdFromHash($request->clinic_id) : (request()->header('X-Clinic-ID') ? Common::getIdFromHash(request()->header('X-Clinic-ID')) : null);

            $eventDateTime = Carbon::createFromFormat('Y-m-d H:i', $request->event_date . ' ' . $request->event_time);

            $event = new CalendarEvent();
            $event->company_id = $companyId;
            $event->clinic_id = $clinicId;
            $event->doctor_id = $doctorId;
            $event->patient_id = $request->patient_id ? Common::getIdFromHash($request->patient_id) : null;
            $event->title = $request->title;
            $event->event_date = $eventDateTime;
            $event->duration = $request->duration;
            $event->color = $request->color ?? '#1890ff';
            $event->description = $request->description;
            $event->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Event created successfully',
                'data' => $event
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create event: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'doctor_id' => 'required',
            'patient_id' => 'nullable',
            'event_date' => 'required|date_format:Y-m-d',
            'event_time' => 'required|date_format:H:i',
            'duration' => 'required|integer|min:5',
        ]);

        try {
            DB::beginTransaction();

            // Support either hash ID or real ID mapping
            $eventId = is_numeric($id) ? $id : Common::getIdFromHash($id);
            $event = CalendarEvent::findOrFail($eventId);

            // Simple drag and drop update
            $eventDateTime = Carbon::createFromFormat('Y-m-d H:i', $request->event_date . ' ' . $request->event_time);
            
            $doctorId = Common::getIdFromHash($request->doctor_id);
            $event->doctor_id = $doctorId;
            if ($request->has('patient_id')) {
                $event->patient_id = $request->patient_id ? Common::getIdFromHash($request->patient_id) : null;
            }
            $event->event_date = $eventDateTime;
            $event->duration = $request->duration;
            
            if ($request->has('title')) {
                $event->title = $request->title;
            }
            if ($request->has('color')) {
                $event->color = $request->color;
            }
            if ($request->has('description')) {
                $event->description = $request->description;
            }

            $event->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Event updated successfully',
                'data' => $event
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update event: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $eventId = is_numeric($id) ? $id : Common::getIdFromHash($id);
            $event = CalendarEvent::findOrFail($eventId);
            $event->delete();

            return response()->json([
                'success' => true,
                'message' => 'Event deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete event: ' . $e->getMessage()
            ], 500);
        }
    }
}
