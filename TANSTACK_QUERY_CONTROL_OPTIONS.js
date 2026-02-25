// ================================================================
// ALTERNATIVE APPROACHES FOR CONTROLLING TANSTACK QUERY FETCHING
// ================================================================

// ============================================================
// APPROACH 1: Manual Fetching (No Auto-Fetch)
// ============================================================
export default {
    setup() {
        const crudVariables = crudQuery();
        const shouldFetchStats = ref(false); // Disabled by default
        
        const { 
            stats: doctorStats, 
            isLoading: statsLoading, 
            refetch: refetchStats 
        } = useStatsQuery("/doctors/stats", {
            initialData: { totalDoctors: 0 },
            enabled: shouldFetchStats, // Won't fetch until enabled
            staleTime: Infinity, // Never become stale
            gcTime: Infinity, // Cache forever
        });

        // Manually trigger fetch when needed
        const loadStats = () => {
            refetchStats(); // Fetches once
        };

        onMounted(() => {
            setUrlData();
            loadStats(); // Fetch manually when YOU decide
        });

        return { doctorStats, statsLoading, loadStats };
    },
};

// ============================================================
// APPROACH 2: Lazy Query (Fetch on User Action)
// ============================================================
export default {
    setup() {
        const statsEnabled = ref(false);
        
        const { 
            stats: doctorStats, 
            refetch: refetchStats 
        } = useStatsQuery("/doctors/stats", {
            initialData: { totalDoctors: 0 },
            enabled: statsEnabled,
        });

        // Load stats when user clicks a button or performs action
        const handleShowStats = () => {
            statsEnabled.value = true; // Triggers fetch
        };

        return { doctorStats, handleShowStats };
    },
};

// Template:
// <a-button @click="handleShowStats">Load Stats</a-button>

// ============================================================
// APPROACH 3: Use Cached Data Only (No Network)
// ============================================================
import { useQueryClient } from "@tanstack/vue-query";

export default {
    setup() {
        const queryClient = useQueryClient();
        const doctorStats = ref({ totalDoctors: 0 });

        // Get data from cache without fetching
        const getCachedStats = () => {
            const cached = queryClient.getQueryData(['stats', '/doctors/stats']);
            if (cached) {
                doctorStats.value = cached;
            }
        };

        onMounted(() => {
            getCachedStats(); // Uses cache only, no network request
        });

        return { doctorStats };
    },
};

// ============================================================
// APPROACH 4: Conditional Fetching
// ============================================================
export default {
    setup() {
        const user = computed(() => store.state.auth.user);
        
        // Only fetch if user is admin
        const { stats: doctorStats } = useStatsQuery("/doctors/stats", {
            initialData: { totalDoctors: 0 },
            enabled: computed(() => user.value?.role === 'admin'),
        });

        return { doctorStats };
    },
};

// ============================================================
// APPROACH 5: No TanStack Query (Old Manual Way)
// ============================================================
export default {
    setup() {
        const crudVariables = crudQuery(); // Still use for table
        const doctorStats = ref({ totalDoctors: 0 });
        const loading = ref(false);

        // Manual fetch without TanStack Query
        const fetchDoctorStats = async () => {
            try {
                loading.value = true;
                const response = await axiosAdmin.get("/doctors/stats");
                doctorStats.value = response.data;
            } catch (error) {
                console.error("Error:", error);
            } finally {
                loading.value = false;
            }
        };

        onMounted(() => {
            setUrlData();
            // Don't call fetchDoctorStats() here to prevent auto-fetch
        });

        // Call manually when needed
        const loadStats = () => {
            fetchDoctorStats();
        };

        return { doctorStats, loading, loadStats };
    },
};

// ============================================================
// APPROACH 6: Hybrid - Cache First, Then Update
// ============================================================
export default {
    setup() {
        const { 
            stats: doctorStats, 
            isLoading,
            isFetching 
        } = useStatsQuery("/doctors/stats", {
            initialData: { totalDoctors: 0 },
            staleTime: Infinity, // Never refetch automatically
            refetchOnMount: false, // Don't refetch when component mounts
            refetchOnWindowFocus: false, // Don't refetch on window focus
            refetchOnReconnect: false, // Don't refetch on reconnect
        });

        // Still shows cached data but won't automatically refetch
        // Call refetch() manually when you want fresh data

        return { doctorStats, isLoading, isFetching };
    },
};

