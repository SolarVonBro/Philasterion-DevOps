<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DiaryEntryResource;
use App\Models\DiaryEntry;
use Illuminate\Http\Request;

class DiaryEntryController extends Controller
{
    public function index(Request $request)
    {
        $entries = $request->user()
            ->diaryEntries()
            ->orderByDesc('recorded_at')
            ->paginate(15);

        return DiaryEntryResource::collection($entries);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'recorded_at' => ['required', 'date'],
            'mood'        => ['required', 'integer', 'between:1,5'],
            'energy'      => ['required', 'integer', 'between:1,10'],
            'sleep_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'notes'       => ['nullable', 'string', 'max:2000'],
        ]);

        $entry = $request->user()->diaryEntries()->create($data);

        return new DiaryEntryResource($entry);
    }

    public function show(Request $request, DiaryEntry $diary)
    {
        $this->authorizeEntry($request, $diary);

        return new DiaryEntryResource($diary);
    }

    public function update(Request $request, DiaryEntry $diary)
    {
        $this->authorizeEntry($request, $diary);

        $data = $request->validate([
            'recorded_at' => ['sometimes', 'date'],
            'mood'        => ['sometimes', 'integer', 'between:1,5'],
            'energy'      => ['sometimes', 'integer', 'between:1,10'],
            'sleep_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'notes'       => ['nullable', 'string', 'max:2000'],
        ]);

        $diary->update($data);

        return new DiaryEntryResource($diary);
    }

    public function destroy(Request $request, DiaryEntry $diary)
    {
        $this->authorizeEntry($request, $diary);
        $diary->delete();

        return response()->json(['message' => 'Entry deleted.']);
    }

    private function authorizeEntry(Request $request, DiaryEntry $diary): void
    {
        if ($diary->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
