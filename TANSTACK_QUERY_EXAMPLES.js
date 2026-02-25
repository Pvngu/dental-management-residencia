// ====================================
// EXAMPLE: Doctors Index - Before/After TanStack Query
// ====================================

// ============ BEFORE ============
import { ref, onMounted } from "vue";
import crud from "../../../../common/composable/crud";

export default {
    setup() {
        const crudVariables = crud();
        const doctorStats = ref({ totalDoctors: 0 });
        const loading = ref(true);

        // Manual stats fetching
        const fetchDoctorStats = async () => {
            try {
                const response = await axiosAdmin.get("/doctors/stats");
                doctorStats.value = response.data;
            } catch (error) {
                console.error("Error:", error);
                doctorStats.value = { totalDoctors: 0 };
            } finally {
                loading.value = false;
            }
        };

        // Manual refetch on success
        const originalAddEditSuccess = crudVariables.addEditSuccess;
        crudVariables.addEditSuccess = (id) => {
            originalAddEditSuccess(id);
            fetchDoctorStats(); // Manual refetch
        };

        onMounted(() => {
            setUrlData();
            fetchDoctorStats(); // Manual fetch
        });

        return {
            ...crudVariables,
            doctorStats,
            loading,
        };
    },
};

// ============ AFTER ============
import { ref, onMounted } from "vue";
import crudQuery from "../../../../common/composable/crudQuery";
import { useStatsQuery } from "../../../../common/composable/useStatsQuery";

export default {
    setup() {
        const crudVariables = crudQuery(); // ✅ Drop-in replacement

        // ✅ Automatic caching, refetching, loading states
        const { 
            stats: doctorStats, 
            isLoading: loading,
            refetch: refetchStats 
        } = useStatsQuery("/doctors/stats", {
            initialData: { totalDoctors: 0 },
            staleTime: 2 * 60 * 1000, // Cache for 2 minutes
        });

        // ✅ Simple refetch on success
        const originalAddEditSuccess = crudVariables.addEditSuccess;
        crudVariables.addEditSuccess = (id) => {
            originalAddEditSuccess(id);
            refetchStats(); // Automatic cache invalidation
        };

        onMounted(() => {
            setUrlData();
            // ✅ No manual fetch needed - useStatsQuery handles it
        });

        return {
            ...crudVariables,
            doctorStats,
            loading,
        };
    },
};

// ====================================
// BENEFITS
// ====================================

/*
✅ Automatic Caching
   - Data cached for 2 minutes
   - No duplicate requests
   - Instant data on revisit

✅ Background Refetching
   - Data stays fresh automatically
   - No loading spinners on refetch
   - Better UX

✅ Error Handling
   - Built-in error states
   - Automatic retries
   - Fallback to initial data

✅ Less Boilerplate
   - No try/catch blocks
   - No loading state management
   - No manual error handling

✅ Request Deduplication
   - Multiple components = single request
   - Prevents race conditions
   - Optimizes network usage

✅ Developer Experience
   - DevTools integration
   - Easy debugging
   - Type-safe
*/

// ====================================
// ADDITIONAL FEATURES
// ====================================

// 1. Multiple queries simultaneously
const { queries, isLoading, refetchAll } = useMultipleStatsQuery([
    "/doctors/stats",
    "/appointments/stats",
]);

// 2. Conditional queries
const { stats } = useStatsQuery("/data", {
    enabled: computed(() => user.value.isAdmin),
});

// 3. Polling (auto-refresh)
const { stats } = useStatsQuery("/live-data", {
    refetchInterval: 30000, // Every 30 seconds
});

// 4. Optimistic updates
const { mutate } = useMutation({
    mutationFn: updateDoctor,
    onMutate: async (newData) => {
        // Cancel outgoing refetches
        await queryClient.cancelQueries(['doctors']);
        
        // Snapshot previous value
        const previous = queryClient.getQueryData(['doctors']);
        
        // Optimistically update
        queryClient.setQueryData(['doctors'], (old) => ({
            ...old,
            ...newData,
        }));
        
        return { previous };
    },
    onError: (err, newData, context) => {
        // Rollback on error
        queryClient.setQueryData(['doctors'], context.previous);
    },
});

// 5. Dependent queries
const { stats: doctorStats } = useStatsQuery("/doctors/stats");
const { stats: departmentStats } = useStatsQuery(
    `/departments/${doctorStats.value?.departmentId}/stats`,
    {
        enabled: computed(() => !!doctorStats.value?.departmentId),
    }
);
