<?php

namespace App\Http\Controllers\EasyApply;

use App\Http\Controllers\Controller;
use App\Models\JobAutofillEvent;
use Inertia\Inertia;

class EasyApplyController extends Controller
{
    /**
     * Display the Easy Apply page.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $events = JobAutofillEvent::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10)
            ->through(function (JobAutofillEvent $e) {
                return [
                    'id' => $e->id,
                    'resume_variant' => $e->resume_variant,
                    'job_title' => $e->job_title,
                    'company' => $e->company,
                    'source_host' => $e->source_host,
                    'page_url' => $e->page_url,
                    'fields' => $e->fields,
                    'field_details' => $e->field_details,
                    'filled_count' => $e->filled_count,
                    'created_at' => $e->created_at?->toDateTimeString(),
                ];
            });

        return Inertia::render('easy-apply/EasyApply', [
            'events' => $events,
        ]);
    }
}
