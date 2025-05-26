@extends('admin.layouts.app')

@section('title', __('admin.ip_address_management'))

@section('content')
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 bg-white border-b border-gray-200">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold text-gray-700">{{ __('admin.ip_address_list') }}</h2>
            <form action="{{ route('admin.ip_addresses.clear-logs') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition-colors" 
                        onclick="return confirm('{{ __('admin.confirm_clear_logs') }}')">
                    {{ __('admin.clear_logs') }}
                </button>
            </form>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white p-4 rounded-lg shadow">
            <div class="alert alert-info mb-4 p-4 bg-blue-50 text-blue-700 border border-blue-200 rounded">
                {{ __('admin.ip_address_blocked_info') }}
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.ip_address') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.country') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.visit_count') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.last_visit') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($ipAddresses as $ip)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $ip->ip_address }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $ip->country }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $ip->visit_count }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $ip->last_visit }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    @if(in_array($ip->ip_address, $blockedIps))
                                        <form action="{{ route('admin.ip_addresses.unblock', $ip->ip_address) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-white bg-green-500 hover:bg-green-600 px-3 py-1 rounded text-xs"
                                                    onclick="return confirm('{{ __('admin.confirm_unblock_ip') }}')">
                                                {{ __('admin.unblock') }}
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.ip_addresses.block') }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="ip_address" value="{{ $ip->ip_address }}">
                                            <button type="submit" class="text-white bg-red-500 hover:bg-red-600 px-3 py-1 rounded text-xs"
                                                    onclick="return confirm('{{ __('admin.confirm_block_ip') }}')">
                                                {{ __('admin.block') }}
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $ipAddresses->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
