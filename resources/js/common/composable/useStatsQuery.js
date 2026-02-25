import { useQuery } from "@tanstack/vue-query";
import { ref, computed } from "vue";

/**
 * Composable for fetching statistics data with TanStack Query
 * @param {string} endpoint - The API endpoint to fetch stats from
 * @param {object} options - Additional query options
 * @returns {object} - Query state and methods
 */
export const useStatsQuery = (endpoint, options = {}) => {
    const {
        refetchInterval = false,
        staleTime = 10 * 60 * 1000, // 10 minutes default (increased for better caching)
        enabled = true,
        onSuccess = null,
        onError = null,
        initialData = null,
    } = options;

    const fetchStats = async () => {
        const response = await axiosAdmin.get(endpoint);
        return response.data;
    };

    const {
        data,
        isLoading,
        isFetching,
        isError,
        error,
        refetch,
        isSuccess,
    } = useQuery({
        queryKey: ["stats", endpoint],
        queryFn: fetchStats,
        refetchInterval,
        staleTime,
        enabled: computed(() => enabled),
        initialData,
        refetchOnMount: false, // Don't refetch when component remounts - use cache
        refetchOnWindowFocus: false, // Don't refetch when window gains focus
        refetchOnReconnect: false, // Don't refetch on internet reconnect
        gcTime: 30 * 60 * 1000, // 30 minutes - keep cache longer
        onSuccess: (data) => {
            if (onSuccess) onSuccess(data);
        },
        onError: (err) => {
            console.error(`Error fetching stats from ${endpoint}:`, err);
            if (onError) onError(err);
        },
    });

    return {
        stats: data,
        isLoading,
        isFetching,
        isError,
        error,
        refetch,
        isSuccess,
    };
};

/**
 * Composable for fetching multiple stats endpoints
 * @param {Array<string>} endpoints - Array of API endpoints
 * @returns {object} - Combined query state
 */
export const useMultipleStatsQuery = (endpoints) => {
    const queries = endpoints.map((endpoint) => useStatsQuery(endpoint));

    const isLoading = computed(() => queries.some((q) => q.isLoading.value));
    const isFetching = computed(() => queries.some((q) => q.isFetching.value));
    const isError = computed(() => queries.some((q) => q.isError.value));

    const refetchAll = () => {
        queries.forEach((q) => q.refetch());
    };

    return {
        queries,
        isLoading,
        isFetching,
        isError,
        refetchAll,
    };
};
