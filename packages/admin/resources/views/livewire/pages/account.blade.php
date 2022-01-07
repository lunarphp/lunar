<div>
  @livewire('hub.components.account', [
    'staff' => Auth::guard('staff')->user(),
  ])
</div>