<?php


namespace App\Http\Controllers\Api\Auth;


use App\Business\AuthManager;
use Illuminate\Http\Request;

class RegisterController
{
    public function __invoke(Request $request, AuthManager $authManager)
    {
        $validated = $request->validate([
            'u_id' => 'string|required',
            'app_id' => 'string|required',
            'language' => 'string|in:en,tr|required',
            'operating_system' => 'string|in:ios,android|required',
            'callback_url' => 'string|required'
        ]);

        $token = $authManager->register($validated);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('device')->factory()->getTTL() * 60
        ]);
    }
}
