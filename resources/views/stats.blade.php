@extends('layouts.app')

@section('content')
    <dl class="grid grid-cols-1 gap-5 mt-5 sm:grid-cols-4">
        <div class="px-4 py-5 overflow-hidden bg-white rounded-lg shadow sm:p-6">
            <dt class="text-sm font-medium text-gray-500 truncate">Last Week</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">
                <h3>{{ $week['current'] }} hits</h3>
                <p class="text-sm text-gray-500">Previous: {{ $week['previous'] }}</p>
                <p class="text-sm text-gray-500">Percent Change: {{ $week['changePercent'] }}%</p>
            </dd>
        </div>

        <div class="px-4 py-5 overflow-hidden bg-white rounded-lg shadow sm:p-6">
            <dt class="text-sm font-medium text-gray-500 truncate">Last Month</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">
                <h3>{{ $month['current'] }} hits</h3>
                <p class="text-sm text-gray-500">Previous: {{ $month['previous'] }}</p>
                <p class="text-sm text-gray-500">Percent Change: {{ $month['changePercent'] }}%</p>
            </dd>
        </div>

        <div class="px-4 py-5 overflow-hidden bg-white rounded-lg shadow sm:p-6">
            <dt class="text-sm font-medium text-gray-500 truncate">All Time</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">
                <h3>{{ $year['current'] }} hits</h3>
                <p class="text-sm text-gray-500">Previous: {{ $year['previous'] }}</p>
                <p class="text-sm text-gray-500">Percent Change: {{ $year['changePercent'] }}%</p>
            </dd>
        </div>

        <div class="px-4 py-5 overflow-hidden bg-white rounded-lg shadow sm:p-6">
            <dt class="text-sm font-medium text-gray-500 truncate">Top Endpoints <span class="text-xs">(All Time)</span></dt>
            <dd class="mt-1 text-xs font-semibold tracking-tight text-gray-900">
                @foreach ($top as $topHit)
                    <div class="flex justify-between my-2">
                        <p>{{ $topHit->endpoint }}</p>
                        <p>{{ $topHit->count }}</p>
                    </div>
                @endforeach
            </dd>
        </div>
    </dl>


    <table class="min-w-full mt-4 divide-y divide-gray-300">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Endpoint</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">User Agent</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Referer</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">IP</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Date</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach ($hits as $hit)
                <tr>
                    <td class="py-4 pl-4 text-sm text-gray-500 whitespace-nowrap">{{ $hit->endpoint }}</td>
                    <td class="px-3 py-4 text-sm text-gray-500">{{ $hit->user_agent }}</td>
                    <td class="px-3 py-4 text-sm text-gray-500">{{ $hit->referer }}</td>
                    <td class="px-3 py-4 text-sm text-gray-500">{{ $hit->ip }}</td>
                    <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $hit->created_at }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $hits->links() }}
@endsection
