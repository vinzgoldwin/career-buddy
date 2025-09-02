<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobAutofillEvent;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class AutofillEventController extends Controller
{
    public function storeSigned(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user' => ['required', 'integer', 'min:1'],
            'expires' => ['required', 'integer'],
            'signature' => ['required', 'string'],

            'resume_variant' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'source_host' => ['nullable', 'string', 'max:255'],
            'page_url' => ['nullable', 'string'],
            'fields' => ['nullable', 'array'],
            'fields.*' => ['string'],
            'field_details' => ['nullable', 'array'],
            'field_details.*' => ['array'],
            'filled_count' => ['nullable', 'integer', 'min:0'],
        ]);

        // Validate signature against the profile signed route using provided params
        $expiresTs = (int) $data['expires'];
        abort_if($expiresTs < now()->getTimestamp(), 403, 'Signature expired');

        $expectedUrl = URL::temporarySignedRoute(
            'api.profile.signed',
            CarbonImmutable::createFromTimestamp($expiresTs),
            ['user' => (int) $data['user']]
        );

        $expectedQuery = [];
        parse_str(parse_url($expectedUrl, PHP_URL_QUERY) ?: '', $expectedQuery);
        $expectedSignature = $expectedQuery['signature'] ?? '';

        abort_unless(hash_equals((string) $expectedSignature, (string) $data['signature']), 403, 'Invalid signature');

        $event = JobAutofillEvent::query()->create([
            'user_id' => (int) $data['user'],
            'resume_variant' => $data['resume_variant'] ?? null,
            'job_title' => $data['job_title'] ?? null,
            'company' => $data['company'] ?? null,
            'source_host' => $data['source_host'] ?? null,
            'page_url' => $data['page_url'] ?? null,
            'fields' => $data['fields'] ?? null,
            'field_details' => $data['field_details'] ?? null,
            'filled_count' => (int) ($data['filled_count'] ?? 0),
        ]);

        return response()->json([
            'ok' => true,
            'id' => $event->id,
        ]);
    }
}
