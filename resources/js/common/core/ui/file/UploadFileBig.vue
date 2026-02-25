<template>
	<div v-if="formData[uploadField] == undefined || noChange">
		<a-upload-dragger
			:accept="acceptFormat"
			v-model:file-list="fileList"
			name="file"
			:customRequest="customRequest"
		>
			<p class="ant-upload-drag-icon">
				<CloudUploadOutlined />
			</p>
			<p class="ant-upload-text">{{ $t("common.drag_n_drop") }}</p>
		</a-upload-dragger>
	</div>
	<div v-else>
		<a-typography-link :href="formData[`${uploadField}_url`]" target="_blank">
			<a-tag color="success">
				<template #icon>
					<DownloadOutlined />
				</template>
				{{ $t("common.download") }}
			</a-tag>
		</a-typography-link>
		<a-typography-link
			@click="onDelete"
		>
			<a-tag color="error">
				<template #icon>
					<DeleteOutlined />
				</template>
				{{ $t("common.delete") }}
			</a-tag>
		</a-typography-link>
	</div>
</template>
<script>
import { UploadOutlined, DownloadOutlined, DeleteOutlined, CloudUploadOutlined } from "@ant-design/icons-vue";
import { message } from "ant-design-vue";
import { defineComponent, ref } from "vue";
import { useI18n } from "vue-i18n";

export default defineComponent({
	props: {
		acceptFormat: {
			default: "image/*,.pdf",
			type: String,
		},
		formData: null,
		folder: String,
		uploadField: {
			default: "file",
			type: String,
		},
		noChange: {
			default: false,
			type: Boolean,
		},
	},
	components: {
		UploadOutlined,
		DownloadOutlined,
		DeleteOutlined,
		CloudUploadOutlined
	},
	methods: {
		onDelete() {
			axiosAdmin.delete(`deleteFile/${this.formData[this.uploadField]}`);
			this.formData[this.uploadField] = undefined;
			this.formData[`${this.uploadField}_url`] = undefined;
		},
	},

	setup(props, { emit }) {
		const fileList = ref([]);
		const loading = ref(false);
		const { t } = useI18n();

		const customRequest = (info) => {
			console.log(info)
			const formData = new FormData();
			formData.append("file", info.file);
			formData.append("folder", props.folder);

			loading.value = true;

			axiosAdmin
				.post("upload-file", formData, {
					headers: {
						"Content-Type": "multipart/form-data",
					},
				})
				.then((response) => {
					fileList.value = [];
					loading.value = false;

					emit("onFileUploaded", {
						file: response.data.file,
						file_url: response.data.file_url,
						name: info.file.name.replace(/\.[^/.]+$/, ""),
						size: info.file.size,
						type: info.file.type,
					});
				})
				.catch(() => {
					loading.value = false;
					message.error(t("messages.uploading_failed"));
				});
		};

		return {
			fileList,
			loading,
			customRequest,
		};
	},
});
</script>
