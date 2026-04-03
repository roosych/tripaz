<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * List all users with their current roles.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $search = $request->input('search');

        $users = User::query()
            ->with('roles')
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        $roles = Role::orderBy('name')->pluck('name');

        return view('admin.users.index', compact('users', 'roles', 'search'));
    }

    /**
     * Update the role assigned to a user.
     * An admin may assign any single role from the defined set.
     */
    public function updateRole(Request $request, User $user)
    {
        $this->authorize('updateRole', $user);

        $validated = $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        // syncRoles replaces all current roles with the one given — one role per user.
        $user->syncRoles([$validated['role']]);

        return back()->with('success', "User [{$user->email}] role updated to [{$validated['role']}].");
    }
}
