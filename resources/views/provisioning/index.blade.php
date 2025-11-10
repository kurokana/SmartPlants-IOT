<x-app-layout>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      
      @if (session('status'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl flex items-start">
          <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
          </svg>
          <span class="text-sm">{{ session('status') }}</span>
        </div>
      @endif

      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Provisioning</h1>
        <p class="mt-1 text-sm text-gray-500">Kelola token provisioning untuk device baru</p>
      </div>

      <!-- Generate Form -->
      <div class="bg-white rounded-2xl shadow-sm p-6 mb-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Generate Token Baru</h2>
        <form method="POST" action="/provisioning/generate" class="grid grid-cols-1 md:grid-cols-4 gap-4">
          @csrf
          <input name="planned_device_id" 
                 class="px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-900 focus:border-transparent text-sm" 
                 placeholder="Device ID (opsional)">
          <input name="name_hint" 
                 class="px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-900 focus:border-transparent text-sm" 
                 placeholder="Nama device">
          <input name="location_hint" 
                 class="px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-900 focus:border-transparent text-sm" 
                 placeholder="Lokasi">
          <div class="flex gap-2">
            <input name="ttl_hours" 
                   type="number" 
                   class="w-24 px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-900 focus:border-transparent text-sm" 
                   value="12" 
                   placeholder="Jam">
            <button type="submit" class="flex-1 px-6 py-2.5 bg-gray-900 text-white text-sm font-medium rounded-xl hover:bg-gray-800 transition-colors">
              Generate
            </button>
          </div>
        </form>
      </div>

      <!-- Tokens Table -->
      <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100">
          <h2 class="text-xl font-semibold text-gray-900">Token Provisioning</h2>
          <p class="text-sm text-gray-500 mt-1">Daftar semua token yang telah dibuat</p>
        </div>
        
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Token</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Device ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expires</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Claimed Device</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              @forelse ($tokens as $t)
                <tr class="hover:bg-gray-50 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <code class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded font-mono">{{ $t->token }}</code>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $t->planned_device_id ?: '-' }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $t->expires_at->format('d M Y H:i') }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    @if($t->claimed)
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5"></span>
                        Claimed
                      </span>
                    @else
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400 mr-1.5"></span>
                        Pending
                      </span>
                    @endif
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $t->claimed_device_id ?: '-' }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                    <form method="POST" action="{{ route('provisioning.destroy', $t->id) }}" 
                          onsubmit="return confirm('{{ $t->claimed ? 'PERINGATAN: Token ini sudah digunakan oleh device ' . $t->claimed_device_id . '. Menghapus token ini akan menghapus device dan semua data sensor terkait. Yakin ingin melanjutkan?' : 'Hapus token ini?' }}');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="inline-flex items-center px-3 py-1.5 {{ $t->claimed ? 'bg-red-50 text-red-700 hover:bg-red-100 border-red-200' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 border-gray-200' }} border text-xs font-medium rounded-lg transition-colors">
                        @if($t->claimed)
                          <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                          </svg>
                        @endif
                        Hapus
                      </button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada token</h3>
                    <p class="mt-1 text-sm text-gray-500">Buat token provisioning pertama Anda</p>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</x-app-layout>
