<?php

namespace App\Http\Controllers;

use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FollowUpController extends Controller
{
    public function store(Request $request, $leadId)
    {
        $currentUser = auth('sanctum')->user();

        $validator = Validator::make($request->all(), [
            'note' => 'nullable|string|max:255',
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

            if ($lead->user_id !== $currentUser->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: You can only follow-up your own leads.',
                ], 403);
            }

            if ($lead->status == '6') {
                return response()->json([
                    'success' => false,
                    'message' => 'leads have been deal',
                ], 403);
            }

            $followUp = FollowUp::create([
                'lead_id' => $lead->id,
                'user_id' => $currentUser->id,
                'note' => $request->input('note'),
            ]);

            if ($request->status == '6') {
                $password = Str::random(6);

                $user = User::create([
                    'name' => $lead->name ?? null,
                    'email' => $lead->email ?? null,
                    'phone' => $lead->phone ?? null,
                    'role_id' => 5,
                    'password' => bcrypt($password)
                ]);

                try {
                    Mail::send('email.account-client', ['user' => $user, 'password' => $password], function ($message) use ($user) {
                        $message->to($user->email);
                        $message->subject('Welcome to SolarKita - Your Account Details');
                    });
                } catch (\Exception $e) {
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Follow-up recorded successfully.',
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
