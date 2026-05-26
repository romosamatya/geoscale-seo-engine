import { createSlice } from '@reduxjs/toolkit';

const { is_premium } = window.geoscaleApiData || { is_premium: false };

const initialState = {
    isPremium: is_premium,
    limits: {
        maxRows: 100,
        maxCampaigns: 1
    }
};

const licenseSlice = createSlice({
    name: 'license',
    initialState,
    reducers: {}
});

export default licenseSlice.reducer;
