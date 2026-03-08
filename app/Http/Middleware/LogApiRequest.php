<?php

namespace App\Http\Middleware;

use App\Models\ApiLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $bearerToken = $request->bearerToken();
        $expectedKey = config('services.checkin.api_key');

        // Mask the tokens for display (show first 8 and last 4 chars)
        $maskedBearer = $bearerToken ? $this->maskToken($bearerToken) : null;

        // Redact selfie file data from payload
        $payload = $request->except('selfie');
        if ($request->hasFile('selfie')) {
            $file = $request->file('selfie');
            $payload['selfie'] = "(file: {$file->getClientOriginalName()}, {$file->getSize()} bytes)";
        }

        // Capture relevant headers
        $headers = [
            'Authorization' => $maskedBearer ? "Bearer {$maskedBearer}" : null,
            'Content-Type' => $request->header('Content-Type'),
            'Accept' => $request->header('Accept'),
            'User-Agent' => $request->header('User-Agent'),
        ];

        ApiLog::query()->create([
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'bearer_token' => $maskedBearer,
            'authenticated' => $bearerToken && $expectedKey && $bearerToken === $expectedKey,
            'payload' => $payload,
            'headers' => $headers,
            'response_status' => $response->getStatusCode(),
            'response_body' => mb_substr($response->getContent(), 0, 2000),
        ]);

        return $response;
    }

    private function maskToken(string $token): string
    {
        $length = strlen($token);
        if ($length <= 12) {
            return str_repeat('*', $length);
        }

        return substr($token, 0, 8).'...'.substr($token, -4);
    }
}
