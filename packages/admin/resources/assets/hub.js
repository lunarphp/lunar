import Sortable from 'sortablejs';

require('flatpickr')

if (typeof window.livewire === 'undefined') {
    throw 'Livewire Sortable Plugin: window.livewire is undefined. Make sure @livewireScripts is placed above this script include';
}

window.livewire.directive('sort', (el, directive, component) => {
    let options = {};

    if (el.hasAttribute('sort.options')) {
        var otherOptions = el.getAttribute('sort.options');
        var optionJson = otherOptions.replace(/(['"])?([a-z0-9A-Z_]+)(['"])?:/g, '"$2": ');

        try {
            options = JSON.parse(optionJson);
        } catch (e) {
            options = {};
        }
    }

    if (directive.modifiers.length > 0) return;

    const sortable = new Sortable(el, {
        group: options.group,
        draggable: '[sort\\.item]',
        handle: '[sort\\.handle]',
        ghostClass: 'sortable-ghost',
        fallbackOnBody: true,
        animation: 150,
        invertSwap: true,
        onSort: () => {
            let items = [];
            // Get the current one

            el.querySelectorAll('[sort\\.item="' + options.group + '"]').forEach((el, index) => {
                items.push({
                    order: index + 1,
                    id: el.getAttribute('sort.id'),
                    parent: el.getAttribute('sort.parent'),
                });
            });

            component.call(options.method, {
                owner: options.owner,
                group: options.group,
                items,
            });
        },
    });
});
