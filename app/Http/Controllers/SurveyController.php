<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Survey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SurveyController extends Controller
{
    public function survey(Request $request, $leadId)
    {
        $currentUser = auth('sanctum')->user();

        $validator = Validator::make($request->all(), [
            'note' => 'required_if:status,4|string',
            'image' => 'required_if:status,4|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:2,3,4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $lead = Lead::findOrFail($leadId);

            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('surveys', 'public');
            }

            $followUp = Survey::create([
                'lead_id' => $lead->id,
                'user_id' => $currentUser->id,
                'note' => $request->input('note'),
                'image' => $imagePath,
            ]);

            $lead->update(['status' => $request->status]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Survey recorded successfully.',
                'data' => $followUp,
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Server Error',
                'data' => $th->getMessage(),
            ], 500);
        }
    }
}
