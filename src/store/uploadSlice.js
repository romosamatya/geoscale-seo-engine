import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';

const { root, nonce } = window.geoscaleApiData || { root: '', nonce: '' };

export const uploadCsv = createAsyncThunk(
    'upload/uploadCsv',
    async (formData, { getState, rejectWithValue }) => {
        try {
            const { isPremium } = getState().license;
            const response = await fetch(`${root}geoscale/v1/upload`, {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': nonce,
                    'X-GeoScale-Tier': isPremium ? 'Pro' : 'Free'
                },
                body: formData
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Upload failed');
            return data;
        } catch (error) {
            return rejectWithValue(error.message);
        }
    }
);

export const fetchStatus = createAsyncThunk(
    'upload/fetchStatus',
    async (_, { rejectWithValue }) => {
        try {
            const response = await fetch(`${root}geoscale/v1/status`, {
                headers: {
                    'X-WP-Nonce': nonce
                }
            });
            const data = await response.json();
            if (!response.ok) throw new Error('Status fetch failed');
            return data;
        } catch (error) {
            return rejectWithValue(error.message);
        }
    }
);

const uploadSlice = createSlice({
    name: 'upload',
    initialState: {
        uploading: false,
        error: null,
        successMessage: null,
        pending: 0,
        inProgress: 0,
        activeJobs: 0,
    },
    reducers: {
        clearMessages: (state) => {
            state.error = null;
            state.successMessage = null;
        }
    },
    extraReducers: (builder) => {
        builder
            .addCase(uploadCsv.pending, (state) => {
                state.uploading = true;
                state.error = null;
                state.successMessage = null;
            })
            .addCase(uploadCsv.fulfilled, (state, action) => {
                state.uploading = false;
                state.successMessage = action.payload.message;
                state.activeJobs = 1;
            })
            .addCase(uploadCsv.rejected, (state, action) => {
                state.uploading = false;
                state.error = action.payload;
            })
            .addCase(fetchStatus.fulfilled, (state, action) => {
                state.pending = action.payload.pending;
                state.inProgress = action.payload.in_progress;
                state.activeJobs = action.payload.active_jobs;
                
                if (state.activeJobs === 0 && state.uploading === false && state.successMessage === 'Upload successful. Processing started in background.') {
                    state.successMessage = "Processing complete! All jobs are finished.";
                }
            });
    }
});

export const { clearMessages } = uploadSlice.actions;
export default uploadSlice.reducer;
