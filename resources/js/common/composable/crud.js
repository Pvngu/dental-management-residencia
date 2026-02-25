import { reactive, ref, computed, watch, createVNode } from "vue";
import { useQuery, useMutation, useQueryClient } from "@tanstack/vue-query";
import { Modal, notification } from "ant-design-vue";
import { useI18n } from "vue-i18n";
import { useStore } from "vuex";
import { ExclamationCircleOutlined } from "@ant-design/icons-vue";
import { includes, has, get, forEach, forOwn, filter, debounce } from "lodash-es";

/**
 * TanStack Query-powered CRUD composable
 * Maintains backward compatibility with existing crud composable interface
 */
const crudQuery = () => {
    const { t } = useI18n();
    const store = useStore();
    const queryClient = useQueryClient();

    // Table state
    const table = reactive({
        data: [],
        pagination: {
            pageSize: 10,
            showSizeChanger: true,
            current: 1,
            total: 0,
        },
        loading: false,
        sorter: {},
        searchColumn: undefined,
        searchString: "",
        searchStatus: undefined,
        filterLoading: false,
        filterableColumns: [],
        selectedRowKeys: [],
        tableColumns: [],
    });

    // CRUD state
    const detailsVisible = ref(false);
    const viewData = ref({});
    const crudUrl = ref("");
    const langKey = ref("");
    const initData = ref({});
    const hashableColumns = ref([]);
    const multiDimensalObjectColumns = ref({});
    const addEditVisible = ref(false);
    const addEditType = ref("add");
    const addEditUrl = ref("");
    const formData = ref({});
    const currentPage = ref(1);
    const tableUrl = ref({});
    const hashable = ref([]);
    const sendingUrl = ref("");
    const exportDetails = ref({
        allowExport: false,
        exportType: "",
    });
    const queryEnabled = ref(false); // Control when query should run
    const hasInitialFetch = ref(false); // Track if initial fetch has occurred
    
    // Generate URL for queries
    const generateUrl = (limit, offset) => {
        let url = tableUrl.value.url;
        let filterString = "";
        let hashableString = "";
        let trimString = false;
        let trimHashable = false;

        // Filters
        if (
            "filterString" in tableUrl.value &&
            tableUrl.value.filterString != "" &&
            typeof tableUrl.value.filterString == "string"
        ) {
            filterString += `${tableUrl.value.filterString} and `;
            trimString = true;
        }

        if (typeof tableUrl.value.filters == "object") {
            forOwn(tableUrl.value.filters.value || tableUrl.value.filters, (value, key) => {
                if (value != undefined && value != "") {
                    filterString += `${key} eq "${value}" and `;
                    trimString = true;

                    // May be Hashable
                    if (includes(hashable.value, key)) {
                        hashableString += `${value},`;
                        trimHashable = true;
                    }
                }
            });
        }

        if (
            table.searchColumn != undefined &&
            table.searchString != "" &&
            table.searchColumn != "full_name"
        ) {
            filterString += `${table.searchColumn} lk "%${table.searchString}%" and `;
            trimString = true;
            table.filterLoading = true;
        } else if (
            table.searchString != "" &&
            table.filterableColumns.length > 0 &&
            table.searchColumn != "full_name"
        ) {
            let newSearchString = "";
            forEach(table.filterableColumns, (filterColumn) => {
                if (filterColumn.key !== "full_name") {
                    newSearchString += `${filterColumn.key} lk "%${table.searchString}%" or `;
                }
            });

            if (newSearchString.length > 0) {
                filterString += `(${newSearchString.substring(0, newSearchString.length - 3)})`;
                trimString = false;
            }
            table.filterLoading = true;
        }

        if (filterString.length > 0 && trimString == true) {
            url += `&filters=${encodeURIComponent(
                filterString.substring(0, filterString.length - 5)
            )}`;
        } else if (filterString.length > 0 && trimString == false) {
            url += `&filters=${encodeURIComponent(filterString)}`;
        }

        // Extra Filters
        if (tableUrl.value.extraFilters && typeof tableUrl.value.extraFilters == "object") {
            forOwn(tableUrl.value.extraFilters.value || tableUrl.value.extraFilters, (value, key) => {
                if (value != undefined && value != "") {
                    url += `&${key}=${value}`;

                    // May be Hashable
                    if (includes(hashable.value, key)) {
                        hashableString += `${value},`;
                        trimHashable = true;
                    }
                }
            });
        }

        // Order
        if (table.sorter && table.sorter.field) {
            const sortOrder =
                table.sorter.order == "ascend" || table.sorter.order == "asc" ? "asc" : "desc";
            url += `&order=${table.sorter.field} ${sortOrder}`;
        } else {
            url += "&order=id desc";
        }

        // Offset and Limit
        url += `&offset=${offset}&limit=${limit}`;

        // Hashable
        if (trimHashable) {
            hashableString = hashableString.substring(0, hashableString.length - 1);
            url += `&hashable=${hashableString}`;
        }

        return url;
    };

    // Query key generator
    const getQueryKey = () => {
        return [
            "crud-data",
            tableUrl.value.url,
            currentPage.value,
            table.pagination.pageSize,
            table.sorter,
            table.searchColumn,
            table.searchString,
            tableUrl.value.filters?.value || tableUrl.value.filters,
            tableUrl.value.extraFilters?.value || tableUrl.value.extraFilters,
        ];
    };

    // Fetch data function
    const queryData = async (params) => {
        const limit = params.limit;
        const offset = (params.page - 1) * limit;
        const url = generateUrl(limit, offset);
        sendingUrl.value = url;
        const response = await axiosAdmin.get(url);
        return response;
    };

    // TanStack Query for fetching data
    const {
        data: queryResult,
        isLoading,
        isFetching,
        refetch: refetchQuery,
        error,
    } = useQuery({
        queryKey: getQueryKey,
        queryFn: () =>
            queryData({
                limit: table.pagination.pageSize,
                page: currentPage.value,
            }),
        enabled: computed(() => 
            tableUrl.value.url != null && 
            tableUrl.value.url != ""
        ),
        staleTime: 10 * 60 * 1000, // 10 minutes - data stays fresh longer
        gcTime: 30 * 60 * 1000, // 30 minutes - cache kept longer
        refetchOnMount: false, // Don't refetch when component remounts
        refetchOnWindowFocus: false, // Already disabled in global config
        refetchOnReconnect: false, // Don't refetch on internet reconnect
    });

    // Watch query result and update table
    watch(
        () => queryResult.value,
        (newResult) => {
            if (newResult) {
                const data = newResult.data;
                const pagination = { ...table.pagination };
                pagination.total = newResult.meta.paging.total;
                pagination.current = currentPage.value;
                table.data = data;
                table.pagination = pagination;
                table.filterLoading = false;
                table.selectedRowKeys =
                    table.selectedRowKeys.length > 0 ? table.selectedRowKeys : [];

                // For export data
                if (exportDetails.value.allowExport) {
                    const exportDataType = exportDetails.value.exportType;
                    if (exportDataType != "") {
                        const allExportDatas = store.state.auth.allExportData;
                        const allExportDataExceptType = filter(
                            allExportDatas,
                            (allExportData) => allExportData.export_type != exportDataType
                        );

                        store.commit("auth/updatAllExportData", [
                            ...allExportDataExceptType,
                            {
                                export_type: exportDataType,
                                data: data,
                                url: sendingUrl.value,
                            },
                        ]);
                    }
                }
            }
        }
    );

    // Watch loading state
    watch([isLoading, isFetching], ([loading, fetching]) => {
        table.loading = loading || fetching;
    });

    // Fetch function (maintains backward compatibility)
    const fetch = (params = {}) => {
        if (params.page) {
            currentPage.value = params.page;
        }
        if (params.limit) {
            table.pagination.pageSize = params.limit;
        }
        
        // First fetch: Query will auto-fetch when URL is set (enabled becomes true)
        // Subsequent fetches: Only refetch if it's a user action (not onMounted)
        if (hasInitialFetch.value) {
            // This is a user action (filter change, search, pagination, etc)
            refetchQuery();
        } else {
            // First time setup - mark as initialized
            // TanStack Query will handle fetching automatically
            hasInitialFetch.value = true;
        }
    };

    // Handle table change
    const handleTableChange = (pagination, filters, sorter) => {
        const pager = { ...table.pagination };
        pager.current = pagination.current;
        pager.pageSize = pagination.pageSize;
        table.pagination = pager;
        currentPage.value = pagination.current;
        table.sorter = sorter;

        fetch({
            limit: pagination.pageSize,
            page: pagination.current,
        });
    };

    // Search with debounce
    const onTableSearch = debounce(() => {
        fetch({ page: 1 });
    }, 400);

    // Row selection
    const onRowSelectChange = (rowKeys) => {
        table.selectedRowKeys = rowKeys;
    };

    // Delete mutation
    const deleteMutation = useMutation({
        mutationFn: (id) => axiosAdmin.delete(`${crudUrl.value}/${id}`),
        onSuccess: () => {
            updateSubscriptionModules();
            refetchQuery();
            notification.success({
                message: t("common.success"),
                description: t(`${langKey.value}.deleted`),
                placement: "bottomRight",
            });
        },
    });

    // Batch delete mutation
    const batchDeleteMutation = useMutation({
        mutationFn: (ids) => {
            const deletePromises = ids.map((id) => axiosAdmin.delete(`${crudUrl.value}/${id}`));
            return Promise.all(deletePromises);
        },
        onSuccess: () => {
            updateSubscriptionModules();
            refetchQuery();
            notification.success({
                message: t("common.success"),
                description: t(`${langKey.value}.deleted`),
                placement: "bottomRight",
            });
        },
    });

    // View item
    const viewItem = (item) => {
        detailsVisible.value = true;
        viewData.value = item;
    };

    // Add item
    const addItem = () => {
        addEditUrl.value = crudUrl.value;
        addEditType.value = "add";
        addEditVisible.value = true;
        viewData.value = {};
    };

    // Close add/edit modal
    const onCloseAddEdit = () => {
        restFormData();
        addEditVisible.value = false;
    };

    // Close details modal
    const onCloseDetails = () => {
        detailsVisible.value = false;
        viewData.value = {};
    };

    // Edit item
    const editItem = (item) => {
        const itemDetails = {};
        const multiDimension = multiDimensalObjectColumns.value;

        Object.keys(initData.value).forEach((key) => {
            if (has(multiDimension, key)) {
                const multiDimensalObjectColumnValue = multiDimension[key];
                itemDetails[key] = get(item, multiDimensalObjectColumnValue);
            } else if (includes(hashableColumns.value, key)) {
                itemDetails[key] = item[`x_${key}`];
            } else {
                itemDetails[key] = item[key];
            }
        });

        itemDetails["_method"] = "PUT";
        formData.value = itemDetails;

        viewData.value = item;
        addEditUrl.value = `${crudUrl.value}/${item.xid}`;
        addEditType.value = "edit";
        addEditVisible.value = true;
    };

    // Update subscription modules
    const updateSubscriptionModules = () => {
        store.dispatch("auth/updateVisibleSubscriptionModules");
    };

    // Success actions
    const addEditSuccess = (id) => {
        successAction(id, "add-edit");
    };

    const addAndNewSuccess = (id) => {
        successAction(id, "add-new");
    };

    const addAndContinueSuccess = (id) => {
        successAction(id, "add-continue");
    };

    const successAction = (id, submitType = "add-edit") => {
        // If add action is performed then move page to first
        if (addEditType.value == "add") {
            currentPage.value = 1;
        }

        // Update Visible Subscription Modules
        updateSubscriptionModules();

        if (submitType == "add-edit" || submitType == "add-new") {
            restFormData();
        }

        // Invalidate and refetch queries
        queryClient.invalidateQueries({ queryKey: ["crud-data"] });
        refetchQuery();

        if (submitType == "add-edit") {
            addEditVisible.value = false;
        }
    };

    // Show delete confirmation
    const showDeleteConfirm = (id) => {
        Modal.confirm({
            title: t("common.delete") + "?",
            icon: createVNode(ExclamationCircleOutlined),
            content: t(`${langKey.value}.delete_message`),
            centered: true,
            okText: t("common.yes"),
            okType: "danger",
            cancelText: t("common.no"),
            onOk() {
                return deleteMutation.mutateAsync(id);
            },
            onCancel() {},
        });
    };

    // Show batch delete confirmation
    const showSelectedDeleteConfirm = () => {
        Modal.confirm({
            title: t("common.delete") + "?",
            icon: createVNode(ExclamationCircleOutlined),
            content: t(`${langKey.value}.selected_delete_message`),
            centered: true,
            okText: t("common.yes"),
            okType: "danger",
            cancelText: t("common.no"),
            onOk() {
                return batchDeleteMutation.mutateAsync(table.selectedRowKeys);
            },
            onCancel() {},
        });
    };

    // Rest form data
    const restFormData = () => {
        formData.value = { ...initData.value };
    };

    // Assign new form data
    const assignNewFormData = (newObject) => {
        formData.value = {
            ...formData.value,
            ...newObject,
        };
    };

    // Computed properties
    const pageTitle = computed(() => {
        if (langKey.value != "") {
            return addEditType.value == "add"
                ? t(`${langKey.value}.add`)
                : t(`${langKey.value}.edit`);
        } else {
            return "";
        }
    });

    const successMessage = computed(() => {
        if (langKey.value != "") {
            return addEditType.value == "add"
                ? t(`${langKey.value}.created`)
                : t(`${langKey.value}.updated`);
        } else {
            return "";
        }
    });

    // Watch hashable columns
    watch(hashableColumns, (newVal) => {
        hashable.value = newVal;
    });

    return {
        // State
        detailsVisible,
        viewData,
        addEditVisible,
        addEditType,
        addEditUrl,
        currentPage,
        formData,
        initData,
        crudUrl,
        langKey,
        hashableColumns,
        multiDimensalObjectColumns,
        pageTitle,
        successMessage,
        table,
        tableUrl,
        hashable,
        exportDetails,

        // Methods
        viewItem,
        addItem,
        editItem,
        onCloseAddEdit,
        onCloseDetails,
        showDeleteConfirm,
        showSelectedDeleteConfirm,
        addEditSuccess,
        addAndNewSuccess,
        addAndContinueSuccess,
        restFormData,
        assignNewFormData,
        handleTableChange,
        fetch,
        onTableSearch,
        onRowSelectChange,

        // TanStack Query specific
        refetchQuery,
        queryClient,
        isLoading,
        isFetching,
    };
};

export default crudQuery;
