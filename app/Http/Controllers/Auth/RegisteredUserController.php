<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        abort_unless(config('registration.code'), 404);
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(config('registration.code'), 404);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', 'min:10'],
            'registration_code' => ['required', Rule::in([config('registration.code')])],
        ]);

        $user = User::create(['name' => $data['name'], 'email' => $data['email'], 'password' => Hash::make($data['password'])]);
        event(new Registered($user));

        $department = Department::firstOrCreate(['name' => 'Seksi Aplikasi, Siber dan Statistik']);
        $department->members()->attach($user, ['role' => $department->members()->exists() ? 'member' : 'owner']);
        Auth::login($user);

        return redirect()->route('projects.index');
    }
}
