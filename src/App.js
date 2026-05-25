import React, { useEffect, useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { fetchStatus } from './store/uploadSlice';
import CsvUploader from './components/CsvUploader';
import ProgressMonitor from './components/ProgressMonitor';
import SchemaEditor from './components/SchemaEditor';
import CampaignsList from './components/CampaignsList';
import CampaignDetails from './components/CampaignDetails';

const App = () => {
    const dispatch = useDispatch();
    const { activeJobs } = useSelector((state) => state.upload);
    const { activeTaskId } = useSelector(state => state.tasks);
    const [currentTab, setCurrentTab] = useState('campaigns');

    useEffect(() => {
        dispatch(fetchStatus());
        
        let interval;
        if (activeJobs > 0) {
            interval = setInterval(() => {
                dispatch(fetchStatus());
            }, 3000);
        }
        return () => clearInterval(interval);
    }, [activeJobs, dispatch]);

    if (activeTaskId) {
        return (
            <div className="geoscale-app-container" style={{ padding: '20px', maxWidth: '1000px' }}>
                <h1>WP GeoScale 3.0 Dashboard</h1>
                <CampaignDetails />
            </div>
        );
    }

    return (
        <div className="geoscale-app-container" style={{ padding: '20px', maxWidth: '1000px' }}>
            <h1>WP GeoScale 3.0 Dashboard</h1>
            
            <h2 className="nav-tab-wrapper" style={{ marginBottom: '20px' }}>
                <a href="#" className={`nav-tab ${currentTab === 'campaigns' ? 'nav-tab-active' : ''}`} onClick={(e) => { e.preventDefault(); setCurrentTab('campaigns'); }}>Campaigns</a>
                <a href="#" className={`nav-tab ${currentTab === 'upload' ? 'nav-tab-active' : ''}`} onClick={(e) => { e.preventDefault(); setCurrentTab('upload'); }}>New Upload</a>
                <a href="#" className={`nav-tab ${currentTab === 'settings' ? 'nav-tab-active' : ''}`} onClick={(e) => { e.preventDefault(); setCurrentTab('settings'); }}>SEO Schema</a>
            </h2>
            
            <div style={{ display: 'grid', gap: '20px' }}>
                {activeJobs > 0 && <ProgressMonitor />}
                
                {currentTab === 'campaigns' && <CampaignsList />}
                {currentTab === 'upload' && <CsvUploader />}
                {currentTab === 'settings' && <SchemaEditor />}
            </div>
        </div>
    );
};

export default App;