// ============================================================
// APPROACH 7: Prefetch Without Rendering
// ============================================================
import { useQueryClient } from "@tanstack/vue-query";

export default {
    setup() {
        const queryClient = useQueryClient();

        // Prefetch in background without using the data yet
        const prefetchStats = () => {
            queryClient.prefetchQuery({
                queryKey: ['stats', '/doctors/stats'],
                queryFn: () => axiosAdmin.get("/doctors/stats").then(r => r.data),
                staleTime: 5 * 60 * 1000,
            });
        };

        onMounted(() => {
            prefetchStats(); // Fetches and caches without showing
        });

        // Later, when you want to show the data:
        const { stats: doctorStats } = useStatsQuery("/doctors/stats", {
            staleTime: 5 * 60 * 1000,
        });
        // This will use the prefetched cache instantly!

        return {};
    },
};

// ============================================================
// RECOMMENDED APPROACH FOR YOUR USE CASE
// ============================================================
export default {
    setup() {
        const crudVariables = crudQuery();
        
        // Disable auto-fetch by default
        const { 
            stats: doctorStats, 
            isLoading: statsLoading, 
            refetch: refetchStats 
        } = useStatsQuery("/doctors/stats", {
            initialData: {
                totalDoctors: 0,
                topSpecialist: '-',
                avgAppointmentCharge: 0,
                availableToday: 0
            },
            enabled: false, // ⭐ KEY: This prevents auto-fetch
            staleTime: 5 * 60 * 1000, // Cache for 5 minutes when fetched
        });

        // Setup table
        const setUrlData = () => {
            crudVariables.table.filterableColumns = filterableColumns;
            crudVariables.crudUrl.value = addEditUrl;
            crudVariables.langKey.value = "doctors";
            crudVariables.initData.value = { ...initData };
            crudVariables.formData.value = { ...initData };
            crudVariables.tableUrl.value = { url, filters, extraFilters };
            crudVariables.fetch({ page: 1 });
        };

        // Manually trigger stats after table loads
        const loadStatsAfterTable = () => {
            refetchStats(); // Fetches only when you call this
        };

        // Override success to refresh stats
        const originalAddEditSuccess = crudVariables.addEditSuccess;
        crudVariables.addEditSuccess = (id) => {
            originalAddEditSuccess(id);
            refetchStats(); // Refresh stats after add/edit
        };

        onMounted(() => {
            getPrefetchData();
            setUrlData(); // Load table first
            
            // Option A: Load stats immediately
            loadStatsAfterTable();
            
            // Option B: Load stats after delay
            // setTimeout(() => loadStatsAfterTable(), 1000);
            
            // Option C: Don't load stats at all (show initialData only)
            // (Don't call loadStatsAfterTable)
        });

        return {
            ...crudVariables,
            doctorStats,
            loading: statsLoading,
            loadStatsAfterTable, // Expose for manual triggering
        };
    },
};

// ================================================================
// KEY POINTS
// ================================================================

/*
1. ✅ TanStack Query SHOULD fetch from endpoints
   - That's not a bug, it's the feature!
   - It caches responses to avoid duplicate requests

2. 🔧 To prevent auto-fetch:
   - Set `enabled: false`
   - Or use `refetchOnMount: false`
   - Or set `enabled: ref(false)` and toggle later

3. 📦 Benefits of TanStack Query even with manual control:
   - Smart caching (no duplicate requests)
   - Automatic retries on failure
   - Background refetching
   - Easy invalidation
   - DevTools for debugging

4. 🎯 Best Practice:
   - Use TanStack Query for data that changes
   - Set appropriate staleTime based on data volatility
   - Use enabled flag for lazy/conditional loading

5. 🚫 When NOT to use TanStack Query:
   - One-time POST requests
   - File uploads
   - Real-time data (use WebSockets)
   - If you need 100% manual control
*/
