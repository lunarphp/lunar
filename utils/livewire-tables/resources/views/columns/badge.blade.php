<div>
<span @class([
    'text-xs inline-block py-1 px-2 rounded',
    'text-green-600 bg-green-50' => !@empty($success),
    'text-yellow-600 bg-yellow-50' => !@empty($warning),
    'text-red-600 bg-red-50' => !@empty($danger),
])>
    {{ $value }}
</span>
</div>
