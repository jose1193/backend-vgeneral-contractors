<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;
use App\Classes\ApiResponseClass;
use App\Http\Requests\AuthRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Traits\HandlesApiErrors;
use App\DTOs\AuthDTO;
use App\Http\Requests\PasswordUpdateRequest;
use App\DTOs\PasswordUpdateDTO;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;

class AuthController extends BaseController
{
    use HandlesApiErrors;

    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Authenticate a user.
     */
    public function login(AuthRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $dto = AuthDTO::fromArray($validatedData);
            $result = $this->authService->authenticateUser($dto);

            // Formatear la respuesta según lo solicitado
            $response = [
            'message' => 'User logged successfully',
            'token' => $result['token'],
            'token_type' => 'Bearer',
            'token_created_at' => now()->format('Y-m-d H:i:s'),
            'user' => new UserResource($result['user']) 
            ];

            return ApiResponseClass::sendSimpleResponse($response, 200);
            } catch (\Exception $e) {
            return $this->handleError($e, 'Error authenticating user');
            }
    }


    /**
     * Log out the authenticated user.
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $this->authService->logoutUser($request->user());
            return ApiResponseClass::sendResponse('User logged out successfully', '', 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error logging out user');
        }
    }

    /**
     * Get the authenticated user's details.
     */
    public function user(Request $request): JsonResponse
    {
        try {
            $user = $this->authService->getAuthenticatedUser($request->user()->id);
            return ApiResponseClass::sendSimpleResponse(new UserResource($user), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error retrieving user details');
        }
    }

    /**
     * Update the authenticated user's password.
     */
    public function updatePassword(PasswordUpdateRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $dto = PasswordUpdateDTO::fromArray($validatedData);
            $this->authService->updateUserPassword($dto, $request->user());
            
            return response()->json(['success' => 'Password updated successfully']);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error updating password');
        }
    }

    /**
     * Check email availability.
     */
    public function checkEmailAvailability(Request $request, string $email): JsonResponse
    {
        try {
            $result = $this->authService->checkEmailAvailability($email, $request->user());
            return ApiResponseClass::sendResponse($result, 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error checking email availability');
        }
    }

    /**
     * Check username availability.
     */
    public function checkUsernameAvailability(Request $request, string $username): JsonResponse
    {
        try {
            $result = $this->authService->checkUsernameAvailability($username, $request->user());
            return ApiResponseClass::sendResponse($result, 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error checking username availability');
        }
    }
}