<?php

namespace App\Http\Controllers\Interview;

use App\Http\Controllers\Controller;
use App\Models\InterviewQuestion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InterviewQuestionBankController extends Controller
{
    /**
     * Display the Interview Question Bank page.
     *
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $search = (string) $request->query('search', '');
        $category = (string) $request->query('category', '');
        $difficulty = (string) $request->query('difficulty', '');

        $query = InterviewQuestion::query();

        if ($search !== '') {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($category !== '') {
            $query->where('category', $category);
        }

        if ($difficulty !== '') {
            $query->where('difficulty', $difficulty);
        }

        $questions = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $categories = InterviewQuestion::query()
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $difficulties = ['Easy', 'Medium', 'Hard'];

        return Inertia::render('interview/InterviewQuestionBank', [
            'filters' => [
                'search' => $search,
                'category' => $category,
                'difficulty' => $difficulty,
            ],
            'categories' => $categories,
            'difficulties' => $difficulties,
            'questions' => $questions->through(function (InterviewQuestion $q) {
                return [
                    'id' => $q->id,
                    'title' => $q->title,
                    'category' => $q->category,
                    'difficulty' => $q->difficulty,
                    'views_count' => $q->views_count,
                    'users_practiced_count' => $q->users_practiced_count,
                    'time_ago' => $q->time_ago,
                    'created_at' => $q->created_at,
                ];
            }),
        ]);
    }

    public function show(InterviewQuestion $question)
    {
        // Optionally increment views when viewing details
        $question->increment('views_count');

        return Inertia::render('interview/InterviewQuestion', [
            'question' => [
                'id' => $question->id,
                'title' => $question->title,
                'category' => $question->category,
                'difficulty' => $question->difficulty,
                'views_count' => $question->views_count,
                'users_practiced_count' => $question->users_practiced_count,
                'time_ago' => $question->time_ago,
                'explanation' => $question->explanation,
            ],
        ]);
    }
}
