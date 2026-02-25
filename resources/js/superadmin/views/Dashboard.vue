<template>
	<SuperAdminPageHeader>
		<template #header>
			<a-page-header :title="$t(`menu.dashboard`)" style="padding: 0px" />
		</template>
	</SuperAdminPageHeader>

	<div class="dashboard-page-content-container">
		<div class="mt-7 mb-5">
			<a-row :gutter="[15, 15]">
				<a-col :xs="24" :sm="24" :md="12" :lg="6" :xl="6">
					<StateWidget
						:title="$t('superadmin_dashboard.total_companies')"
						:value="responseData.stats ? responseData.stats.totalCompaniesCount : 0"
					/>
				</a-col>
				<a-col :xs="24" :sm="24" :md="12" :lg="6" :xl="6">
					<StateWidget
						:title="$t('superadmin_dashboard.active_companies')"
						:value="responseData.stats ? responseData.stats.activeCompaniesCount : 0"
					/>
				</a-col>
				<a-col :xs="24" :sm="24" :md="12" :lg="6" :xl="6">
					<StateWidget
						:title="$t('superadmin_dashboard.inactive_companies')"
						:value="responseData.stats ? responseData.stats.inactiveCompaniesCount : 0"
					/>
				</a-col>
				<a-col :xs="24" :sm="24" :md="12" :lg="6" :xl="6">
					<StateWidget
						:title="$t('superadmin_dashboard.license_expired')"
						:value="responseData.stats ? responseData.stats.expiredCompaniesCount : 0"
					/>
				</a-col>
			</a-row>
		</div>

		<a-row :gutter="18" class="mt-7 mb-5">
			<a-col :span="24">
				<a-card :title="$t('superadmin_dashboard.recently_registered_companies')">
					<CompanyTable :showFilterInput="false" :perPageItems="5" />
					<template #extra>
						<a-button
							class="mt-2"
							type="link"
							@click="$router.push({ name: 'superadmin.companies.index' })"
						>
							{{ $t("common.view_all") }}
							<DoubleRightOutlined />
						</a-button>
					</template>
				</a-card>
			</a-col>
		</a-row>
	</div>
</template>

<script>
import { ref, onMounted, reactive, toRef, watch } from "vue";
import {
	RocketOutlined,
	DatabaseOutlined,
	EyeInvisibleOutlined,
	WarningOutlined,
	DoubleRightOutlined,
} from "@ant-design/icons-vue";
import SuperAdminPageHeader from "../layouts/SuperAdminPageHeader.vue";
import StateWidget from "../../common/components/common/card/StateWidget.vue";
import CompanyTable from "./companies/CompanyTable.vue";

export default {
	components: {
		SuperAdminPageHeader,

		StateWidget,
		RocketOutlined,
		DatabaseOutlined,
		EyeInvisibleOutlined,
		WarningOutlined,
		DoubleRightOutlined,

		CompanyTable,
	},
	setup() {
		const responseData = ref([]);

		onMounted(() => {
			axiosAdmin.get("superadmin/dashboard").then((dashboardResponse) => {
				responseData.value = dashboardResponse.data;
			});
		});

		return {
			responseData,
		};
	},
};
</script>
