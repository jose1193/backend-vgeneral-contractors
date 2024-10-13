<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use App\Http\Requests\SocialLoginRequest;
use App\Services\SocialLoginService;
use App\DTOs\SocialLoginDTO;
use App\Classes\ApiResponseClass;
use App\Traits\HandlesApiErrors;

class SocialLoginController extends BaseController
{
    use HandlesApiErrors;

    protected $socialLoginService;

    public function __construct(SocialLoginService $socialLoginService)
    {
        $this->socialLoginService = $socialLoginService;
    }

    public function handleProviderCallback(SocialLoginRequest $request)
    {
        try {
            $dto = SocialLoginDTO::fromArray($request->validated());
            $result = $this->socialLoginService->handleProviderCallback($dto);
            
            return ApiResponseClass::sendResponse([
                'message' => 'User logged successfully',
                'token' => $result['token']['token'],
                'token_type' => 'Bearer',
                'token_created_at' => $result['token']['created_at'],
                'user' => $result['userResource'],
            ], 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error during social login');
        }
    }
}