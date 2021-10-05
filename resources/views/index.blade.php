@extends('layouts.app')

@section('content')

    <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">
        PHP Versions
    </h2>
    <p class="mt-4 text-gray-500">Provides API endpoints for support information for PHP versions 5.6 and later.</p>

    <p class="mt-4 text-gray-500">This is a Laravel-based application that leverages the GitHub Repositories API to stream release tags from the
        <a class="text-indigo-700 hover:text-indigo-900 underline" href="https://github.com/php/php-src">php/php-src</a> repo.
    </p>

    <p class="mt-4 text-gray-500">This project is heavily inspired by <a class="text-indigo-700 hover:text-indigo-900 underline" href="https://laravelversions.com/">LaravelVersions</a>.</p>


    @if ($showGraphic)
        <h3 class="text-2xl font-extrabold tracking-tight text-gray-900 mt-12">
            Currently Supported Versions
            <span class="text-xs ml-2 text-gray-600">From <a href="https://www.php.net/supported-versions.php" class="text-indigo-700 hover:text-indigo-900 underline">php.net</a></span>
        </h3>

        {!! \Illuminate\Support\Facades\Storage::disk('public')->get('supported-versions.svg') !!}
    @endif

    <h3 class="text-2xl font-extrabold tracking-tight text-gray-900 mt-12">
        Documentation
    </h3>

    <div id="markdown">
        {!!
            \Illuminate\Support\Str::of(
                \Illuminate\Support\Facades\File::get(__DIR__ . '/../../../ApiDocs.md')
            )->trim()->markdown()
        !!}
    </div>

@endsection
