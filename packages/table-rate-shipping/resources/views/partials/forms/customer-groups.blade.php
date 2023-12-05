<div>
	<div>
		<span>Restrict to customer groups</span>
		<p class="text-sm text-gray-500">Restrict this shipping method to certain customer groups.</p>
  	</div>
	@foreach($this->availableCustomerGroups as $group)
		<div>
			{{ $group->name }}
		</div>
	@endforeach
</div>
