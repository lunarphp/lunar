<div class="flow-root">

  <ul role="list" class="-mb-8">
    @foreach($this->activity as $batch)
      {{-- {{ json_encode($batch) }}
       --}}
       @if($batch->first()->event == 'added_image')
        @include('adminhub::partials.activity-log.images')
       @else
        @foreach($batch as $row)
          {{-- {{ json_encode($row) }} --}}
          @if(!$row->causer)
            @include('adminhub::partials.activity-log.system')
          @else
            @include('adminhub::partials.activity-log.user-changes')
          @endif
        @endforeach
       @endif



    @endforeach
    {{-- <li>
      <div class="relative pb-8">
        <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
        <div class="relative flex items-start space-x-3">
          <div>
            <div class="relative px-1">
              <div class="flex items-center justify-center w-8 h-8 bg-gray-100 rounded-full ring-8 ring-white">
                <!-- Heroicon name: solid/user-circle -->
                <svg class="w-5 h-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd" />
                </svg>
              </div>
            </div>
          </div>
          <div class="min-w-0 flex-1 py-1.5">
            <div class="text-sm text-gray-500">
              <a href="#" class="font-medium text-gray-900">System</a>
              re indexed attributes for searching
              <span class="whitespace-nowrap">2d ago</span>
            </div>
          </div>
        </div>
      </div>
    </li>

    <li>
      <div class="relative pb-8">
        <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
        <div class="relative flex items-start space-x-3">
          <div>
            <div class="relative px-1">
              <x-hub::gravatar class="flex items-center justify-center w-10 h-10 bg-gray-400 rounded-full ring-8 ring-white" email="hello@itsalec.co.uk" />

            </div>
          </div>
          <div class="min-w-0 flex-1 py-1.5">
            <div class="text-sm text-gray-500">
              <a href="#" class="font-medium text-gray-900">Alec</a>
              Created initial product
              <span class="whitespace-nowrap">2d ago</span>
            </div>
          </div>
        </div>
      </div>
    </li> --}}

  </ul>
</div>
