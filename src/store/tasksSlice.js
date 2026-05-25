import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';

const { root, nonce } = window.geoscaleApiData || { root: '', nonce: '' };

export const fetchTasks = createAsyncThunk(
    'tasks/fetchTasks',
    async (_, { rejectWithValue }) => {
        try {
            const response = await fetch(`${root}geoscale/v1/tasks`, {
                headers: { 'X-WP-Nonce': nonce }
            });
            const data = await response.json();
            if (!response.ok) throw new Error('Failed to fetch tasks');
            return data;
        } catch (error) {
            return rejectWithValue(error.message);
        }
    }
);

export const bulkActionTask = createAsyncThunk(
    'tasks/bulkActionTask',
    async ({ taskId, action }, { rejectWithValue }) => {
        try {
            const response = await fetch(`${root}geoscale/v1/tasks/bulk-action`, {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': nonce,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ task_id: taskId, action })
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message);
            return data;
        } catch (error) {
            return rejectWithValue(error.message);
        }
    }
);

const tasksSlice = createSlice({
    name: 'tasks',
    initialState: {
        items: [],
        loading: false,
        error: null,
        activeTaskId: null,
    },
    reducers: {
        setActiveTask: (state, action) => {
            state.activeTaskId = action.payload;
        }
    },
    extraReducers: (builder) => {
        builder
            .addCase(fetchTasks.pending, (state) => {
                state.loading = true;
            })
            .addCase(fetchTasks.fulfilled, (state, action) => {
                state.loading = false;
                state.items = action.payload;
            })
            .addCase(fetchTasks.rejected, (state, action) => {
                state.loading = false;
                state.error = action.payload;
            });
    }
});

export const { setActiveTask } = tasksSlice.actions;
export default tasksSlice.reducer;
