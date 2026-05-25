import React from 'react';
import { useSelector } from 'react-redux';

const ProgressMonitor = () => {
    const { pending, inProgress } = useSelector((state) => state.upload);

    return (
        <div style={{ background: '#fff', padding: '20px', border: '1px solid #ccd0d4', boxShadow: '0 1px 1px rgba(0,0,0,.04)' }}>
            <h2>Processing Jobs</h2>
            <p>Background workers are currently inserting CSV rows into the database.</p>
            
            <div style={{ display: 'flex', gap: '20px', marginTop: '15px' }}>
                <div style={{ flex: 1, padding: '15px', background: '#f0f0f1', textAlign: 'center' }}>
                    <h3 style={{ margin: 0, fontSize: '24px' }}>{inProgress}</h3>
                    <p style={{ margin: 0 }}>In Progress</p>
                </div>
                <div style={{ flex: 1, padding: '15px', background: '#f0f0f1', textAlign: 'center' }}>
                    <h3 style={{ margin: 0, fontSize: '24px' }}>{pending}</h3>
                    <p style={{ margin: 0 }}>Pending Chunks</p>
                </div>
            </div>
            
            <div style={{ marginTop: '20px', color: '#646970', fontStyle: 'italic' }}>
                * This page will update automatically. You can safely leave this tab.
            </div>
        </div>
    );
};

export default ProgressMonitor;
