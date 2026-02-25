# TanStack Query Integration Guide

This guide explains how to use TanStack Query (Vue Query) in your Vue 3 application for better data fetching, caching, and state management.

## Overview

We've integrated TanStack Query into the application to provide:
- **Automatic caching** - Reduces unnecessary API calls
- **Background refetching** - Keeps data fresh automatically
- **Request deduplication** - Multiple components requesting same data = single request
- **Optimistic updates** - Better UX with instant UI updates
- **Loading & error states** - Built-in state management
- **Pagination support** - Seamless handling of paginated data

## Installation

TanStack Query is already installed and configured in `app.js`:

```javascript
import { VueQueryPlugin } from '@tanstack/vue-query';

app.use(VueQueryPlugin, {
    queryClientConfig: {
        defaultOptions: {
            queries: {
                refetchOnWindowFocus: false,
                retry: 1,
                staleTime: 5 * 60 * 1000, // 5 minutes
            },
        },
    },
});
```

## Using crudQuery Composable

The `crudQuery` composable is a drop-in replacement for the original `crud` composable with TanStack Query integration.

### Basic Usage

```vue
<script>
import crudQuery from "../../../../common/composable/crudQuery";
import fields from "./fields";

export default {
    setup() {
        const { url, addEditUrl, initData, columns } = fields();
        const crudVariables = crudQuery();

        const filters = ref({
            status: undefined,
        });

        onMounted(() => {
            crudVariables.table.filterableColumns = filterableColumns;
            crudVariables.crudUrl.value = addEditUrl;
            crudVariables.langKey.value = "model_name";
            crudVariables.initData.value = { ...initData };
            crudVariables.formData.value = { ...initData };

            crudVariables.tableUrl.value = {
                url,
                filters,
            };

            crudVariables.fetch({ page: 1 });
        });

        return {
            ...crudVariables,
            filters,
        };
    },
};
</script>
```

### Key Features

#### 1. Automatic Caching
Data is automatically cached with a 30-second stale time. No need to manually manage cache.

#### 2. Background Updates
When data becomes stale, TanStack Query refetches in the background without showing loading states.

#### 3. Built-in Loading States
Access loading states via:
- `table.loading` - Overall loading state
- `isLoading` - Initial load
- `isFetching` - Background refetch

#### 4. Manual Refetch
```javascript
// Refetch data manually
crudVariables.refetchQuery();

// Access QueryClient for advanced operations
crudVariables.queryClient.invalidateQueries({ queryKey: ['crud-data'] });
```

#### 5. Mutations with Auto-Refresh
All CRUD operations automatically refresh data:
```javascript
// Delete automatically refreshes table
crudVariables.showDeleteConfirm(recordId);

// Success actions automatically invalidate cache
crudVariables.addEditSuccess(id);
```

## Using useStatsQuery Composable

For fetching statistics or any non-CRUD data, use the `useStatsQuery` composable.

### Basic Usage

```vue
<script>
import { useStatsQuery } from "../../../../common/composable/useStatsQuery";

export default {
    setup() {
        // Fetch stats with automatic caching
        const { 
            stats, 
            isLoading, 
            isFetching,
            refetch 
        } = useStatsQuery("/doctors/stats", {
            initialData: {
                totalDoctors: 0,
                avgCharge: 0
            },
            staleTime: 2 * 60 * 1000, // 2 minutes
        });

        // Refresh stats after an action
        const handleUpdate = () => {
            refetch();
        };

        return {
            stats,
            isLoading,
            handleUpdate,
        };
    },
};
</script>

<template>
    <StateWidget
        :title="$t('doctors.total')"
        :value="stats?.totalDoctors || 0"
        :loading="isLoading"
    />
</template>
```

### useStatsQuery Options

```javascript
useStatsQuery(endpoint, {
    // Initial data to show while loading
    initialData: null,
    
    // How long data stays fresh (default: 5 minutes)
    staleTime: 5 * 60 * 1000,
    
    // Auto-refetch interval (default: false)
    refetchInterval: 30000, // 30 seconds
    
    // Enable/disable query (default: true)
    enabled: true,
    
    // Success callback
    onSuccess: (data) => {
        console.log('Stats loaded:', data);
    },
    
    // Error callback
    onError: (error) => {
        console.error('Error loading stats:', error);
    },
});
```

