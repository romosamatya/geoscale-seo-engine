import { configureStore } from '@reduxjs/toolkit';
import uploadReducer from './uploadSlice';
import tasksReducer from './tasksSlice';
import routesReducer from './routesSlice';

export const store = configureStore({
    reducer: {
        upload: uploadReducer,
        tasks: tasksReducer,
        routes: routesReducer,
    },
});
