<label class="inline-flex items-center px-3 py-3 text-sm leading-4 text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm cursor-pointer hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500">
  <input {{ $attributes }} aria-describedby="candidates-description" name="candidates" type="checkbox" class="w-4 h-4 text-sky-600 border-gray-300 rounded focus:ring-sky-500">
  <span class="block ml-2">{{ $slot }}</span>
</label>
