<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * GET /api/users
     * List all users with optional search.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by status
        // boolean(): "true"/"false"/"1"/"0" all work (a raw "true" string
        // compared as 0 in MySQL and returned the inactive users).
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $users = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Users retrieved successfully.',
            'data'    => $users,
        ], 200);
    }

    /**
     * GET /api/users/{id}
     * Get a single user by ID.
     */
    public function show(int $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'User retrieved successfully.',
            'data'    => $user,
        ], 200);
    }

    /**
     * POST /api/users
     * Create a new user.
     */
    public function store(Request $request): JsonResponse
    {
        // Validate incoming data
        $validated = $request->validate([
            'name'      => 'required|string|max:120',
            'email'     => 'required|email|max:191|unique:users,email',
            'password'  => 'required|string|min:8',
            'role'      => ['required', Rule::in(['super_admin', 'ceo', 'cenro'])],
            'office'    => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $user = User::create([
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'password_hash' => Hash::make($validated['password']),
            'role'          => $validated['role'],
            'office'        => $validated['office'] ?? null,
            'is_active'     => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully.',
            'data'    => $user,
        ], 201);
    }

    /**
     * PUT /api/users/{id}
     * Update an existing user.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        // Validate — email unique check ignores the current user's own email
        $validated = $request->validate([
            'name'      => 'sometimes|required|string|max:120',
            'email'     => ['sometimes', 'required', 'email', 'max:191', Rule::unique('users', 'email')->ignore($user->id)],
            'password'  => 'nullable|string|min:8',
            'role'      => ['sometimes', 'required', Rule::in(['super_admin', 'ceo', 'cenro'])],
            'office'    => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        // Build update data — only update what was sent
        $updateData = [];

        if (isset($validated['name']))      $updateData['name']      = $validated['name'];
        if (isset($validated['email']))     $updateData['email']     = $validated['email'];
        if (isset($validated['role']))      $updateData['role']      = $validated['role'];
        // array_key_exists, not isset: sending office = null must clear it.
        if (array_key_exists('office', $validated)) $updateData['office'] = $validated['office'];
        if (isset($validated['is_active'])) $updateData['is_active'] = $validated['is_active'];

        // Only update password if a new one was provided
        if (! empty($validated['password'])) {
            $updateData['password_hash'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        // A deactivated account or a reset password must not keep working
        // through tokens issued before the change.
        if ((array_key_exists('is_active', $updateData) && ! $updateData['is_active'])
            || isset($updateData['password_hash'])) {
            $user->tokens()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully.',
            'data'    => $user->fresh(), // fresh() reloads from DB
        ], 200);
    }

    /**
     * DELETE /api/users/{id}
     * Delete a user.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        // Prevent the logged-in admin from deleting their own account
        if ($request->user()->id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account.',
            ], 403);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.',
        ], 200);
    }
}
