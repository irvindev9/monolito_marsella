import { defineStore } from "pinia";

export const useAdminStore = defineStore('adminStore', {
    id: "adminStore",
    state: () => ({
        activeTab: 'users',
        isLoading: false,
    }),
    getters: {
        getActiveTab() {
            return this.activeTab;
        },
        getIsLoading() {
            return this.isLoading;
        }
    },
    actions: {
        setActiveTab(tab) {
            this.activeTab = tab;
        },
        setIsLoading(isLoading) {
            this.isLoading = isLoading;
        }
    },
});
