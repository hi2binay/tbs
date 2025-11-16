<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::withCount('bookings')->latest()->paginate(15);

        return view('backend.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load(['bookings.reservation.event', 'bookings.payment']);

        return view('backend.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('backend.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20',
            'is_admin' => 'boolean',
        ]);

        $user->update($validated);

        return redirect()->route('backend.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('backend.users.index')
                ->with('error', 'You cannot delete yourself.');
        }

        $user->delete();

        return redirect()->route('backend.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
