<?php

namespace App\Http\Controllers;

use App\Models\Release;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReleaseController
{
    public function index()
    {
        return response()->json(
            Release::orderByDesc('major')
                ->orderByDesc('minor')
                ->orderByDesc('release')
                ->get()
        );
    }

    public function minimumSupported(string $supportType = 'active')
    {
        return response()->json(
            Release::where("{$supportType}_support_until", '>', Carbon::now())
                ->orderBy('major')
                ->orderBy('minor')
                ->orderByDesc('release')
                ->first()
        );
    }

    public function showLatest()
    {
        return response()->json(
            (string) Release::orderByDesc('major')
                ->orderByDesc('minor')
                ->orderByDesc('release')
                ->first()
        );
    }

    public function show(Request $request)
    {
        $release = explode('.', $request->release);

        if (count($release) === 3) {
            $provided = Release::firstWhere([
                'major' => $release[0],
                'minor' => $release[1],
                'release' => $release[2],
            ]);

            return response()->json([
                'provided' => $provided,
                'latest_release' => (string) Release::orderByDesc('major')
                    ->orderByDesc('minor')
                    ->orderByDesc('release')
                    ->first(),
            ]);
        }

        return response()->json(
            Release::query()
                ->when(array_key_exists(1, $release), function ($query) use ($release) {
                    $query->where('major', $release[0])
                        ->where('minor', $release[1]);
                })
                ->when(array_key_exists(0, $release), function ($query) use ($release) {
                    $query->where('major', $release[0]);
                })
                ->get()
        );
    }
}
