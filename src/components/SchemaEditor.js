import React, { useState, useEffect } from 'react';

const { root, nonce } = window.geoscaleApiData || { root: '', nonce: '' };

const SchemaEditor = () => {
    const [schema, setSchema] = useState('');
    const [saving, setSaving] = useState(false);
    const [message, setMessage] = useState(null);

    useEffect(() => {
        fetch(`${root}geoscale/v1/schema`, { headers: { 'X-WP-Nonce': nonce } })
            .then(res => res.json())
            .then(data => setSchema(data.schema || ''))
            .catch(err => console.error(err));
    }, []);

    const handleSave = async () => {
        setSaving(true);
        setMessage(null);
        try {
            const formData = new FormData();
            formData.append('schema', schema);
            
            const res = await fetch(`${root}geoscale/v1/schema`, {
                method: 'POST',
                headers: { 'X-WP-Nonce': nonce },
                body: formData
            });
            const data = await res.json();
            if (res.ok) setMessage({ type: 'success', text: data.message });
            else setMessage({ type: 'error', text: data.message });
        } catch (err) {
            setMessage({ type: 'error', text: 'Failed to save schema.' });
        }
        setSaving(false);
    };

    return (
        <div style={{ background: '#fff', padding: '20px', border: '1px solid #ccd0d4', boxShadow: '0 1px 1px rgba(0,0,0,.04)' }}>
            <h2>Dynamic JSON-LD Schema</h2>
            <p>Paste your JSON-LD template below. You can inject dynamic values using the <code>[geoscale field="name"]</code> shortcodes.</p>
            
            {message && (
                <div style={{ 
                    color: message.type === 'success' ? '#00a32a' : '#d63638', 
                    padding: '10px', 
                    background: message.type === 'success' ? '#edf8f1' : '#fcf0f1',
                    borderLeft: `4px solid ${message.type === 'success' ? '#00a32a' : '#d63638'}`,
                    marginBottom: '15px' 
                }}>
                    {message.text}
                </div>
            )}

            <textarea 
                rows="10" 
                style={{ width: '100%', fontFamily: 'monospace', padding: '10px', marginBottom: '15px' }}
                value={schema}
                onChange={e => setSchema(e.target.value)}
                placeholder={'{\n  "@context": "https://schema.org",\n  "@type": "LocalBusiness",\n  "name": "[geoscale field=\\"business_name\\"]"\n}'}
            ></textarea>
            
            <button className="button button-primary" onClick={handleSave} disabled={saving}>
                {saving ? 'Saving...' : 'Save Schema Template'}
            </button>
        </div>
    );
};

export default SchemaEditor;
