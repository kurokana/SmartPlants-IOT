<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
                <p class="mt-1 text-sm text-gray-500">Monitor dan kontrol smart plant Anda</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <!-- Temperature -->
                <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Suhu Udara</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">
                                {{ $analytics['temperature']['avg'] ?? '28' }}<span class="text-xl text-gray-500">°C</span>
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Humidity -->
                <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Kelembapan</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">
                                {{ $analytics['humidity']['avg'] ?? '75' }}<span class="text-xl text-gray-500">%</span>
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Soil -->
                <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Tanah</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">
                                {{ $analytics['soil_moisture']['avg'] ?? '45' }}<span class="text-xl text-gray-500">%</span>
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Devices Section -->
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Perangkat</h2>
                        <p class="text-sm text-gray-500 mt-1">{{ $devices->count() }} perangkat terdaftar</p>
                    </div>
                    <a href="{{ route('provisioning.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-xl hover:bg-gray-800 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Device
                    </a>
                </div>

                <div class="p-6">
                    @forelse ($devices as $device)
                        <a href="{{ route('device.show', $device) }}" 
                           class="group block p-5 mb-3 last:mb-0 bg-gray-50 hover:bg-gray-100 rounded-xl transition-all duration-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-sm">
                                        <svg class="w-6 h-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-900 group-hover:text-gray-700">
                                            {{ $device->name }}
                                        </h3>
                                        <p class="text-sm text-gray-500">{{ $device->id }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $device->is_online ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                                        <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $device->is_online ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                                        {{ $device->is_online ? 'Online' : 'Offline' }}
                                    </span>
                                    <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada perangkat</h3>
                            <p class="mt-1 text-sm text-gray-500">Mulai dengan menambahkan perangkat pertama Anda</p>
                            <div class="mt-6">
                                <a href="{{ route('provisioning.index') }}" 
                                   class="inline-flex items-center px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-xl hover:bg-gray-800 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Tambah Device
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>