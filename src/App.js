import React, { useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { fetchStatus } from './store/uploadSlice';
import CsvUploader from './components/CsvUploader';
import ProgressMonitor from './components/ProgressMonitor';
import SchemaEditor from './components/SchemaEditor';

const App = () => {
    const dispatch = useDispatch();
    const { activeJobs } = useSelector((state) => state.upload);

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

    return (
        <div className="geoscale-app-container" style={{ padding: '20px', maxWidth: '800px' }}>
            <h1>WP GeoScale 2.0 Dashboard</h1>
            
            <div style={{ display: 'grid', gap: '20px' }}>
                <CsvUploader />
                {activeJobs > 0 && <ProgressMonitor />}
                <SchemaEditor />
            </div>
        </div>
    );
};

export default App;
