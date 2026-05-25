import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';

const { root, nonce } = window.geoscaleApiData || { root: '', nonce: '' };

export const fetchTaskRoutes = createAsyncThunk(
    'routes/fetchTaskRoutes',
    async ({ taskId, page = 1, search = '' }, { getState, rejectWithValue }) => {
        try {
            const { isPremium } = getState().license;
            const url = new URL(`${root}geoscale/v1/tasks/${taskId}/routes`);
            url.searchParams.append('page', page);
            if (search) url.searchParams.append('search', search);

            const response = await fetch(url, { 
                headers: { 
                    'X-WP-Nonce': nonce,
                    'X-GeoScale-Tier': isPremium ? 'Pro' : 'Free'
                } 
            });
            const data = await response.json();
            if (!response.ok) throw new Error('Failed to fetch routes');
            return data;
        } catch (error) {
            return rejectWithValue(error.message);
        }
    }
);

const routesSlice = createSlice({
    name: 'routes',
    initialState: {
        items: [],
        total: 0,
        pages: 0,
        currentPage: 1,
        loading: false,
        error: null,
    },
    reducers: {
        setCurrentPage: (state, action) => {
            state.currentPage = action.payload;
        }
    },
    extraReducers: (builder) => {
        builder
            .addCase(fetchTaskRoutes.pending, (state) => {
                state.loading = true;
            })
            .addCase(fetchTaskRoutes.fulfilled, (state, action) => {
                state.loading = false;
                state.items = action.payload.routes;
                state.total = action.payload.total;
                state.pages = action.payload.pages;
            })
            .addCase(fetchTaskRoutes.rejected, (state, action) => {
                state.loading = false;
                state.error = action.payload;
            });
    }
});

export const { setCurrentPage } = routesSlice.actions;
export default routesSlice.reducer;
