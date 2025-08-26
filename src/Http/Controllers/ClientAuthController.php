<?php

namespace App\Http\Controllers\ClientForAuthenticationProvider;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use UnexpectedValueException;

class ClientAuthController extends Controller
{
    /**
     * Handle mini app login with token verification
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function miniAppCreateOrLoginUser(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_data' => 'required',
                'user_data.email' => 'required|email',
                'user_data.mobile' => 'required|string',
                'user_data.first_name' => 'required|string',
                'user_data.last_name' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Validation failed', 
                    'errors' => $validator->errors()
                ]);
            }

            $userEmail = $request->user_data['email'];
            $userModel = config('auth.providers.users.model', 'App\Models\User');
            $existingUser = $userModel::where('email', $userEmail)->first();
            
            if ($existingUser) {
                $updateExistingUser = [
                    'status' => 'approved',
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                ];

                if (empty($existingUser->phone)) {
                    $updateExistingUser['phone'] = $request->user_data['mobile'];
                }

                if (empty($existingUser->email)) {
                    $updateExistingUser['email'] = $request->user_data['email'];
                }

                $existingUser->update($updateExistingUser);
                $existingUser->refresh();
                $jwtToken = $existingUser->createToken('token')->plainTextToken;
                
                return response()->json([
                    'success' => true,
                    'token' => $jwtToken,
                    'data' => $existingUser
                ]);
            }

            return $this->createNewUserFromVerificationData($request->user_data);
        } catch (UnexpectedValueException $e) {
            return response()->json(['success' => false, 'message' => 'Invalid token']);
        } catch (Exception $e) {
            Log::error('Mini App Login Error: ' . $e->getTraceAsString());
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Create new user from verification data
     *
     * @param array $userData
     * @return \Illuminate\Http\JsonResponse
     */
    private function createNewUserFromVerificationData(array $userData): JsonResponse
    {
        try {
            DB::beginTransaction();
            
            $config = config('client-for-authentication-provider');
            $countryCode = $userData['country_code'] ?? $config['default_country_code'];
            $userData['mobile'] = str_replace($countryCode, '', $userData['mobile']);
            
            $userModel = config('auth.providers.users.model', 'App\Models\User');
            
            $newUser = [
                'name' => $userData['first_name'] . ' ' . $userData['last_name'],
                'role_id' => $config['default_role_id'],
                'email' => $userData['email'],
                'phone' => $userData['mobile'],
                'country_code' => $countryCode,
                'country_iso_code' => $userData['country_iso_code'] ?? $config['default_country_iso_code'],
                'provider' => $config['provider_name'],
                'status' => 'approved',
                'provider_user_id' => $userData['id'] ?? $userData['email'],
                'password' => $userData['password'] ?? bcrypt(str_random(16)),
                'is_mealsmash_pending' => false,
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ];

            $createdUser = $userModel::create($newUser);
            $createdUser->refresh();

            $jwtToken = $createdUser->createToken('token')->plainTextToken;

            DB::commit();

            return response()->json([
                'success' => true,
                'token' => $jwtToken,
                'data' => $createdUser
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Mini App Login Error: ' . $e->getTraceAsString());
            throw $e;
        }
    }
}
