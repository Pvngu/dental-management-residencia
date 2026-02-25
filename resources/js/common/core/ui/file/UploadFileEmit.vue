<template>
	<!-- Secondary Design (Dragger Style) -->
	<div v-if="useSecondaryDesign">
		<div v-if="formData[uploadField] == undefined || noChange">
			<a-upload-dragger
				:accept="acceptFormat"
				v-model:file-list="fileList"
				name="file"
				:customRequest="customRequestSecondary"
				:disabled="disabled"
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
			<a-typography-link @click="onDelete">
				<a-tag color="error">
					<template #icon>
						<DeleteOutlined />
					</template>
					{{ $t("common.delete") }}
				</a-tag>
			</a-typography-link>
		</div>
	</div>

	<!-- Primary Design (Picture Card Style) -->
	<a-upload
		v-else
		:accept="acceptFormat"
		v-model:file-list="fileList"
		name="file"
		list-type="picture-card"
		class="avatar-uploader"
		:show-upload-list="false"
		:customRequest="customRequest"
		:disabled="disabled"
	>
		<div v-if="currentFile != null" class="image-container">
			<img style="width: 128px" :src="currentFile" alt="file preview" />
			<!-- <div v-else class="file-icon">
				<file-outlined />
				<div>{{ fileName }}</div>
			</div> -->
			<div v-if="showDelete" class="delete-icon" @click.stop="removeFile">
				<delete-outlined />
			</div>
		</div>
		<div v-else>
			<loading-outlined v-if="loading"></loading-outlined>
			<plus-outlined v-else></plus-outlined>
			<div class="ant-upload-text">{{ $t("common.upload") }}</div>
		</div>
	</a-upload>
</template>
<script>
import { PlusOutlined, LoadingOutlined, DeleteOutlined, FileOutlined, CloudUploadOutlined, DownloadOutlined } from "@ant-design/icons-vue";
import { defineComponent, ref, watch, computed } from "vue";
import { message } from "ant-design-vue";
import { useI18n } from "vue-i18n";

export default defineComponent({
	props: {
		formData: null,
		uploadField: {
			default: "file",
			type: String,
		},
		folder: {
			default: "default",
			type: String,
		},
		acceptFormat: {
			default: "image/*,.pdf",
			type: String,
		},
		disabled: {
			default: false,
			type: Boolean,
		},
		showDelete: {
			default: true,
			type: Boolean,
		},
		useSecondaryDesign: {
			default: false,
			type: Boolean,
		},
		noChange: {
			default: false,
			type: Boolean,
		},
	},
	components: {
		LoadingOutlined,
		PlusOutlined,
		DeleteOutlined,
		FileOutlined,
		CloudUploadOutlined,
		DownloadOutlined,
	},
	emits: ["onFileUploaded"],
	setup(props, { emit }) {
		const fileList = ref([]);
		const loading = ref(false);
		const currentFile = ref(null);
		const fileName = ref("");
		const { t } = useI18n();
		
		// Check if current file is an image
		const isImage = computed(() => {
			if (!currentFile.value) return false;
			
			// Check if url ends with common image extensions
			const extensions = ['.jpg', '.jpeg', '.png', '.gif', '.bmp', '.webp', '.svg'];
			return extensions.some(ext => 
				currentFile.value.toLowerCase().endsWith(ext)
			) || currentFile.value.startsWith('data:image/');
		});

		// Primary design - client-side file reading and emit
		const customRequest = (info) => {
			loading.value = true;
			
			// Create a reader to get the file as base64 or preview
			const reader = new FileReader();
			
			reader.onload = (e) => {
				// Store file name
				fileName.value = info.file.name;
				
				// Generate a preview if it's an image
				if (info.file.type.startsWith('image/')) {
					currentFile.value = e.target.result;
				} else {
					// For non-images, we'll use a file icon
					currentFile.value = "non-image";
				}
				
				fileList.value = [];
				loading.value = false;
				
				// Emit the file data to the parent component
				emit("onFileUploaded", {
					file: info.file,
					file_url: currentFile.value,
					file_name: fileName.value,
					upload_field: props.uploadField,
					folder: props.folder
				});
			};
			
			reader.onerror = () => {
				loading.value = false;
			};
			
			// Read the file as a data URL if it's an image, otherwise just read as binary
			if (info.file.type.startsWith('image/')) {
				reader.readAsDataURL(info.file);
			} else {
				reader.readAsArrayBuffer(info.file);
				// Set a placeholder for non-image files
				currentFile.value = "non-image";
			}
		};

		// Secondary design - server upload via API
		const customRequestSecondary = (info) => {
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

		watch(
			() => props.formData && props.formData[props.uploadField],
			(newVal) => {
				if (props.formData && props.formData[`${props.uploadField}_url`]) {
					currentFile.value = props.formData[`${props.uploadField}_url`];
					
					// Extract file name from URL if available
					const urlParts = currentFile.value.split('/');
					if (urlParts.length > 0) {
						fileName.value = urlParts[urlParts.length - 1];
					}
				} else {
					currentFile.value = null;
					fileName.value = "";
				}
			},
			{ immediate: true }
		);

		const removeFile = (event) => {
			event.stopPropagation();
			currentFile.value = null;
			fileName.value = "";
			
			if (props.formData) {
				props.formData[props.uploadField] = null;
				props.formData[`${props.uploadField}_url`] = null;
			}
			
			emit("onFileUploaded", {
				file: null,
				file_url: null,
				file_name: null,
				upload_field: props.uploadField,
				folder: props.folder
			});
		};

		// Secondary design - server delete via API
		const onDelete = () => {
			axiosAdmin.delete(`deleteFile/${props.formData[props.uploadField]}`);
			props.formData[props.uploadField] = undefined;
			props.formData[`${props.uploadField}_url`] = undefined;
		};

		return {
			fileList,
			loading,
			customRequest,
			customRequestSecondary,
			currentFile,
			removeFile,
			onDelete,
			fileName,
			isImage
		};
	},
});
</script>
<style scoped>
.avatar-uploader > .ant-upload {
	border-radius: 8px;
	width: 128px;
	height: 128px;
}

.avatar-uploader {
	padding: 30px;
	overflow: hidden; 
}

.ant-upload-select-picture-card i {
	font-size: 32px;
	color: #999;
}

.ant-upload-select-picture-card .ant-upload-text {
	margin-top: 8px;
	color: #666;
}

.image-container {
	position: relative;
	width: 128px;
	height: 128px;
	display: flex;
	align-items: center;
	justify-content: center;
}

.delete-icon {
	position: absolute;
	top: 5px;
	right: 8px;
	color: red;
	width: 20px;
	height: 20px;
	display: flex;
	align-items: center;
	justify-content: center;
	cursor: pointer;
	z-index: 1;
	transition: all 0.3s;
	border-radius: 8px;
}

.delete-icon:hover {
	transform: scale(1.1);
	background-color: #ff7875;
}

.file-icon {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	color: #1890ff;
	font-size: 24px;
}

.file-icon div {
	font-size: 12px;
	margin-top: 8px;
	width: 100px;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
	text-align: center;
}
</style>
