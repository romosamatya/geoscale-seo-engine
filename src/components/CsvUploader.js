import React, { useState, useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { uploadCsv, clearMessages } from '../store/uploadSlice';

const { root, nonce } = window.geoscaleApiData || { root: '', nonce: '' };

const CsvUploader = () => {
    const dispatch = useDispatch();
    const { uploading, error, successMessage, activeJobs } = useSelector((state) => state.upload);
    const { isPremium, limits } = useSelector((state) => state.license);
    const { items } = useSelector(state => state.tasks);
    
    const [file, setFile] = useState(null);
    const [templateId, setTemplateId] = useState('');
    const [pages, setPages] = useState([]);
    const [loadingPages, setLoadingPages] = useState(true);

    useEffect(() => {
        // Fetch published pages from WP REST API
        fetch(`${root}wp/v2/pages?per_page=100`, { headers: { 'X-WP-Nonce': nonce } })
            .then(res => res.json())
            .then(data => {
                if (Array.isArray(data)) {
                    setPages(data);
                }
                setLoadingPages(false);
            })
            .catch(err => {
                console.error(err);
                setLoadingPages(false);
            });
    }, []);

    const handleUpload = (e) => {
        e.preventDefault();
        if (!file || !templateId) {
            alert('Please select a file and a template page.');
            return;
        }

        const submitData = () => {
            const formData = new FormData();
            formData.append('csv_file', file);
            formData.append('template_post_id', templateId);
            dispatch(uploadCsv(formData));
        };

        if (!isPremium) {
            const reader = new FileReader();
            reader.onload = (event) => {
                const lines = event.target.result.split('\n').filter(line => line.trim() !== '').length;
                if (lines > limits.maxRows) {
                    alert(`Free version limit: ${limits.maxRows} rows. Your CSV has ${lines} rows. Please upgrade to Pro.`);
                    return;
                }
                submitData();
            };
            reader.readAsText(file);
        } else {
            submitData();
        }
    };

    if (!isPremium && items.length >= limits.maxCampaigns) {
        return (
            <div style={{ background: '#fff', padding: '40px', border: '1px solid #ccd0d4', boxShadow: '0 1px 1px rgba(0,0,0,.04)', textAlign: 'center' }}>
                <h2 style={{ fontSize: '24px' }}>Campaign Limit Reached <span style={{ color: '#d63638' }}>(PRO Feature)</span></h2>
                <p style={{ fontSize: '16px' }}>The Free version is limited to {limits.maxCampaigns} campaign. Upgrade to WP GeoScale Pro for unlimited campaigns.</p>
                <button className="button button-primary button-large" disabled>Upgrade to Pro</button>
            </div>
        );
    }

    return (
        <div style={{ background: '#fff', padding: '20px', border: '1px solid #ccd0d4', boxShadow: '0 1px 1px rgba(0,0,0,.04)' }}>
            <h2>Upload Data CSV</h2>
            
            {error && <div style={{ color: '#d63638', padding: '10px', background: '#fcf0f1', borderLeft: '4px solid #d63638', marginBottom: '15px' }}>{error}</div>}
            {successMessage && <div style={{ color: '#00a32a', padding: '10px', background: '#edf8f1', borderLeft: '4px solid #00a32a', marginBottom: '15px' }}>{successMessage}</div>}

            <form onSubmit={handleUpload}>
                <div style={{ marginBottom: '15px' }}>
                    <label style={{ display: 'block', fontWeight: 'bold', marginBottom: '5px' }}>Master Template Page</label>
                    {loadingPages ? (
                        <p>Loading pages...</p>
                    ) : (
                        <select 
                            value={templateId} 
                            onChange={(e) => setTemplateId(e.target.value)} 
                            required 
                            style={{ padding: '5px', width: '100%', maxWidth: '400px' }}
                        >
                            <option value="">-- Select a Page --</option>
                            {pages.map(page => (
                                <option key={page.id} value={page.id}>
                                    {page.title.rendered} (ID: {page.id})
                                </option>
                            ))}
                        </select>
                    )}
                </div>
                <div style={{ marginBottom: '15px' }}>
                    <label style={{ display: 'block', fontWeight: 'bold', marginBottom: '5px' }}>CSV File</label>
                    <input 
                        type="file" 
                        accept=".csv" 
                        onChange={(e) => setFile(e.target.files[0])} 
                        required 
                    />
                </div>
                <button 
                    type="submit" 
                    className="button button-primary" 
                    disabled={uploading || activeJobs > 0}
                >
                    {uploading ? 'Uploading...' : 'Upload & Process'}
                </button>
            </form>
        </div>
    );
};

export default CsvUploader;
