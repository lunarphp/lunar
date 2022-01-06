<div>
  <select id="location" name="location" class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
    @foreach($languages as $language)
      <option value="{{ $language->code }}">{{ $language->name }}</option>
    @endforeach
  </select>
</div>
