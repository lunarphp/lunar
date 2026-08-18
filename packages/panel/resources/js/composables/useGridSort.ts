import { ref } from 'vue';

/**
 * Animated drag-to-reorder for a wrapping 2-D grid, using per-cell transforms
 * (the grid analogue of useDragSort). The item order never changes mid-drag:
 * every cell's rect is captured once, the target index is the cell whose centre
 * is nearest the pointer, and each displaced cell is translated to the cell it
 * will occupy in the previewed order. The reorder commits once on drop.
 *
 * Because translate cannot resize, a cell moving into or out of a differently
 * sized slot (e.g. a hero tile) slides to the slot's origin at its own size and
 * settles to the new size on drop - an accepted limitation for mixed-size grids.
 *
 * The container may hold trailing non-sortable children (e.g. an add button);
 * `sortableCount` bounds both target detection and the permutation to the real
 * cells.
 */
interface Cell {
    left: number;
    top: number;
    cx: number;
    cy: number;
}

interface GridDragState {
    listId: string;
    from: number;
    to: number;
    count: number;
    cells: Cell[];
    measured: boolean;
}

export interface GridSortOptions {
    onCommit: (listId: string, from: number, to: number) => void;
    duration?: number;
}

export function useGridSort(options: GridSortOptions) {
    const duration = options.duration ?? 150;
    const drag = ref<GridDragState | null>(null);

    const start = (event: DragEvent, listId: string, index: number): void => {
        if (event.dataTransfer) {
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', '');
        }

        drag.value = { listId, from: index, to: index, count: 0, cells: [], measured: false };
    };

    const measure = (container: HTMLElement, sortableCount: number): void => {
        const dragState = drag.value;

        if (!dragState) {
            return;
        }

        const children = Array.from(container.children);

        dragState.cells = children.map((child) => {
            const r = child.getBoundingClientRect();

            return { left: r.left, top: r.top, cx: r.left + r.width / 2, cy: r.top + r.height / 2 };
        });
        dragState.count = Math.min(sortableCount, dragState.cells.length);
        dragState.measured = true;
    };

    const over = (event: DragEvent, listId: string, sortableCount: number): void => {
        const dragState = drag.value;

        if (!dragState || dragState.listId !== listId) {
            return;
        }

        if (!dragState.measured) {
            measure(event.currentTarget as HTMLElement, sortableCount);
        }

        let to = dragState.from;
        let best = Infinity;

        for (let i = 0; i < dragState.count; i++) {
            const dx = dragState.cells[i].cx - event.clientX;
            const dy = dragState.cells[i].cy - event.clientY;
            const distance = dx * dx + dy * dy;

            if (distance < best) {
                best = distance;
                to = i;
            }
        }

        dragState.to = to;
    };

    const style = (listId: string, index: number): Record<string, string> => {
        const dragState = drag.value;

        if (!dragState || dragState.listId !== listId || !dragState.measured) {
            return {};
        }

        // The previewed order, as original indices; the cell originally at
        // `index` ends up wherever that index now sits.
        const order = Array.from({ length: dragState.count }, (_, i) => i);
        order.splice(dragState.to, 0, ...order.splice(dragState.from, 1));

        const target = order.indexOf(index);
        const from = dragState.cells[index];
        const to = dragState.cells[target];

        return {
            transform: `translate(${to.left - from.left}px, ${to.top - from.top}px)`,
            transition: `transform ${duration}ms ease`,
        };
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
        !!drag.value && drag.value.listId === listId && drag.value.from === index;

    return { start, over, style, end, isDragging };
}
