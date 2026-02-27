<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = User::with('roles')->get();
        $roles = Role::all();
        return view('employees.index', compact('employees', 'roles'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('employees.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);
        $user->assignRole($validated['role']);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Employee created successfully.']);
        }
        return redirect()->route('admin.employees.index')->with('success', 'Employee created.');
    }

    public function show(User $employee)
    {
        $employee->load('roles');
        return view('employees.show', compact('employee'));
    }

    public function edit(User $employee)
    {
        $roles = Role::all();
        $employee->load('roles');
        return view('employees.edit', compact('employee', 'roles'));
    }

    public function update(Request $request, User $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $employee->id,
            'phone' => 'nullable|string|max:20',
            'role' => 'required|exists:roles,name',
            'password' => 'nullable|min:8|confirmed',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $employee->update($data);
        $employee->syncRoles([$validated['role']]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Employee updated successfully.']);
        }
        return redirect()->route('admin.employees.index')->with('success', 'Employee updated.');
    }

    public function destroy(User $employee)
    {
        if ($employee->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Cannot delete your own account.'], 403);
        }
        $employee->delete();
        return response()->json(['success' => true, 'message' => 'Employee deleted.']);
    }

    public function toggleStatus(User $employee)
    {
        $employee->update(['is_active' => !$employee->is_active]);
        $status = $employee->is_active ? 'activated' : 'deactivated';
        return response()->json(['success' => true, 'message' => "Employee {$status}.", 'is_active' => $employee->is_active]);
    }
}