### Multiple Stats Queries

Fetch multiple stats endpoints simultaneously:

```javascript
import { useMultipleStatsQuery } from "../../../../common/composable/useStatsQuery";

const { queries, isLoading, refetchAll } = useMultipleStatsQuery([
    "/doctors/stats",
    "/appointments/stats",
    "/patients/stats"
]);

// Access individual query results
const doctorStats = queries[0].stats;
const appointmentStats = queries[1].stats;
const patientStats = queries[2].stats;

// Refetch all at once
refetchAll();
```

## Migration Guide

### From `crud` to `crudQuery`

Replace the import:
```javascript
// Before
import crud from "../../../../common/composable/crud";
const crudVariables = crud();

// After
import crudQuery from "../../../../common/composable/crudQuery";
const crudVariables = crudQuery();
```

Everything else works the same! The API is backward compatible.

### From Manual Stats Fetching to `useStatsQuery`

Before:
```javascript
const doctorStats = ref({ total: 0 });
const loading = ref(true);

const fetchStats = async () => {
    try {
        loading.value = true;
        const response = await axiosAdmin.get("/doctors/stats");
        doctorStats.value = response.data;
    } catch (error) {
        console.error(error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchStats();
});
```

After:
```javascript
const { 
    stats: doctorStats, 
    isLoading: loading 
} = useStatsQuery("/doctors/stats", {
    initialData: { total: 0 }
});
```

## Advanced Usage

### Custom Query Keys

```javascript
// In crudQuery.js, queries are keyed by:
[
    "crud-data",
    tableUrl.value.url,
    currentPage.value,
    table.pagination.pageSize,
    table.sorter,
    table.searchColumn,
    table.searchString,
    filters,
    extraFilters,
]
```

### Invalidating Specific Queries

```javascript
import { useQueryClient } from "@tanstack/vue-query";

const queryClient = useQueryClient();

// Invalidate all CRUD queries
queryClient.invalidateQueries({ queryKey: ['crud-data'] });

// Invalidate stats query
queryClient.invalidateQueries({ queryKey: ['stats', '/doctors/stats'] });

// Invalidate all queries
queryClient.invalidateQueries();
```

### Prefetching Data

```javascript
const queryClient = useQueryClient();

// Prefetch data before navigation
const prefetchNextPage = () => {
    queryClient.prefetchQuery({
        queryKey: ['crud-data', page + 1],
        queryFn: () => fetchData(page + 1),
    });
};
```

## Best Practices

1. **Use appropriate staleTime values**
   - Frequently changing data: 30-60 seconds
   - Moderate updates: 2-5 minutes
   - Rarely changing: 10-30 minutes

2. **Initialize with default data**
   ```javascript
   useStatsQuery("/stats", {
       initialData: { count: 0 } // Prevents undefined errors
   });
   ```

3. **Refetch after mutations**
   ```javascript
   const handleSave = () => {
       // Save logic...
       refetchStats(); // Refresh stats after save
   };
   ```

4. **Use loading states appropriately**
   ```javascript
   // Show skeleton on initial load
   <a-skeleton v-if="isLoading" />
   
   // Show spinner on refetch
   <a-spin v-if="isFetching && !isLoading" />
   ```

5. **Leverage automatic caching**
   - Don't fetch same data multiple times
   - Let TanStack Query deduplicate requests

## Troubleshooting

### Data not updating?
- Check if query key includes all dependencies
- Ensure `refetch()` is called after mutations
- Verify `staleTime` isn't too long

### Too many requests?
- Increase `staleTime`
- Disable `refetchOnWindowFocus`
- Use `refetchInterval` carefully

### Memory issues?
- Adjust `gcTime` (garbage collection time)
- Use `keepPreviousData` option for pagination

## Resources

- [TanStack Query Docs](https://tanstack.com/query/latest/docs/vue/overview)
- [Vue Query Guide](https://tanstack.com/query/latest/docs/vue/guides/queries)
- [Query Keys](https://tanstack.com/query/latest/docs/vue/guides/query-keys)
