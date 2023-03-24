<textarea
  {{ $attributes->merge([
    'type' => 'text',
    'class' => 'form-input block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed',
    'maxlength' => 255,
  ])->class([
    'border-red-400' => !!$error,
  ]) }}
></textarea>
