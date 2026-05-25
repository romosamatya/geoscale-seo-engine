import React, { useEffect, useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { fetchTaskRoutes, setCurrentPage } from '../store/routesSlice';
import { setActiveTask, bulkActionTask, fetchTasks } from '../store/tasksSlice';

const CampaignDetails = () => {
    const dispatch = useDispatch();
    const { activeTaskId } = useSelector(state => state.tasks);
    const { items, total, pages, currentPage, loading } = useSelector(state => state.routes);
    
    const [search, setSearch] = useState('');

    useEffect(() => {
        if (activeTaskId) {
            dispatch(fetchTaskRoutes({ taskId: activeTaskId, page: currentPage, search }));
        }
    }, [dispatch, activeTaskId, currentPage, search]);

    const handleBulkAction = async (action) => {
        if (window.confirm(`Are you sure you want to ${action} all routes in this campaign?`)) {
            await dispatch(bulkActionTask({ taskId: activeTaskId, action }));
            dispatch(fetchTaskRoutes({ taskId: activeTaskId, page: currentPage, search }));
            if (action === 'delete') {
                dispatch(setActiveTask(null));
                dispatch(fetchTasks());
            }
        }
    };

    return (
        <div style={{ background: '#fff', padding: '20px', border: '1px solid #ccd0d4', boxShadow: '0 1px 1px rgba(0,0,0,.04)' }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px' }}>
                <h2 style={{ margin: 0 }}>Campaign Routes</h2>
                <button className="button" onClick={() => dispatch(setActiveTask(null))}>&larr; Back to Campaigns</button>
            </div>

            <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '15px' }}>
                <div>
                    <button className="button" style={{ marginRight: '10px' }} onClick={() => handleBulkAction('activate')}>Activate All</button>
                    <button className="button" style={{ marginRight: '10px' }} onClick={() => handleBulkAction('deactivate')}>Deactivate All</button>
                    <button className="button" style={{ color: '#d63638', borderColor: '#d63638' }} onClick={() => handleBulkAction('delete')}>Delete Campaign</button>
                </div>
                <div>
                    <input 
                        type="search" 
                        placeholder="Search slugs..." 
                        value={search} 
                        onChange={(e) => { setSearch(e.target.value); dispatch(setCurrentPage(1)); }} 
                    />
                </div>
            </div>

            {loading ? <p>Loading routes...</p> : (
                <>
                    <table className="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Route Slug</th>
                                <th>Status</th>
                                <th>Dynamic Data Preview</th>
                            </tr>
                        </thead>
                        <tbody>
                            {items.length === 0 ? (
                                <tr><td colSpan="4">No routes found.</td></tr>
                            ) : items.map(route => (
                                <tr key={route.id}>
                                    <td>{route.id}</td>
                                    <td><strong>/{route.route_slug}/</strong></td>
                                    <td>{route.is_active === "1" ? <span style={{ color: '#00a32a' }}>Active</span> : <span style={{ color: '#d63638' }}>Inactive</span>}</td>
                                    <td><code style={{ fontSize: '11px', background: 'transparent' }}>{JSON.stringify(route.dynamic_data).substring(0, 50)}...</code></td>
                                </tr>
                            ))}
                        </tbody>
                    </table>

                    <div className="tablenav bottom" style={{ display: 'flex', justifyContent: 'space-between', marginTop: '10px' }}>
                        <div className="displaying-num">{total} items</div>
                        <div className="pagination-links">
                            <button className="button" disabled={currentPage <= 1} onClick={() => dispatch(setCurrentPage(currentPage - 1))}>&laquo;</button>
                            <span style={{ margin: '0 10px' }}>{currentPage} of {pages}</span>
                            <button className="button" disabled={currentPage >= pages} onClick={() => dispatch(setCurrentPage(currentPage + 1))}>&raquo;</button>
                        </div>
                    </div>
                </>
            )}
        </div>
    );
};

export default CampaignDetails;
