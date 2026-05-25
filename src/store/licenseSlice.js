import { createSlice } from '@reduxjs/toolkit';

const initialState = {
    isPremium: true,
    limits: {
        maxRows: 100,
        maxCampaigns: 1
    }
};

const licenseSlice = createSlice({
    name: 'license',
    initialState,
    reducers: {
        togglePremium: (state) => {
            state.isPremium = !state.isPremium;
        }
    }
});

export const { togglePremium } = licenseSlice.actions;
export default licenseSlice.reducer;
