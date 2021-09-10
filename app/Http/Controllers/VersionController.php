<?php

namespace App\Http\Controllers;

use App\Models\Version;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VersionController
{
    public function index()
    {
        return response()->json(
            Version::orderByDesc('major')
                ->orderByDesc('minor')
                ->orderByDesc('release')
                ->get()
        );
    }

    public function minimumSupported(string $supportType = 'active')
    {
        return response()->json(
            Version::where("{$supportType}_support_until", '>', Carbon::now())
                ->orderBy('major')
                ->orderBy('minor')
                ->orderByDesc('release')
                ->first()
        );
    }

    public function showLatest()
    {
        return response()->json(
            (string) Version::orderByDesc('major')
            ->orderByDesc('minor')
            ->orderByDesc('release')
            ->first()
        );
    }

    public function show(Request $request)
    {
        $version = explode('.', $request->version);

        if (array_key_exists(2, $version)) {
            $provided = Version::firstWhere([
                    'major' => $version[0],
                    'minor' => $version[1],
                    'release' => $version[2],
            ]);

            return response()->json([
                'provided' => $provided,
                'latest_release' => (string) Version::orderByDesc('major')
                    ->orderByDesc('minor')
                    ->orderByDesc('release')
                    ->first(),
            ]);
        }

        return response()->json(
            Version::query()
                ->when(array_key_exists(1, $version), function ($query) use ($version) {
                    $query->where('major', $version[0])
                        ->where('minor', $version[1]);
                })
                ->when(array_key_exists(0, $version), function ($query) use ($version) {
                    $query->where('major', $version[0]);
                })
                ->get()
        );
    }
}
