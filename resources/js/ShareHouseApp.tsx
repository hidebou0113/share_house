import { useEffect, useState } from 'react';

type HealthResponse = {
    status: 'ok';
};

type HealthRequestState =
    | { phase: 'loading' }
    | { phase: 'success'; data: HealthResponse }
    | { phase: 'error' };

function isHealthResponse(value: unknown): value is HealthResponse {
    return typeof value === 'object' && value !== null && 'status' in value && value.status === 'ok';
}

export default function ShareHouseApp() {
    const [healthState, setHealthState] = useState<HealthRequestState>({ phase: 'loading' });

    useEffect(() => {
        const controller = new AbortController();

        async function fetchHealthStatus() {
            try {
                const response = await fetch('/api/health', {
                    headers: {
                        Accept: 'application/json',
                    },
                    signal: controller.signal,
                });

                if (!response.ok) {
                    throw new Error('Health API returned an unsuccessful response.');
                }

                const data: unknown = await response.json();

                if (!isHealthResponse(data)) {
                    throw new Error('Health API returned an unexpected response.');
                }

                setHealthState({ phase: 'success', data });
            } catch {
                if (controller.signal.aborted) {
                    return;
                }

                setHealthState({ phase: 'error' });
            }
        }

        void fetchHealthStatus();

        return () => {
            controller.abort();
        };
    }, []);

    return (
        <main className="app-shell">
            <section className="app-card" aria-labelledby="app-title">
                <p className="app-label">Share House</p>
                <h1 className="app-title" id="app-title">
                    シェアハウス管理
                </h1>
                <p className="app-description">
                    Laravelから配信したページ上で、Reactが正常に動作しています。
                </p>

                <section className="health-panel" aria-labelledby="health-title">
                    <h2 className="health-title" id="health-title">
                        Laravel API
                    </h2>

                    {healthState.phase === 'loading' && (
                        <p className="health-message health-message-loading" role="status">
                            APIの状態を確認しています…
                        </p>
                    )}

                    {healthState.phase === 'success' && (
                        <p className="health-message health-message-success" role="status">
                            status: <strong>{healthState.data.status}</strong>
                        </p>
                    )}

                    {healthState.phase === 'error' && (
                        <p className="health-message health-message-error" role="alert">
                            APIの状態を取得できませんでした。時間をおいてページを再読み込みしてください。
                        </p>
                    )}
                </section>
            </section>
        </main>
    );
}
