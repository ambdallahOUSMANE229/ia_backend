<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /*Inscription d'un utilisateur*/
public function register(Request $request): JsonResponse
{
	$validated = $request->validate([
		'name' => ['required', 'string', 'max:225'],
		'email' => ['required', 'string', 'email', 'max:225', 'unique:users'],
		'password' => ['required', 'string', 'min:8', 'confirmed'],
]);

	$user = User::create([
		'name' => $request->name,
		'email' => $request->email,
		'password' => Hash::make($request->password),
]);

	$token = $user->createToken('auth_token')->plainTextToken;

	return response()->json([
		'message' => 'User registered successfully',
		'user' => $user,
		'token' => $token,
		'token_type' => 'Bearer',
	], 201);
}

/*Connexion d'un utilisateur*/

public function login(Request $request): JsonResponse
{
	$validated = $request->validate([
		'email' => ['required', 'email'],
		'password' => ['required', 'string'],
]);

	$user = User::where('email', $validated['email'])->first();

	if(!$user || !Hash::check($validated['password'], $user->password)){
		return response()->json([
			'message' => 'Invalid credentials.'
], 401);
}

$user->tokens()->delete();

$token = $user->createToken('auth_token')->plainTextToken;

return response()->json([
	'message' => 'Connexion réussie.',
	'user' => $user,
	'token' => $token,
	'token_type' => 'Bearer',
]);
}

/*Déconnexion*/

public function logout(Request $request): JsonResponse
{
	$request->user()->currentAccessToken()?->delete();

	return response()->json([
		'message' => 'Déconnexion réussie.',
]);
}

/*Utilisateur actuellment connecté*/

public function user(Request $request):JsonResponse
{
	return response()->json([
		'user' => $request->user(),
]);
}
}
