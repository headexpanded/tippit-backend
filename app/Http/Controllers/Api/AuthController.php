<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function redirectToGoogle(): JsonResponse
    {

        $url = Socialite::driver('google')
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        return response()->json(['url' => $url]);
    }

    /**
     * @return JsonResponse
     */
    public function handleGoogleCallback(): JsonResponse
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::updateOrCreate([
                'email' => $googleUser->email,
            ], [
                'name' => $googleUser->name,
                'google_id' => $googleUser->id,
                'password' => Hash::make(Str::random(24)),
            ]);

            $token = $user->createToken('google_token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Google authentication failed'], 401);
        }
    }

    /**
     * @param  Request  $request
     *
     * @return JsonResponse
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();
        $user->sendOneTimePassword();

        return response()->json(['message' => 'OTP sent successfully']);
    }

    /**
     * @param  Request  $request
     *
     * @return JsonResponse
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();
        $result = $user->attemptLoginUsingOneTimePassword($request->otp);

        if ($result->isOk()) {
            $token = $user->createToken('otp_token')->plainTextToken;
            return response()->json([
                'user' => $user,
                'token' => $token
            ]);
        }

        return response()->json([
            'error' => $result->validationMessage()
        ], 401);
    }

    /**
     * @param  Request  $request
     *
     * @return JsonResponse
     */
    public function getPasskeyOptions(Request $request): JsonResponse
    {
        $user = Auth::user();

        $publicKeyCredentialCreationOptions = PublicKeyCredentialCreationOptions::create(
            new PublicKeyCredentialRpEntity(
                config('app.name'),
                config('app.url'),
                null
            ),
            new PublicKeyCredentialUserEntity(
                $user->email,
                $user->id,
                $user->name
            ),
            null,
            null
        );

        session(['passkey_options' => $publicKeyCredentialCreationOptions]);

        return response()->json($publicKeyCredentialCreationOptions);
    }

    /**
     * @param  Request  $request
     *
     * @return JsonResponse
     */
    public function registerPasskey(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'credential' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $publicKeyCredentialSource = PublicKeyCredentialSource::createFromArray($request->credential);

            $user = Auth::user();
            $user->passkey_credentials = array_merge(
                $user->passkey_credentials ?? [],
                [$publicKeyCredentialSource->jsonSerialize()]
            );
            $user->save();

            return response()->json(['message' => 'Passkey registered successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => 'Passkey registration failed'], 422);
        }
    }

    /**
     * @return JsonResponse
     */
    public function getPasskeyAuthenticationOptions(): JsonResponse
    {
        $publicKeyCredentialRequestOptions = PublicKeyCredentialRequestOptions::create(
            null,
            null,
            null,
            null
        );

        session(['passkey_auth_options' => $publicKeyCredentialRequestOptions]);

        return response()->json($publicKeyCredentialRequestOptions);
    }

    /**
     * @param  Request  $request
     *
     * @return JsonResponse
     */
    public function authenticateWithPasskey(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'credential' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $publicKeyCredentialSource = PublicKeyCredentialSource::createFromArray($request->credential);

            $user = User::whereJsonContains('passkey_credentials', $publicKeyCredentialSource->jsonSerialize())
                ->first();

            if (!$user) {
                return response()->json(['error' => 'Invalid passkey'], 401);
            }

            $token = $user->createToken('passkey_token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Passkey authentication failed'], 401);
        }
    }
}
