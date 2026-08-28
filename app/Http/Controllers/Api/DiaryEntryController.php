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

    public function stats(Request $request)
    {
        $entries = $request->user()->diaryEntries()->get();

        if ($entries->isEmpty()) {
            return response()->json([
                'average_mood' => null,
                'average_energy' => null,
                'average_sleep_hours' => null,
                'total_entries' => 0,
            ]);
        }

        $moodSum = 0;
        $energySum = 0;
        $sleepSum = 0;
        $sleepCount = 0;

        foreach ($entries as $entry) {
            $moodSum += $entry->mood;
            $energySum += $entry->energy;

            if ($entry->sleep_hours !== null) {
                $sleepSum += $entry->sleep_hours;
                $sleepCount++;
            }
        }

        $count = $entries->count();

        return response()->json([
            'average_mood' => round($moodSum / $count, 2),
            'average_energy' => round($energySum / $count, 2),
            'average_sleep_hours' => $sleepCount > 0 ? round($sleepSum / $sleepCount, 2) : null,
            'total_entries' => $count,
        ]);
    }
}
