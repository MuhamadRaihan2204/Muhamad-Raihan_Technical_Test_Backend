<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\PreviouslyLead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class LeadController extends Controller
{
    protected $model;

    public function __construct(Lead $lead)
    {

        $this->model = $lead;
    }

    public function index()
    {
        $data = Lead::select('id', 'name', 'email', 'phone', 'user_id', 'status')->with(['user:id,name'])->get()
            ->map(function ($lead) {
                switch ($lead->status) {
                    case 0:
                        $lead->status = 'new';
                        break;
                    case 1:
                        $lead->status = 'follow up';
                        break;
                    case 2:
                        $lead->status = 'survey approved';
                        break;
                    case 3:
                        $lead->status = 'survey rejected';
                        break;
                    case 4:
                        $lead->status = 'survey completed';
                        break;
                    case 5:
                        $lead->status = 'final follow up';
                        break;
                    case 6:
                        $lead->status = 'deal';
                        break;
                }
                return $lead;
            });;

        $data = [
            'success' => true,
            'data' => $data ? $data : []
        ];

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:16',
            'email' => 'required|email|unique:leads,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $payload = $request->all();

            $salesPersons = User::where('role_id', 3)
                ->whereDoesntHave('salesSuspension', function ($query) {
                    $query->where(function ($subQuery) {
                        $subQuery->where('start_date', '<=', now())
                            ->where('end_date', '>=', now());
                    });
                })
                ->get();

            if ($salesPersons->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active salespersons available',
                ], 400);
            }

            $assignedLeads = Lead::whereIn('user_id', $salesPersons->pluck('id'))
                ->where('status', '!=', '6')
                ->pluck('user_id')
                ->toArray();

            $availableSalesPersons = $salesPersons->filter(function ($user) use ($assignedLeads) {
                return !in_array($user->id, $assignedLeads);
            });

            if ($availableSalesPersons->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'All sales are unable to receive leads',
                ], 400);
            }

            $lastLead = Lead::orderBy('created_at', 'desc')->first();
            $nextSalesperson = null;

            if ($lastLead) {
                $lastSalespersonId = $lastLead->user_id;
                $lastIndex = $salesPersons->search(function ($user) use ($lastSalespersonId) {
                    return $user->id == $lastSalespersonId;
                });

                $nextIndex = ($lastIndex + 1) % $salesPersons->count();
                $nextSalesperson = $salesPersons[$nextIndex];

                while (in_array($nextSalesperson->id, $assignedLeads)) {
                    $nextIndex = ($nextIndex + 1) % $salesPersons->count();
                    $nextSalesperson = $salesPersons[$nextIndex];

                    if ($nextIndex === ($lastIndex + 1) % $salesPersons->count()) {
                        break;
                    }
                }
            } else {
                $nextSalesperson = $availableSalesPersons->first();
            }

            $data = $this->model->create([
                'name' => $payload['name'],
                'email' => $payload['email'],
                'phone' => $payload['phone'],
                'user_id' => $nextSalesperson->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Lead successfully created',
                'data' => $data,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Server Error',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function transferLeads(Request $request, string $id)
    {
        try {
            DB::beginTransaction();

            $lead = Lead::findOrFail($id);

            $existingLead = Lead::where('user_id', $request->user_id)
                ->where('status', '!=', '6')
                ->first();

            if ($existingLead) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sales Person already has an active lead and cannot receive this transfer.',
                ], 400);
            }

            $newUser = User::where('role_id', 3)
                ->where('id', $request->user_id)
                ->whereDoesntHave('salesSuspension', function ($query) {
                    $query->where(function ($subQuery) {
                        $subQuery->where('start_date', '<=', now())
                            ->where('end_date', '>=', now());
                    });
                })
                ->first();

            if (!$newUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'No available Sales Person to transfer the lead.',
                ], 404);
            }

            $lead->update([
                'user_id' => $newUser->id,
            ]);

            PreviouslyLead::create([
                'name' => $lead->name ?? null,
                'email' => $lead->email ?? null,
                'phone' => $lead->phone ?? null,
                'user_id' => $lead->user_id ?? null,
                'lead_id' => $lead->id ?? null,
            ]);

            $data = [
                'success' => 'Transfer lead to new sales staff: ' . $newUser->name,
                'user_id' => $lead->user_id,
            ];

            DB::commit();

            return response()->json($data);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Server Error',
                'data' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();
            $lead = $this->model->find($id);
            $data = $lead->delete();

            DB::commit();

            $response = [
                'success' => true,
                'message' => 'Success delete data',
                'data' => $data
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            DB::rollBack();

            $response = [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
            return response()->json($response, 500);
        }
    }

    public function operationalLead()
    {
        $leads = Lead::select('id', 'name', 'email', 'phone', 'user_id', 'status')
            ->with(['user:id,name'])
            ->where('status', '=', '1')
            ->get()
            ->map(function ($lead) {
                $lead->status = 'follow up';
                return $lead;
            });

        $data = [
            'success' => true,
            'data' => $leads,
        ];

        return response()->json($data);
    }
}
