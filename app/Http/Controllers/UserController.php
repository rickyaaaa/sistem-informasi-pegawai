<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Satker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::with('satker')->latest();

        // Search
        if ($request->filled('q')) {
            $q = trim($request->input('q'));
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('username', 'like', "%{$q}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $users   = $query->paginate(15)->withQueryString();
        $satkers = Satker::where('level', 'induk')->orderBy('nama_satker')->get();

        return view('users.index', compact('users', 'satkers'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $satkers = Satker::where('level', 'induk')->orderBy('nama_satker')->get();
        return view('users.create', compact('satkers'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'username'  => ['required', 'string', 'max:255', 'unique:users,username'],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
            'role'      => ['required', Rule::in(['super_admin', 'admin_satker'])],
            'satker_id' => ['nullable', 'integer', 'exists:satkers,id'],
            'status'    => ['required', Rule::in(['active', 'inactive'])],
        ]);

        // Satker is required for admin_satker
        if ($validated['role'] === 'admin_satker' && empty($validated['satker_id'])) {
            return back()
                ->withErrors(['satker_id' => 'Satker wajib dipilih untuk Operator.'])
                ->withInput();
        }

        // Super admin doesn't need satker
        if ($validated['role'] === 'super_admin') {
            $validated['satker_id'] = null;
        }

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Show the form for editing a user.
     */
    public function edit(User $user)
    {
        $satkers = Satker::where('level', 'induk')->orderBy('nama_satker')->get();
        return view('users.edit', compact('user', 'satkers'));
    }

    /**
     * Update an existing user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'username'  => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'password'  => ['nullable', 'string', 'min:8', 'confirmed'],
            'role'      => ['required', Rule::in(['super_admin', 'admin_satker'])],
            'satker_id' => ['nullable', 'integer', 'exists:satkers,id'],
            'status'    => ['required', Rule::in(['active', 'inactive'])],
        ]);

        // Satker required for admin_satker
        if ($validated['role'] === 'admin_satker' && empty($validated['satker_id'])) {
            return back()
                ->withErrors(['satker_id' => 'Satker wajib dipilih untuk Operator.'])
                ->withInput();
        }

        if ($validated['role'] === 'super_admin') {
            $validated['satker_id'] = null;
        }

        // Only update password if provided
        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Delete a user. Super Admin cannot be deleted.
     */
    public function destroy(User $user)
    {
        // Prevent deleting super admin
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Super Admin tidak dapat dihapus.');
        }

        // Prevent deleting own account
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        // Delete related PegawaiRequests first (since SQLite might not have ON DELETE CASCADE)
        \App\Models\PegawaiRequest::where('requested_by', $user->id)->delete();
        \App\Models\PegawaiRequest::where('approved_by', $user->id)->update(['approved_by' => null]);

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil dihapus.');
    }

    /**
     * Toggle user active/inactive status quickly.
     */
    public function toggleStatus(User $user)
    {
        // Prevent deactivating own account or super admin
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $user->update([
            'status' => $user->status === 'active' ? 'inactive' : 'active',
        ]);

        $label = $user->status === 'active' ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "User berhasil {$label}.");
    }
}
