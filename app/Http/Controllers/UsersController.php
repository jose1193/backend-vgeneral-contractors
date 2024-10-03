<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;
use App\Classes\ApiResponseClass;
use App\Http\Requests\CreateUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use App\Traits\HandlesApiErrors;
use App\DTOs\UserDTO;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UsersController extends BaseController
{
    use HandlesApiErrors;

    protected $userService;
    protected $cacheTime = 720;

    public function __construct(UserService $userService)
    {
        $this->middleware('check.permission:Super Admin')->only(['index', 'store', 'update', 'destroy', 'restore']);
        $this->userService = $userService;
    }

    public function index(): JsonResponse
    {
        try {
            $users = $this->userService->all();
            return ApiResponseClass::sendResponse(UserResource::collection($users), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error retrieving users');
        }
    }

    public function store(CreateUserRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $dto = UserDTO::fromArray($validatedData);
            $user = $this->userService->storeUser($dto);
            return ApiResponseClass::sendSimpleResponse(new UserResource($user), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error storing user');
        }
    }

    public function show(string $uuid): JsonResponse
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
            $user = $this->userService->showUser($uuidObject);
            return ApiResponseClass::sendSimpleResponse(new UserResource($user), 200);
        } catch (\Ramsey\Uuid\Exception\InvalidUuidStringException $e) {
            return $this->handleError($e, 'Invalid UUID format');
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error retrieving user');
        }
    }

    public function update(CreateUserRequest $request, string $uuid): JsonResponse
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
            $dto = UserDTO::fromArray($request->validated());
            $user = $this->userService->updateUser($dto, $uuidObject);
            return ApiResponseClass::sendSimpleResponse(new UserResource($user), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error updating user');
        }
    }

    public function destroy(string $uuid): JsonResponse
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
            $this->userService->deleteUser($uuidObject);
            return ApiResponseClass::sendResponse('User deleted successfully', '', 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error deleting user');
        }
    }

    public function restore(string $uuid): JsonResponse
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
            $user = $this->userService->restoreUser($uuidObject);
            return ApiResponseClass::sendSimpleResponse(new UserResource($user), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error restoring user');
        }
    }

    public function getUsersRoles(string $role): JsonResponse
    {
        try {
            $users = $this->userService->getUsersByRole($role);
            return ApiResponseClass::sendResponse(UserResource::collection($users), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error retrieving users by role');
        }
    }

        public function getTechnicalServices(): JsonResponse
    {
        try {
            $users = $this->userService->getUsersByRole('Technical Services');
            
            if ($users->isEmpty()) {
                return ApiResponseClass::sendResponse(null, 'No Technical Services found', 404);
            }
            
            return ApiResponseClass::sendResponse(UserResource::collection($users), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error retrieving Technical Services');
        }
    }


    // SYNC ROLES
    public function create()
    {
    $cacheKey = 'roles_list';
    $roles = $this->getCachedData($cacheKey, 360, function () {
        return Role::orderBy('id', 'DESC')->get();
    });
    return response()->json(['roles' => $roles], 200);
    }

    protected function getCachedData($key, $minutes, $callback)
    {
        return Cache::remember($key, $minutes * 60, $callback);
    }
}