import { createApp, type Component } from 'vue';

/** Mount a component into a fresh container and return that container. */
export function mount(component: Component): HTMLElement {
    const container = document.createElement('div');
    document.body.appendChild(container);
    createApp(component).mount(container);

    return container;
}
