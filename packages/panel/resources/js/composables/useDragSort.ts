import { ref } from 'vue';

/**
 * Animated drag-to-reorder for a vertical list, using slot transforms (the
 * dnd-kit technique). The item order never changes mid-drag: row geometry is
 * captured once, the target slot is derived purely from the pointer against
 * that geometry, and displaced rows are shifted with animated transforms. The
 * reorder commits once on drop. Because the slot computation never reads live
 * positions, the animation cannot feed back into it - the oscillation a
 * hover-swap plus FLIP approach suffers from is impossible by construction.
 *
 * Multiple lists on one page share a single instance, distinguished by an
 * arbitrary `listId` string (an association type, an attribute key, or a
 * constant when a page has just one sortable list).
 *
 * Wire it up per list:
 * - the sortable rows must be the only direct children of one container
 * - `@dragstart="start($event, listId, index)"` on each row (or its handle)
 * - `@dragover.prevent="over($event, listId)"` and `@drop.prevent` on the container
 * - `@dragend="end()"` on each row
 * - `:style="style(listId, index)"` on each row
 * - `:class="isDragging(listId, index) ? '...' : ''"` for the lifted-row look
 */
interface DragState {
    listId: string;
    index: number;
    from: number;
    to: number;
    offsets: number[];
    heights: number[];
    measured: boolean;
}

export interface DragSortOptions {
    /** Persist the reorder. `from`/`to` are indices into the list's items. */
    onCommit: (listId: string, from: number, to: number) => void;
    /** Transform transition duration in ms. */
    duration?: number;
}

export function useDragSort(options: DragSortOptions) {
    const duration = options.duration ?? 150;
    const drag = ref<DragState | null>(null);

    const start = (event: DragEvent, listId: string, index: number): void => {
        if (event.dataTransfer) {
            // effectAllowed + a payload are required for Firefox to start the drag.
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', '');
        }

        drag.value = { listId, index, from: index, to: index, offsets: [], heights: [], measured: false };
    };

    // Measured lazily on the first dragover, where currentTarget is the list
    // container - so it works whether the row or just its handle is draggable,
    // and reads natural positions before any transform is applied.
    const measure = (container: HTMLElement): void => {
        const dragState = drag.value;

        if (!dragState) {
            return;
        }

        const top = container.getBoundingClientRect().top;
        const rects = Array.from(container.children).map((child) => child.getBoundingClientRect());

        dragState.offsets = rects.map((rect) => rect.top - top);
        dragState.heights = rects.map((rect) => rect.height);
        dragState.measured = true;
    };

    // `sortableCount` bounds the drop target when the container holds trailing
    // non-sortable children (e.g. a plain list's always-present add-row), so
    // the dragged row can never land on or past them.
    const over = (event: DragEvent, listId: string, sortableCount?: number): void => {
        const dragState = drag.value;

        if (!dragState || dragState.listId !== listId) {
            return;
        }

        const container = event.currentTarget as HTMLElement;

        if (!dragState.measured) {
            measure(container);
        }

        const y = event.clientY - container.getBoundingClientRect().top;
        const limit = Math.min(sortableCount ?? dragState.offsets.length, dragState.offsets.length);

        let to = limit - 1;

        for (let i = 0; i < limit; i++) {
            if (y < dragState.offsets[i] + dragState.heights[i]) {
                to = i;
                break;
            }
        }

        dragState.to = to;
    };

    const style = (listId: string, index: number): Record<string, string> => {
        const dragState = drag.value;

        if (!dragState || dragState.listId !== listId || !dragState.measured) {
            return {};
        }

        const { from, to, heights } = dragState;

        let shift = 0;

        if (index === from) {
            // The dragged row slides to its target slot.
            shift = to > from
                ? heights.slice(from + 1, to + 1).reduce((sum, height) => sum + height, 0)
                : -heights.slice(to, from).reduce((sum, height) => sum + height, 0);
        } else if (index > from && index <= to) {
            shift = -heights[from];
        } else if (index >= to && index < from) {
            shift = heights[from];
        }

        return { transform: `translateY(${shift}px)`, transition: `transform ${duration}ms ease` };
    };

    const end = (): void => {
        const dragState = drag.value;

        drag.value = null;

        if (!dragState || !dragState.measured || dragState.to === dragState.from) {
            return;
        }

        options.onCommit(dragState.listId, dragState.from, dragState.to);
    };

    const isDragging = (listId: string, index: number): boolean =>
        !!drag.value && drag.value.listId === listId && drag.value.index === index;

    return { start, over, style, end, isDragging };
}
