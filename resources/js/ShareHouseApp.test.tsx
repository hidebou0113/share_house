import { render, screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import ShareHouseApp from './ShareHouseApp';

function stubFetch() {
    const fetchMock = vi.fn<typeof fetch>();

    vi.stubGlobal('fetch', fetchMock);

    return fetchMock;
}

describe('ShareHouseApp', () => {
    it('APIの応答を待っている間は読み込み中と表示する', () => {
        const fetchMock = stubFetch();

        fetchMock.mockReturnValue(new Promise<Response>(() => undefined));

        render(<ShareHouseApp />);

        expect(screen.getByRole('status')).toHaveTextContent('APIの状態を確認しています…');
    });

    it('Health APIの成功時にstatus: okと表示する', async () => {
        const fetchMock = stubFetch();

        fetchMock.mockResolvedValue({
            ok: true,
            json: async () => ({ status: 'ok' }),
        } as Response);

        render(<ShareHouseApp />);

        await screen.findByText('ok');

        expect(screen.getByRole('status')).toHaveTextContent('status: ok');
        expect(fetchMock).toHaveBeenCalledTimes(1);
        expect(fetchMock).toHaveBeenCalledWith(
            '/api/health',
            expect.objectContaining({
                headers: {
                    Accept: 'application/json',
                },
            }),
        );
    });

    it('Health APIとの通信失敗時に利用者向けエラーを表示する', async () => {
        const fetchMock = stubFetch();

        fetchMock.mockRejectedValue(new TypeError('Network request failed.'));

        render(<ShareHouseApp />);

        expect(await screen.findByRole('alert')).toHaveTextContent(
            'APIの状態を取得できませんでした。時間をおいてページを再読み込みしてください。',
        );
    });
});
