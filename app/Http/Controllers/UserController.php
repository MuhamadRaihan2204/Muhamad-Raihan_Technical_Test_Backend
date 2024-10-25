<?php

namespace App\Http\Controllers;

use App\Helpers\PhoneHelper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    protected $model;

    public function __construct(User $user)
    {

        $this->model = $user;
    }

    public function index()
    {
        $users = Redis::get('users_data');
        if (!$users) {
            $data = User::select('id', 'name', 'email', 'role_id')->with(['role:id,name'])->get();
            Redis::set('users_data', $data->toJson());
            Redis::expire('users_data', 60);
        } else {
            $data = json_decode($users);
        }

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
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'is_residential' => 'nullable|in:1',
            'is_commercial' => 'nullable|in:1',
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
            $hashedPassword = bcrypt($request->input('password'));
    
            $phone = PhoneHelper::formatPhone($request->input('phone'));
    
            $isResidential = $request->input('is_residential') === '1' ? '1' : '0';
            $isCommercial = $request->input('is_commercial') === '1' ? '1' : '0';
    
            $data = $this->model->create([
                'name' => $payload['name'],
                'email' => $payload['email'],
                'phone' => $phone,
                'role_id' => $payload['role_id'],
                'password' => $hashedPassword,
                'is_residential' => $isResidential,
                'is_commercial' => $isCommercial,
            ]);
    
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
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = User::findOrFail($id);
        return response()->json($data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:16',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = $this->model->findOrFail($id);
            $payload = $request->all();

            if ($request->has('phone')) {
                $payload['phone'] = PhoneHelper::formatPhone($request->input('phone'));
            }

            if ($request->filled('password')) {
                $payload['password'] = bcrypt($request->input('password'));
            }

            $user->update($payload);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Success update data',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            $response = [
                'success' => false,
                'message' => 'Server Error',
                'data' => $e->getMessage()
            ];

            return response()->json($response, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();
            $user = $this->model->find($id);
            $data = $user->delete();

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
}
