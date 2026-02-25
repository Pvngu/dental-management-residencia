<template>
	<keep-alive>
		<component key="vertical" v-bind:is="'VerticalStyle'" />
	</keep-alive>
</template>

<script>
import common from "../../common/composable/common";
import VerticalStyle from "./VerticalStyle.vue";
import LoadingApp from "./LoadingApp.vue";

export default {
	components: {
		VerticalStyle,
		LoadingApp,
	},
	setup() {
		const { cssSettings } = common();

		return {
			cssSettings,
		};
	},
};
</script>
