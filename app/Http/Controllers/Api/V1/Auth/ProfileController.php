<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json($request->user()->only('name', 'email'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
           'name' => ['required', 'string'],
           'email' => ['required', 'email', Rule::unique('users')->ignore(auth()->user()) ]
        ]);

        auth()->user()->update($validated);

        return response()->json($validated, Response::HTTP_ACCEPTED);
    }
}
