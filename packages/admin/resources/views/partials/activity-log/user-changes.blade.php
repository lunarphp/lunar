<li>
      <div class="relative pb-8">
        <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
        <div class="relative flex items-start space-x-3">
          <div class="relative">
            <x-hub::gravatar class="flex items-center justify-center w-10 h-10 bg-gray-400 rounded-full ring-8 ring-white" :email="$row->causer->email" />

            <span class="absolute -bottom-0.5 -right-1 bg-white rounded-tl px-0.5 py-px">
              <!-- Heroicon name: solid/chat-alt -->
              <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zM7 8H5v2h2V8zm2 0h2v2H9V8zm6 0h-2v2h2V8z" clip-rule="evenodd" />
              </svg>
            </span>
          </div>
          <div class="flex-1 min-w-0">
            <div>
              <div class="text-sm">
                <a href="#" class="font-medium text-gray-900">{{ $row->causer->fullName }}</a>
              </div>
              <p class="mt-0.5 text-sm text-gray-500">
                {{ ucfirst($row->description) }} {{ $row->created_at->diffForHumans() }}
              </p>
            </div>
            <div class="mt-2 text-sm text-gray-700">
              {{-- @forelse(collect($row->changes['old'] ?? [])->filter(fn($value, $field) => $row->changes['old'][$field] != $row->changes['attributes'][$field]) as $field => $value)
                <p>
                  Changed <strong>{{ $field }}</strong> from <strong>{{ $value }}</strong> to <strong>{{ $row->changes['attributes'][$field] }}</strong>
                </p>
              @empty
                <span class="text-xs text-gray-500">No changes logged.</span>
              @endforelse --}}
            </div>
          </div>
        </div>
      </div>
    </li>
