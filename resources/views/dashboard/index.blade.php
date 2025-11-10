<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <div class="bg-white overflow-hidden shadow-md rounded-lg">
                    <div class="p-5 flex items-center">
                        <div class="flex-shrink-0 bg-primary-100 rounded-full p-3">
                            <svg class="h-6 w-6 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M3.9 12c0-1.71.5-3.3 1.3-4.7C6.8 4.8 9.2 3 12 3s5.2 1.8 6.8 4.3c.8 1.4 1.3 3 1.3 4.7s-.5 3.3-1.3 4.7C17.2 19.2 14.8 21 12 21s-5.2-1.8-6.8-4.3C4.4 15.3 3.9 13.7 3.9 12zM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" />
                            </svg>
                        </div>
                        <div class="ml-4 w-0 flex-1">
                            <p class="text-sm font-medium text-gray-500 truncate">
                                Suhu Udara
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ $analytics['temperature']['avg'] ?? '28' }} <span class="text-lg">&deg;C</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-md rounded-lg">
                    <div class="p-5 flex items-center">
                        <div class="flex-shrink-0 bg-primary-100 rounded-full p-3">
                            <svg class="h-6 w-6 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M12 3.75a9 9 0 0 1 8.3 5.9c.4 1.2.6 2.5.6 3.9 0 1.3-.2 2.6-.6 3.8a9 9 0 0 1-16.6 0c-.4-1.2-.6-2.5-.6-3.8 0-1.4.2-2.7.6-3.9A9 9 0 0 1 12 3.75z" />
                              <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 15a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0z" />
                            </svg>
                        </div>
                        <div class="ml-4 w-0 flex-1">
                            <p class="text-sm font-medium text-gray-500 truncate">
                                Kelembapan Udara
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ $analytics['humidity']['avg'] ?? '75' }} <span class="text-lg">%</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-md rounded-lg">
                    <div class="p-5 flex items-center">
                        <div class="flex-shrink-0 bg-primary-100 rounded-full p-3">
                            <svg class="h-6 w-6 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 8.638 5.214l.261-.26M15.101 5.474a.563.563 0 0 0-.81 0l-3.03 3.03a.563.563 0 0 0 0 .81l3.03 3.03a.563.563 0 0 0 .81 0l3.03-3.03a.563.563 0 0 0 0-.81l-3.03-3.03zM8.899 5.474a.563.563 0 0 1 .81 0l3.03 3.03a.563.563 0 0 1 0 .81l-3.03 3.03a.563.563 0 0 1-.81 0l-3.03-3.03a.563.563 0 0 1 0-.81l3.03-3.03z" />
                            </svg>
                        </div>
                        <div class="ml-4 w-0 flex-1">
                            <p class="text-sm font-medium text-gray-500 truncate">
                                Kelembapan Tanah
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ $analytics['soil_moisture']['avg'] ?? '45' }} <span class="text-lg">%</span>
                            </p>
                        </div>
                    </div>
                </div>

            </div> <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg">
                
                <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">
                        Perangkat Terdaftar
                    </h3>
                    <a href="{{ route('provisioning.index') }}">
                        <x-primary-button>
                            {{ __('+ Kelola Provisioning') }}
                        </x-primary-button>
                    </a>
                </div>
                
                <div class="p-6">
                    <div class="space-y-4">
                        @forelse ($devices as $device)
                            <a href="{{ route('device.show', $device) }}" 
                               class="block p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-150 ease-in-out">
                                <div class="flex justify-between items-center">
                                    <div>
                                        {{-- Judul perangkat kini menggunakan warna primer --}}
                                        <h4 class="font-semibold text-lg text-primary-600">{{ $device->name }}</h4>
                                        <p class="text-sm text-gray-600">ID: {{ $device->device_id }}</p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="font-medium text-sm {{ $device->is_online ? 'text-green-500' : 'text-red-500' }}">
                                            {{ $device->is_online ? 'Online' : 'Offline' }}
                                        </span>
                                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <p class="text-gray-500">Belum ada perangkat terdaftar.</p>
                        @endforelse
                    </div>
                </div>
            </div> </div>
    </div>
</x-app-layout>