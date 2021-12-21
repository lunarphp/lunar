<div class="overflow-hidden border-b border-gray-200 shadow sm:rounded-lg">
  {{ $toolbar ?? null }}
  <table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
      <tr>
        {{ $head }}
      </tr>
    </thead>

    <tbody {{ $attributes->wire('sortable') }} class="relative">
      {{ $body }}
    </tbody>
  </table>
</div>
