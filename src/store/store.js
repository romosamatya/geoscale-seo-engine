import { configureStore } from '@reduxjs/toolkit';
import uploadReducer from './uploadSlice';
import tasksReducer from './tasksSlice';
import routesReducer from './routesSlice';
import licenseReducer from './licenseSlice';

export const store = configureStore({
    reducer: {
        upload: uploadReducer,
        tasks: tasksReducer,
        routes: routesReducer,
        license: licenseReducer,
    },
});
