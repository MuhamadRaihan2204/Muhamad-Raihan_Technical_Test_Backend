<?php

namespace App\Http\Controllers;

use App\Models\SalesSuspension;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SalesSuspensionController extends Controller
{
    protected $model;

    public function __construct(SalesSuspension $sales)
    {
        $this->model = $sales;
    }

    public function index()
    {
        $data = SalesSuspension::select('id', 'user_id', 'start_date', 'end_date')
            ->with(['user:id,name'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data ?? []
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::find($request->user_id);
        if ($user->role_id !== 3) {
            return response()->json([
                'success' => false,
                'message' => 'The user does not have permission to be suspended',
            ], 403);
        }

        $existingSuspension = $this->model->where('user_id', $request->user_id)
            ->where('end_date', '>=', now()) 
            ->first();

        if ($existingSuspension) {
            return response()->json([
                'success' => false,
                'message' => 'The user already has an active suspension',
            ], 403);
        }

        try {
            DB::beginTransaction();

            $data = $this->model->create($request->only('user_id', 'start_date', 'end_date'));

            DB::commit();

            return response()->json([
                'success' => true,
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
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
    
        $user = User::find($request->user_id);
        if ($user->role_id !== 3) {
            return response()->json([
                'success' => false,
                'message' => 'The user does not have permission to be suspended',
            ], 403);
        }
    
        $existingSuspension = $this->model->where('user_id', $request->user_id)
            ->where('end_date', '>=', now()) 
            ->where('id', '!=', $id) 
            ->first();
    
        if ($existingSuspension) {
            return response()->json([
                'success' => false,
                'message' => 'The user already has an active suspension',
            ], 403);
        }
    
        try {
            DB::beginTransaction();
    
            $salesSuspension = $this->model->findOrFail($id);
    
            $salesSuspension->update($request->only('user_id', 'start_date', 'end_date'));
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Data updated successfully',
                'data' => $salesSuspension
            ]);
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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $salesSuspension = $this->model->findOrFail($id);
            $salesSuspension->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data deleted successfully',
                'data' => null
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Server Error',
                'data' => $e->getMessage()
            ], 500);
        }
    }
}
