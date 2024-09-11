<?php

namespace App\Repositories;

use App\Models\DocusignClaim;
use App\Models\DocusignToken;
use App\Models\Claim;
use App\Interfaces\DocuSignRepositoryInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class DocuSignRepository implements DocuSignRepositoryInterface
{
    private $config;

    public function __construct()
    {
        $this->config = $this->getDocusignConfig();
    }

    public function index(): Collection
    {
        return DocusignClaim::with('claim')->orderBy('id', 'DESC')->get();
    }

    public function getByUuid(string $uuid): DocusignClaim
    {
        return DocusignClaim::where('uuid', $uuid)->firstOrFail();
    }

    public function delete(string $uuid): DocusignClaim
    {
        $docusign = $this->getByUuid($uuid);
        $docusign->delete();
        return $docusign;
    }

    public function getByUser($user): Collection
    {
        if ($user->hasPermissionTo('Super Admin', 'api')) {
            return DocusignClaim::orderBy('id', 'DESC')->get();
        }
        return DocusignClaim::where('uploaded_by', $user)->orderBy('id', 'DESC')->get();
    }

    public function connectDocusign(): array
    {
        if (DocusignToken::exists()) {
            return ['message' => 'A DocuSign token already exists.', 'status' => 'exists'];
        }

        $params = [
            'response_type' => 'code',
            'scope' => 'signature',
            'client_id' => $this->config['client_id'],
            'redirect_uri' => $this->config['redirect_uri'],
        ];

        $url = $this->config['api_auth_url'] . "/auth?" . http_build_query($params);
        return ['url' => $url];
    }

    public function callbackDocusign(string $code, string $token_uuid, int $userId): array
    {
        $response = $this->getTokenFromDocuSign($code);

        if ($response->successful()) {
            $result = $response->json();
            $userInfo = $this->getUserInfo($result['access_token']);

            if ($userInfo) {
                $this->storeDocusignToken([
                    'uuid' => $token_uuid,
                    'access_token' => $result['access_token'],
                    'refresh_token' => $result['refresh_token'],
                    'expires_at' => now()->addSeconds($result['expires_in']),
                    'connected_by' => $userId,
                    'email_docusign' => $userInfo['email'],
                    'name' => $userInfo['name'] ?? null,
                    'first_name' => $userInfo['given_name'] ?? null,
                    'last_name' => $userInfo['family_name'] ?? null
                ]);

                return [
                    'message' => 'DocuSign successfully connected',
                    'name' => $userInfo['name'] ?? null,
                    'email' => $userInfo['email'],
                    'access_token' => $result['access_token'],
                    'refresh_token' => $result['refresh_token'],
                    'expires_in' => $result['expires_in'],
                    'scope' => $result['scope'],
                ];
            }
        }

        return ['error' => 'Authentication failed', 'details' => $response->json()];
    }

    public function storeDocusignToken(array $data): DocusignToken
    {
        return DocusignToken::create($data);
    }

    public function getClaimByUuid(string $uuid): Claim
    {
        return Claim::with('property.customers')->where('uuid', $uuid)->firstOrFail();
    }

    public function sendDocumentToDocusign(string $accessToken, array $envelopeDefinition): array
    {
        $response = Http::withToken($accessToken)
            ->post("{$this->config['api_base_url']}/accounts/{$this->config['account_id']}/envelopes", $envelopeDefinition);

        if ($response->successful()) {
            return [
                'message' => 'Document sent for validation',
                'envelope_id' => $response->json('envelopeId'),
            ];
        }

        throw new \Exception('Failed to send document to DocuSign: ' . json_encode($response->json()));
    }

    public function storeDocusign(array $data): DocusignClaim
    {
        return DocusignClaim::create($data);
    }

    public function getAccessToken(int $userId): ?string
    {
        $token = DocusignToken::where('connected_by', $userId)->first();
        return ($token && $token->expires_at > now()) ? $token->refresh_token : null;
    }

    public function updateTokens(int $userId, array $tokens): void
    {
        DocusignToken::where('connected_by', $userId)->update([
            'access_token' => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'],
            'expires_at' => now()->addSeconds($tokens['expires_in']),
        ]);
    }

    public function checkDocumentStatus(string $accessToken, string $envelopeId): array
    {
        $response = Http::withToken($accessToken)
            ->get("{$this->config['api_base_url']}/accounts/{$this->config['account_id']}/envelopes/{$envelopeId}");

        if ($response->successful()) {
            return [
                'status' => $response->json('status'),
                'details' => $response->json(),
            ];
        }

        return [
            'error' => 'Failed to retrieve document status',
            'details' => $response->json(),
        ];
    }

    public function getAllDocumentStatuses(string $accessToken): array
    {
        $params = [
            'from_date' => now()->subDays(30)->format('Y-m-d'),
            'status' => 'completed',
        ];

        $response = Http::withToken($accessToken)
            ->get("{$this->config['api_base_url']}/accounts/{$this->config['account_id']}/envelopes", $params);

        if ($response->successful()) {
            $documents = $response->json('envelopes');
            usort($documents, fn($a, $b) => strtotime($b['createdDateTime']) - strtotime($a['createdDateTime']));

            return [
                'message' => 'Document statuses retrieved successfully',
                'documents' => $documents
            ];
        }

        return [
            'error' => 'Failed to retrieve document statuses',
            'details' => $response->json(),
        ];
    }

    public function getDocusignAccount(): ?DocusignToken
    {
        return DocusignToken::first();
    }

    public function getDocusignConfig(): array
    {
        return [
            'client_id' => env('DOCUSIGN_INTEGRATOR_KEY'),
            'client_secret' => env('DOCUSIGN_CLIENT_SECRET'),
            'redirect_uri' => env('DOCUSIGN_REDIRECT_URI'),
            'account_id' => env('DOCUSIGN_ACCOUNT_ID'),
            'api_auth_url' => env('DOCUSIGN_AUTH_SERVER'),
            'api_base_url' => env('DOCUSIGN_API_BASE_URL')
        ];
    }

    private function getTokenFromDocuSign(string $code)
    {
        return Http::withBasicAuth($this->config['client_id'], $this->config['client_secret'])
            ->post($this->config['api_auth_url'] . '/token', [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $this->config['redirect_uri'],
            ]);
    }

    private function getUserInfo(string $accessToken): ?array
    {
        $response = Http::withToken($accessToken)->get($this->config['api_auth_url'] . '/userinfo');
        return $response->successful() ? $response->json() : null;
    }
}