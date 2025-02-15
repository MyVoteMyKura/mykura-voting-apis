<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Position;
use App\Models\Admin;
use Illuminate\Support\Facades\Gate;
use App\Models\Voter;

class AdminController extends Controller
{
    public function getVoters()
    {
        Gate::inspect('create', Admin::class);
        $voters = Voter::orderBy('id', 'desc')->get();
        return response()->json([
            'data' => $voters
        ]);
    }

    public function assignPosition(Request $request)
    {
        Gate::inspect('create', Admin::class);

        $validated = $request->validate([
            'voter_id' => 'required|numeric|max_digits:4|exists:voters,id',
            'position_id' => 'required|numeric|exists:positions,id'
        ]);

        try {

            $voter = Voter::findorFail($validated['voter_id']);

            $position = Position::findorFail($validated['position_id']);

            $voter->position()->associate($position);

            $voter->save();

            return response()->json([
                'status' => 'Success',
                'message' => 'Position assigned sucessfully',
                'data' => [
                    'voter' => $voter->load('position'),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => $e->getMessage(),
                'message' => 'Failed to assign position to voter'
            ], 500);
        }
    }

    public function adminLogin(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        try {

            $admin = null;

            $chunks = Admin::chunk(100, function ($admins) use ($validated, &$admin) {
                $admin = $admins->first(function ($admin) use ($validated) {
                    return $admin->email === $validated['email'];
                });
                if ($admin) {
                    return;
                }
            });

            if (!$admin) {
                return response()->json([
                    'message' => 'Invalid credentials'
                ], 401);
            }

            if (!password_verify($validated['password'], $admin->password)) {
                return response()->json([
                    'message' => 'Invalid credentials'
                ], 401);
            }

            $token = $admin->createToken('admin_token')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'bearer_token' => $token
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function createAdmin(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
            ]);

            $admin = Admin::create($validated);

            return response()->json([
                'status' => true,
                'message' => 'Admin created successfully',
                'data' => $admin
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function createPosition(Request $request)
    {
        Gate::inspect('create', Admin::class);
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        try {
            $position = Position::create($validated);

            return response()->json([
                'status' => 'Success',
                'message' => 'Position created sucessfully',
                'data' => [
                    'position' => $position
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => $e->getMessage(),
                'message' => 'Failed to create position'
            ], 500);
        }
    }
}
