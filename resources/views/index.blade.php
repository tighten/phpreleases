@extends('layouts.app')

@section('content')

    <p class="mt-4 text-gray-500">This is a Laravel-based application that leverages the GitHub Repositories API to stream release tags from the
        <a class="text-indigo-700 hover:text-indigo-900 underline" href="https://github.com/php/php-src">php/php-src</a> repo.
    </p>

    <p class="mt-4 text-gray-500">This project is heavily inspired by <a class="text-indigo-700 hover:text-indigo-900 underline" href="https://laravelversions.com/">LaravelVersions</a>.</p>

    <h3 class="text-2xl font-extrabold tracking-tight text-gray-900 mt-12">
        Currently Supported Versions
        <span class="text-xs ml-2 text-gray-600">From <a href="https://www.php.net/supported-versions.php" class="text-indigo-700 hover:text-indigo-900 underline">php.net</a></span>
    </h3>

    <div class="schedule-wrapper">
        {!! Storage::disk('public')->get('supported-versions.svg') !!}
    </div>

    @if (! $graphicUpdatedToday)
        <p class="text-xs ml-2 text-gray-600">Graphic updated {{ Carbon\Carbon::createFromTimestamp(Storage::disk('public')->lastModified('supported-versions.svg'))->toDateTimeString() }}</p>
    @endif

    <h3 class="text-2xl font-extrabold tracking-tight text-gray-900 mt-12">
        Documentation
    </h3>

    <div class="markdown">
        {!!
            Str::of(
                File::get(__DIR__ . '/../../../ApiDocs.md')
            )->trim()->markdown()
        !!}
    </div>

@endsection


@push('scripts')
    <script>
        window.onload = () => {
            const today = new Date();
            const date = {
                'day' : today.toLocaleString('default', { day: 'numeric' }),
                'month' : today.toLocaleString('default', { month: 'short' }),
                'year' : today.toLocaleString('default', { year: 'numeric'})
            };
            document.querySelectorAll('g.today > text')[0].innerHTML = `Today: ${date.day} ${date.month} ${date.year}`;
        }
    </script>
@endpush