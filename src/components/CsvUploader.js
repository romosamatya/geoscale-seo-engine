import React, { useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { uploadCsv, clearMessages } from '../store/uploadSlice';

const CsvUploader = () => {
    const dispatch = useDispatch();
    const { uploading, error, successMessage, activeJobs } = useSelector((state) => state.upload);
    
    const [file, setFile] = useState(null);
    const [templateId, setTemplateId] = useState('');

    const handleUpload = (e) => {
        e.preventDefault();
        if (!file || !templateId) {
            alert('Please select a file and a template ID.');
            return;
        }

        dispatch(clearMessages());
        
        const formData = new FormData();
        formData.append('csv_file', file);
        formData.append('template_post_id', templateId);
        
        dispatch(uploadCsv(formData));
    };

    return (
        <div style={{ background: '#fff', padding: '20px', border: '1px solid #ccd0d4', boxShadow: '0 1px 1px rgba(0,0,0,.04)' }}>
            <h2>Upload Data CSV</h2>
            
            {error && <div style={{ color: '#d63638', padding: '10px', background: '#fcf0f1', borderLeft: '4px solid #d63638', marginBottom: '15px' }}>{error}</div>}
            {successMessage && <div style={{ color: '#00a32a', padding: '10px', background: '#edf8f1', borderLeft: '4px solid #00a32a', marginBottom: '15px' }}>{successMessage}</div>}

            <form onSubmit={handleUpload}>
                <div style={{ marginBottom: '15px' }}>
                    <label style={{ display: 'block', fontWeight: 'bold', marginBottom: '5px' }}>Master Template ID</label>
                    <input 
                        type="number" 
                        value={templateId} 
                        onChange={(e) => setTemplateId(e.target.value)} 
                        placeholder="e.g. 15"
                        required 
                        style={{ padding: '5px' }}
                    />
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
