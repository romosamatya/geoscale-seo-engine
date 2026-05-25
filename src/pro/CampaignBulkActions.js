import React from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { bulkActionTask } from '../store/tasksSlice';
import { fetchTaskRoutes } from '../store/routesSlice';
import { setActiveTask, fetchTasks } from '../store/tasksSlice';

const CampaignBulkActions = ({ activeTaskId, currentPage, search }) => {
    const dispatch = useDispatch();
    const { isPremium } = useSelector(state => state.license);

    const handleBulkAction = async (action) => {
        if (!isPremium) {
            alert('Bulk Actions are a PRO feature. Upgrade today!');
            return;
        }

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
        <div>
            <button className="button" style={{ marginRight: '10px' }} onClick={() => handleBulkAction('activate')}>Activate All</button>
            <button className="button" style={{ marginRight: '10px' }} onClick={() => handleBulkAction('deactivate')}>Deactivate All</button>
            <button className="button" style={{ color: '#d63638', borderColor: '#d63638' }} onClick={() => handleBulkAction('delete')}>Delete Campaign</button>
            {!isPremium && <span style={{ marginLeft: '10px', color: '#d63638', fontWeight: 'bold' }}>PRO</span>}
        </div>
    );
};

export default CampaignBulkActions;
