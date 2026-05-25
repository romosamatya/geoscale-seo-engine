import React, { useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { fetchTasks, setActiveTask, bulkActionTask } from '../store/tasksSlice';

const CampaignsList = () => {
    const dispatch = useDispatch();
    const { items, loading } = useSelector(state => state.tasks);

    useEffect(() => {
        dispatch(fetchTasks());
    }, [dispatch]);

    const handleDelete = async (taskId) => {
        if (window.confirm('Are you sure you want to delete this campaign and all its routes?')) {
            await dispatch(bulkActionTask({ taskId, action: 'delete' }));
            dispatch(fetchTasks());
        }
    };

    return (
        <div style={{ background: '#fff', padding: '20px', border: '1px solid #ccd0d4', boxShadow: '0 1px 1px rgba(0,0,0,.04)' }}>
            <h2>Campaigns</h2>
            {loading ? <p>Loading...</p> : (
                <table className="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Campaign Name</th>
                            <th>Template ID</th>
                            <th>Routes</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {items.length === 0 ? (
                            <tr><td colSpan="7">No campaigns found. Or you need to deactivate/reactivate the plugin to update the database schema!</td></tr>
                        ) : items.map(task => (
                            <tr key={task.id}>
                                <td>{task.id}</td>
                                <td><strong><a href="#" onClick={(e) => { e.preventDefault(); dispatch(setActiveTask(task.id)); }}>{task.task_name}</a></strong></td>
                                <td>{task.template_post_id}</td>
                                <td>{task.row_count}</td>
                                <td>
                                    <span style={{ 
                                        padding: '3px 8px', 
                                        borderRadius: '3px',
                                        background: task.status === 'completed' ? '#edf8f1' : '#f0f0f1',
                                        color: task.status === 'completed' ? '#00a32a' : '#2271b1',
                                    }}>{task.status}</span>
                                </td>
                                <td>{new Date(task.created_at).toLocaleString()}</td>
                                <td>
                                    <button className="button button-small" onClick={() => dispatch(setActiveTask(task.id))}>View Routes</button>
                                    <button className="button button-small button-link-delete" style={{ color: '#d63638', marginLeft: '10px' }} onClick={() => handleDelete(task.id)}>Delete</button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            )}
        </div>
    );
};

export default CampaignsList;
