@props(['nodes', 'group'])
@php
    $encodedNodes = json_encode($nodes);
@endphp
<div
        class="space-y-2"
        x-data="{
            nodes: JSON.parse(@js($encodedNodes))
        }"
        x-ref="sortable"
        x-init="() => {

        el = $refs.sortable

          el.sortable = Sortable.create(el, {
              group: '{{ $group }}',
              draggable: '[x-sortable-item]',
              handle: '[x-sortable-handle]',
              dataIdAttr: 'x-sortable-item',
              animation: 300,
              ghostClass: 'fi-sortable-ghost',
              onEnd: (event) => {
                const rows = nodes

                // Get the current node
                const reorderedStored = rows[event.oldIndex]
                const siblingStored = rows[event.newIndex]

                const reorderedRow = rows.splice(event.oldIndex, 1)[0]

                let direction = event.oldIndex < event.newIndex ? 'after' : 'before'

                let newSibling = rows[event.newIndex]

                rows.splice(event.newIndex, 0, reorderedRow)

                $wire.call('sort', reorderedStored.id, siblingStored.id, direction)
              }
          })
        }"
>
    @foreach ($nodes as $node)
        <div wire:key="node_{{ $node['id'] }}"  x-sortable-item="{{ $node['id'] }}">
            <x-lunarpanel::collections.nested-set-item
                    :id="$node['id']"
                    :name="$node['name']"
                    :edit-url="$node['edit_url']"
                    :children-count="$node['children_count']"
                    :children="$node['children']"
                    :thumbnail="$node['thumbnail']"
                    :parent="$node['parent_id']"
            >
            </x-lunarpanel::collections.nested-set-item>
        </div>
    @endforeach
</div>