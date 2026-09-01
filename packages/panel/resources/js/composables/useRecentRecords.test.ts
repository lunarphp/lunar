import { beforeEach, describe, expect, it } from 'vitest';
import { useRecentRecords } from './useRecentRecords';
import type { SearchResult } from '../types/search';

const record = (id: number, label = `Record ${id}`, kind = 'products'): SearchResult => ({
    kind,
    kind_label: 'Products',
    icon: 'box',
    id,
    label,
    hint: null,
    url: `/panel/products/${id}/edit`,
});

describe('useRecentRecords', () => {
    beforeEach(() => {
        window.localStorage.clear();
    });

    it('keeps the most recent record first', () => {
        const { records, remember } = useRecentRecords(1);

        remember(record(1));
        remember(record(2));

        expect(records.value.map((row) => row.id)).toEqual([2, 1]);
    });

    it('dedupes by kind and id, promoting the revisited record', () => {
        const { records, remember } = useRecentRecords(2);

        remember(record(1));
        remember(record(2));
        remember(record(1));

        expect(records.value.map((row) => row.id)).toEqual([1, 2]);
    });

    it('treats the same id in different sources as different records', () => {
        const { records, remember } = useRecentRecords(3);

        remember(record(1, 'A product'));
        remember(record(1, 'A brand', 'brands'));

        expect(records.value).toHaveLength(2);
    });

    it('caps the list', () => {
        const { records, remember } = useRecentRecords(4);

        for (let id = 1; id <= 12; id++) {
            remember(record(id));
        }

        expect(records.value).toHaveLength(8);
        expect(records.value[0].id).toBe(12);
    });

    it('forgets a record that no longer resolves', () => {
        const { records, remember, forget } = useRecentRecords(5);

        remember(record(1));
        remember(record(2));
        forget('products', 1);

        expect(records.value.map((row) => row.id)).toEqual([2]);
    });

    it('persists per staff member', () => {
        useRecentRecords(6).remember(record(1));

        expect(window.localStorage.getItem('lunar-panel-recent-records:6')).toContain('"id":1');
        expect(window.localStorage.getItem('lunar-panel-recent-records:7')).toBeNull();
    });

    it('reloads the stored list when a different staff member is active', () => {
        useRecentRecords(8).remember(record(1));

        const { records } = useRecentRecords(9);

        expect(records.value).toEqual([]);
    });

    it('ignores a corrupt stored payload', () => {
        window.localStorage.setItem('lunar-panel-recent-records:10', '{not json');

        expect(useRecentRecords(10).records.value).toEqual([]);
    });
});
