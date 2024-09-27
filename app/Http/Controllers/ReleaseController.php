<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReleaseResource;
use App\Models\Release;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ReleaseController
{
    public function index()
    {
        return ReleaseResource::collection(Release::orderByDesc('tagged_at')->get());
    }

    public function minimumSupported(string $supportType = 'active')
    {
        request()
            ->merge(['supportType' => $supportType])
            ->validate([
                'supportType' => 'nullable|' . Rule::in(['active', 'security']),
            ]);

        return new ReleaseResource(
            Release::where("{$supportType}_support_until", '>', Carbon::now())
                ->orderBy('major')
                ->orderBy('minor')
                ->orderByDesc('release')
                ->first()
        );
    }

    public function showLatest(): JsonResponse
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

        $validator = Validator::make([
            'major' => $release[0],
            'minor' => $release[1] ?? null,
            'release' => $release[2] ?? null,
        ], [
            'major' => ['required', 'regex:/^[\',"]?[5-9]|1[0-1][\',"]?$/'],
            'minor' => ['nullable', 'regex:/^[\',"]?[0-9]{1,3}[\',"]?$/'],
            'release' => ['nullable', 'regex:/^[\',"]?[0-9]{1,3}[\',"]?$/'],
        ])->validate();

        if (count($release) === 3) {
            $provided = Release::firstWhere([
                'major' => $validator['major'],
                'minor' => $validator['minor'],
                'release' => $validator['release'],
            ]);

            return new ReleaseResource($provided);
        }

        return ReleaseResource::collection(
            Release::query()
                ->when(array_key_exists(1, $release), function ($query) use ($validator) {
                    $query->where('major', $validator['major'])
                        ->where('minor', $validator['minor']);
                })
                ->when(array_key_exists(0, $release), function ($query) use ($validator) {
                    $query->where('major', $validator['major']);
                })
                ->orderByDesc('tagged_at')
                ->get()
        );
    }
}
