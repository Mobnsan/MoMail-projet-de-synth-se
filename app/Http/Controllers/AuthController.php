<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Template;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        if (!$request->name || !$request->email || !$request->password) {
            return response()->json(['error' => 'Name, email and password are required.'], 400);
        }

        if (User::where('email', strtolower(trim($request->email)))->exists()) {
            return response()->json(['error' => 'An account with that email already exists.'], 409);
        }

        $user = User::create([
            'name' => trim($request->name),
            'email' => strtolower(trim($request->email)),
            'password_hash' => Hash::make($request->password),
        ]);

        Template::create([
            'ownerId' => $user->id,
            'name' => 'Welcome campaign',
            'senderName' => $user->name,
            'senderEmail' => $user->email,
            'subject' => 'Welcome to MoMail, {{name}}!',
            'body' => "Hi {{name}},\n\nThanks for joining MoMail. We help you create personalized email campaigns fast.\n\nBest,\n{{senderName}}",
        ]);

        Auth::login($user);

        return response()->json(['id' => $user->id, 'name' => $user->name, 'email' => $user->email]);
    }

    public function login(Request $request)
    {
        if (!$request->email || !$request->password) {
            return response()->json(['error' => 'Email and password are required.'], 400);
        }

        $user = User::where('email', strtolower(trim($request->email)))->first();

        if (!$user || !Hash::check($request->password, $user->password_hash)) {
            return response()->json(['error' => 'Invalid login credentials.'], 401);
        }

        Auth::login($user);

        return response()->json(['id' => $user->id, 'name' => $user->name, 'email' => $user->email]);
    }

    public function me(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Not authenticated.'], 401);
        }

        $user = Auth::user();
        return response()->json(['id' => $user->id, 'name' => $user->name, 'email' => $user->email]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return response()->json(['ok' => true]);
    }
}
