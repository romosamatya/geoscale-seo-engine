import React from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { togglePremium } from '../store/licenseSlice';

const DevToolbar = () => {
    const dispatch = useDispatch();
    const { isPremium } = useSelector(state => state.license);

    return (
        <div style={{ background: isPremium ? '#d4edda' : '#f8d7da', padding: '10px 20px', borderRadius: '4px', border: '1px solid #ccd0d4', marginBottom: '20px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
            <div>
                <strong>Dev Toolbar:</strong> You are viewing the <strong>{isPremium ? '⚡ Premium Mode' : '🎯 Free Version'}</strong> UI.
            </div>
            <button className="button" onClick={() => dispatch(togglePremium())}>
                Switch to {isPremium ? 'Free' : 'Premium'}
            </button>
        </div>
    );
};

export default DevToolbar;
