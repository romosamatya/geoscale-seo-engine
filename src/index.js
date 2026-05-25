import React from 'react';
import { createRoot } from 'react-dom/client';
import { Provider } from 'react-redux';
import { store } from './store/store';
import App from './App';

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('geoscale-admin-app');
    if (container) {
        const root = createRoot(container);
        root.render(
            <Provider store={store}>
                <App />
            </Provider>
        );
    }
});
