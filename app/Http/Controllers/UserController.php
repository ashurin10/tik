<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(10);
        return view('users.index', compact('users'));
    }

    public function store(StoreUserRequest $request)
    {
        User::create($request->validated());

        return redirect()->route('users.index')->with('status', 'User created successfully!');
    }

    public function show(User $user)
    {
        // unused
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        if ($request->has('unlock_account') && $request->unlock_account == '1') {
            $data['percobaan_gagal'] = 0;
            $data['terkunci_sampai'] = null;
        }

        if (!$request->filled('password')) {
            unset($data['password']); // don't write empty password
        }

        $user->update($data);

        return redirect()->route('users.index')->with('status', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        if ($user->email === 'admin@example.com') {
            return back()->withErrors(['message' => 'Cannot delete the main admin account.']);
        }

        $user->delete();
        return redirect()->route('users.index')->with('status', 'User deleted successfully!');
    }
}
