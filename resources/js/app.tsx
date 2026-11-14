import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import ShareHouseApp from './ShareHouseApp';

const rootElement = document.getElementById('app');

if (rootElement === null) {
    throw new Error('Reactの描画先となる #app が見つかりません。');
}

createRoot(rootElement).render(
    <StrictMode>
        <ShareHouseApp />
    </StrictMode>,
);
