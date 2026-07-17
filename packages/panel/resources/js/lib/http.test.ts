import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { DraftConflictError, HttpError, ValidationError, http } from './http';

function jsonResponse(status: number, body: unknown): Response {
    return new Response(JSON.stringify(body), {
        status,
        headers: { 'Content-Type': 'application/json' },
    });
}

describe('http', () => {
    beforeEach(() => {
        document.cookie = 'XSRF-TOKEN=token%3Dvalue';
        vi.stubGlobal('fetch', vi.fn());
    });

    afterEach(() => {
        vi.unstubAllGlobals();
    });

    it('sends JSON requests with the XSRF token from the cookie', async () => {
        vi.mocked(fetch).mockResolvedValue(jsonResponse(200, { ok: true }));

        await http.patch('/draft', { data: { first_name: 'A' } });

        const [url, init] = vi.mocked(fetch).mock.calls[0];

        expect(url).toBe('/draft');
        expect(init?.method).toBe('PATCH');
        expect(init?.body).toBe(JSON.stringify({ data: { first_name: 'A' } }));
        expect(init?.headers).toMatchObject({
            Accept: 'application/json',
            'X-XSRF-TOKEN': 'token=value',
        });
    });

    it('returns the decoded payload on success', async () => {
        vi.mocked(fetch).mockResolvedValue(jsonResponse(200, { updated_at: 'now' }));

        await expect(http.post('/commit')).resolves.toEqual({ updated_at: 'now' });
    });

    it('returns null for 204 responses', async () => {
        vi.mocked(fetch).mockResolvedValue(new Response(null, { status: 204 }));

        await expect(http.delete('/draft')).resolves.toBeNull();
    });

    it('throws a typed ValidationError on 422', async () => {
        vi.mocked(fetch).mockResolvedValue(jsonResponse(422, { errors: { first_name: ['Required.'] } }));

        const error = await http.post('/commit').catch((caught: unknown) => caught);

        expect(error).toBeInstanceOf(ValidationError);
        expect((error as ValidationError).errors).toEqual({ first_name: ['Required.'] });
    });

    it('throws a typed DraftConflictError carrying the conflict set on 409', async () => {
        const conflicts = [{ key: 'first_name', label: 'First name', mine: 'A', base: 'B', theirs: 'C' }];
        vi.mocked(fetch).mockResolvedValue(jsonResponse(409, { conflicts }));

        const error = await http.post('/commit').catch((caught: unknown) => caught);

        expect(error).toBeInstanceOf(DraftConflictError);
        expect((error as DraftConflictError).conflicts).toEqual(conflicts);
    });

    it('throws HttpError for other failure statuses', async () => {
        vi.mocked(fetch).mockResolvedValue(jsonResponse(500, {}));

        const error = await http.get('/draft').catch((caught: unknown) => caught);

        expect(error).toBeInstanceOf(HttpError);
        expect((error as HttpError).status).toBe(500);
    });
});
