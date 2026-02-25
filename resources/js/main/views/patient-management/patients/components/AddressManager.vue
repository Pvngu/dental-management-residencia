<template>
    <div class="address-manager">
        <a-card 
            v-for="(address, index) in addresses" 
            :key="index"
            class="address-card mb-4!"
            :title="`${$t('common.address')} ${index + 1}`"
        >
            <template #extra>
                <a-space>
                    <a-switch
                        :checked="address.is_default"
                        :checkedChildren="$t('address.default')"
                        :unCheckedChildren="$t('address.not_default')"
                        :disabled="!address.status || anyEditing"
                        @change="(checked) => setDefaultAddress(index, checked)"
                    />
                    <a-button 
                        v-if="!editingAddress[index]"
                        type="primary"
                        size="small"
                        @click="startEditing(index)"
                    >
                        <template #icon><EditOutlined /></template>
                    </a-button>
                    <a-button 
                        v-if="!editingAddress[index] && !address.is_default"
                        type="danger" 
                        size="small"
                        @click="showDeleteConfirm(index)"
                        v-show="addresses.length > 1 && !isNewAddress(index)"
                    >
                        <template #icon><DeleteOutlined /></template>
                    </a-button>
                </a-space>
            </template>

            <!-- View mode -->
            <div v-if="!editingAddress[index]" class="address-display">
                <a-row :gutter="16">
                    <a-col :xs="24" :sm="24" :md="12" :lg="12">
                        <a-descriptions :column="1" size="small">
                            <a-descriptions-item :label="$t('address.address_type')">
                                {{ $t(`address.${address.address_type}`) || address.address_type }}
                            </a-descriptions-item>
                            <a-descriptions-item :label="$t('address.street_address')">
                                {{ address.address_line_1 }}
                            </a-descriptions-item>
                        </a-descriptions>
                    </a-col>
                    <a-col :xs="24" :sm="24" :md="12" :lg="12">
                        <a-descriptions :column="1" size="small">
                            <a-descriptions-item :label="$t('address.zip_code')">
                                {{ address.postal_code || address.zip_code }}
                            </a-descriptions-item>
                            <a-descriptions-item :label="$t('address.city')">
                                {{ address.city }}{{ address.neighborhood ? `, ${address.neighborhood}` : '' }}
                            </a-descriptions-item>
                            <a-descriptions-item :label="$t('address.state')">
                                {{ address.state || address.state_name }}
                            </a-descriptions-item>
                            <a-descriptions-item :label="$t('address.country')">
                                {{ getCountryName(address.country_code) }}
                            </a-descriptions-item>
                        </a-descriptions>
                    </a-col>
                </a-row>
            </div>

            <!-- Edit mode -->
            <div v-else class="address-form">
                <a-row :gutter="16">
                    <a-col :xs="24" :sm="24" :md="12" :lg="12">
                        <a-form-item
                            :label="$t('address.address_type')"
                            :name="`addresses.${index}.address_type`"
                            :help="getFieldError(index, 'address_type')"
                            :validateStatus="getFieldError(index, 'address_type') ? 'error' : null"
                            class="required"
                        >
                            <a-select
                                :value="address.address_type"
                                :placeholder="$t('common.select_default_text', [$t('address.address_type')])"
                                style="width: 100%"
                                @change="(value) => updateAddressField(index, 'address_type', value)"
                            >
                                <a-select-option value="home">{{ $t('address.home') }}</a-select-option>
                                <a-select-option value="work">{{ $t('address.work') }}</a-select-option>
                                <a-select-option value="billing">{{ $t('address.billing') }}</a-select-option>
                                <a-select-option value="shipping">{{ $t('address.shipping') }}</a-select-option>
                                <a-select-option value="other">{{ $t('address.other') }}</a-select-option>
                            </a-select>
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="24" :md="12" :lg="12">
                        <a-form-item
                            :label="$t('address.status')"
                            :name="`addresses.${index}.status`"
                        >
                            <a-switch
                                :checked="address.status"
                                :checkedChildren="$t('common.active')"
                                :unCheckedChildren="$t('common.inactive')"
                                :disabled="address.is_default"
                                @change="(value) => updateAddressField(index, 'status', value)"
                            />
                        </a-form-item>
                    </a-col>
                </a-row>

                <!-- Using AddressForm component for address fields -->
                <AddressForm
                    :model-value="address"
                    :rules="getAddressFormRules(index)"
                    :google-api-key="googleMapsApiKey"
                    @update:modelValue="onAddressUpdate(index, $event)"
                />
            </div>
            <template #actions v-if="editingAddress[index]">
                <a-row justify="end" align="middle" class="mr-5">
                    <a-col>
                        <a-button
                            type="primary"
                            @click="saveAddress(index)"
                            :loading="savingAddress[index]"
                        >
                            <template #icon>
                                <SaveOutlined v-if="!savingAddress[index]" />
                            </template>
                            {{ $t('common.save') }}
                        </a-button>
                        <a-button
                            style="margin-left: 8px"
                            @click="cancelEditing(index)"
                            v-if="!(addresses.length === 1 && isNewAddress(index))"
                        >
                            {{ $t('common.cancel') }}
                        </a-button>
                    </a-col>
                </a-row>
            </template>
        </a-card>

        <a-button 
            type="dashed"
            class="w-full"
            @click="addAddress"
            :disabled="isAddingNewAddress"
        >
            <PlusOutlined /> {{ $t('address.add_address') }}
        </a-button>
    </div>
</template>

<script>
import { defineComponent, ref, watch, createVNode, nextTick, computed } from 'vue';
import { PlusOutlined, DeleteOutlined, ExclamationCircleOutlined, EditOutlined, SaveOutlined, CloseOutlined, LoadingOutlined } from '@ant-design/icons-vue';
import { Modal, message } from 'ant-design-vue';
import AddressForm from '../../../../../common/components/common/address/AddressForm.vue';
import apiAdmin from '../../../../../common/composable/apiAdmin';
import common from '../../../../../common/composable/common';

export default defineComponent({
    name: 'AddressManager',
    props: {
        modelValue: { type: Array, default: () => [] },
        rules: { type: Object, default: () => ({}) },
        userId: { type: String, required: true },
    },
    emits: ['update:modelValue'],
    components: {
        PlusOutlined,
        DeleteOutlined,
        EditOutlined,
        SaveOutlined,
        CloseOutlined,
        LoadingOutlined,
        AddressForm,
    },
    setup(props, { emit }) {
        const addresses = ref([]);
        const editingAddress = ref({});
        const addressBackups = ref({});
        const isAddingNewAddress = ref(false);
        const newAddressIndexes = ref([]);
        const savingAddress = ref({});
        const { addEditRequestAdmin } = apiAdmin();
        const { googleMapsApiKey } = common();

        const anyEditing = computed(() => {
            return Object.values(editingAddress.value).some(v => v === true);
        });

        // Map of country codes to names
        const countryNames = {
            'MX': 'Mexico',
            'US': 'United States',
            'CA': 'Canada',
        };

        // Helper function to get country name from code
        const getCountryName = (code) => {
            return countryNames[code] || code;
        };

        // Initialize addresses
        const initializeAddresses = () => {
            // Reset state
            newAddressIndexes.value = [];
            savingAddress.value = {};
            
            if (props.modelValue && props.modelValue.length > 0) {
                addresses.value = props.modelValue.map(addr => {
                    // If zip_code is an object, use its code property
                    let zipCodeValue = addr.zip_code;
                    let city = addr.city || '';
                    let stateName = addr.state_name || '';
                    let countryCode = addr.country_code || 'MX';
                    let countryName = addr.country_name || '';
                    
                    if (addr.zip_code && typeof addr.zip_code === 'object') {
                        zipCodeValue = addr.zip_code.code || addr.zip_code;
                        if (addr.zip_code.city && !city) city = addr.zip_code.city;
                        if (addr.zip_code.state && addr.zip_code.state.name && !stateName) {
                            stateName = addr.zip_code.state.name;
                        }
                        if (addr.zip_code.state && addr.zip_code.state.country) {
                            if (!countryCode || countryCode === 'MX') {
                                countryCode = addr.zip_code.state.country.code || countryCode;
                            }
                            if (!countryName) {
                                countryName = addr.zip_code.state.country.name || '';
                            }
                        }
                    }
                    
                    return {
                        ...addr,
                        zip_code: addr.postal_code || addr.zip_code || zipCodeValue,
                        postal_code: addr.postal_code || addr.zip_code || zipCodeValue,
                        city: city,
                        state_name: addr.state || addr.state_name || stateName,
                        state: addr.state || addr.state_name || stateName,
                        country_code: countryCode,
                        country_name: countryName,
                        status: addr.status !== undefined ? addr.status : true, // Ensure status is set
                    };
                });
            } else {
                const newIndex = 0;
                addresses.value = [{
                    address_line_1: "",
                    address_line_2: "",
                    neighborhood: undefined,
                    zip_code: "",
                    postal_code: "", // Add both field names for compatibility
                    zip_code_id: undefined,
                    city: "",
                    municipality: "",
                    state_name: "",
                    state: "", // Add both field names for compatibility
                    state_code: undefined,
                    state_xid: undefined,
                    country_code: "MX",
                    country_name: "",
                    country_xid: undefined,
                    address_type: "home",
                    is_default: true,
                    status: true,
                }];
                // Set the first address as new and in edit mode
                newAddressIndexes.value.push(newIndex);
                editingAddress.value[newIndex] = true;
            }
            emitChange();
        };

        const emitChange = () => {
            console.log('emitChange called with addresses:', addresses.value.length, 'items'); // Debug log
            emit('update:modelValue', [...addresses.value]);
        };

        const addAddress = () => {
            // Close any other open edit first so only one can be edited at a time
            const currentlyEditing = Object.keys(editingAddress.value).map(k => parseInt(k)).find(i => editingAddress.value[i]);
            if (currentlyEditing !== undefined && currentlyEditing !== null) {
                closeEditingSilently(currentlyEditing);
            }

            const newIndex = addresses.value.length;
            addresses.value.push({
                address_line_1: "",
                address_line_2: "",
                neighborhood: undefined,
                postal_code: "", // Use postal_code to match backend
                zip_code: "", // Add both field names for compatibility
                zip_code_id: undefined,
                city: "",
                municipality: "",
                state_name: "",
                state: "", // Add both field names for compatibility
                state_code: undefined,
                state_xid: undefined,
                country_code: "MX",
                country_name: "",
                country_xid: undefined,
                address_type: "home",
                is_default: addresses.value.length === 0, // First address is default
                status: true,
            });
            
            // Set the new address to edit mode
            isAddingNewAddress.value = true;
            newAddressIndexes.value.push(newIndex);
            editingAddress.value = { ...editingAddress.value, [newIndex]: true };
            emitChange();
        };
        
        // Helper to check if an address is new (hasn't been saved yet)
        const isNewAddress = (index) => {
            return newAddressIndexes.value.includes(index);
        };

        const startEditing = (index) => {
            // If another address is currently in edit mode, close it first
            const currentlyEditing = Object.keys(editingAddress.value).map(k => parseInt(k)).find(i => editingAddress.value[i]);
            if (currentlyEditing !== undefined && currentlyEditing !== null && currentlyEditing !== index) {
                closeEditingSilently(currentlyEditing);
            }

            // Create a backup of the current address state
            addressBackups.value[index] = JSON.stringify(addresses.value[index]);
            // Set this address to edit mode
            editingAddress.value = { ...editingAddress.value, [index]: true };
        };

        // Close an edit without removing a new address or altering array indexes.
        // Restores from backup if available and clears the editing flag.
        const closeEditingSilently = (oldIndex) => {
            if (oldIndex === undefined || oldIndex === null) return;
            if (addressBackups.value[oldIndex]) {
                try {
                    addresses.value[oldIndex] = JSON.parse(addressBackups.value[oldIndex]);
                } catch (e) {
                    // ignore parse errors
                }
                delete addressBackups.value[oldIndex];
            }
            editingAddress.value = { ...editingAddress.value, [oldIndex]: false };
        };

        const saveAddress = (index) => {
            // If this address is being set as default, ensure all others are not default
            if (addresses.value[index].is_default) {
                addresses.value.forEach((addr, i) => {
                    if (i !== index) {
                        addr.is_default = false;
                    }
                });
            }

            // Set saving state
            savingAddress.value[index] = true;
            
            // Determine if creating or updating
            const address = addresses.value[index];
            const isCreating = isNewAddress(index) || !address.xid;
            
            // Prepare data for API
            const addressData = {
                user_id: props.userId,
                address_line_1: address.address_line_1,
                address_line_2: address.address_line_2,
                neighborhood: address.neighborhood,
                postal_code: address.postal_code || address.zip_code, // Handle both field names
                city: address.city,
                state: address.state || address.state_name, // Handle both field names
                country_code: address.country_code,
                country_name: address.country_name,
                reference: address.reference,
                latitude: address.latitude,
                longitude: address.longitude,
                address_type: address.address_type,
                contact_name: address.contact_name,
                contact_phone: address.contact_phone,
                notes: address.notes,
                is_default: address.is_default,
                status: address.status || true,
            };
            
            // Choose appropriate URL and method
            const url = isCreating ? 'addresses' : `addresses/${address.xid}`;
            const method = isCreating ? 'post' : 'put';

            // Call the API
            addEditRequestAdmin({
                url,
                data: addressData,
                method,
                successMessage: isCreating ? 'Address created successfully' : 'Address updated successfully',
                success: async (res) => {
                    console.log('Address save success:', res); // Debug log
                    // The response IS the address data, not res.data
                    const savedAddress = res;
                    console.log('Saved address data:', savedAddress); // Debug log
                    
                    // If zip_code is an object, extract the code and related data
                    let zipCodeValue = savedAddress.zip_code;
                    let city = savedAddress.city || addresses.value[index].city || '';
                    let stateName = savedAddress.state_name || addresses.value[index].state_name || '';
                    let countryCode = savedAddress.country_code || addresses.value[index].country_code || 'MX';
                    let countryName = savedAddress.country_name || addresses.value[index].country_name || '';
                    
                    if (savedAddress.zip_code && typeof savedAddress.zip_code === 'object') {
                        zipCodeValue = savedAddress.zip_code.code || savedAddress.zip_code;
                        if (savedAddress.zip_code.city && !city) city = savedAddress.zip_code.city;
                        if (savedAddress.zip_code.state && savedAddress.zip_code.state.name && !stateName) {
                            stateName = savedAddress.zip_code.state.name;
                        }
                        if (savedAddress.zip_code.state && savedAddress.zip_code.state.country) {
                            if (!countryCode || countryCode === 'MX') {
                                countryCode = savedAddress.zip_code.state.country.code || countryCode;
                            }
                            if (!countryName) {
                                countryName = savedAddress.zip_code.state.country.name || '';
                            }
                        }
                    }
                    
                    // Update with returned data (including generated IDs)
                    addresses.value[index] = {
                        ...savedAddress,
                        // Ensure both field name variations are available
                        zip_code: savedAddress.postal_code || savedAddress.zip_code || zipCodeValue,
                        postal_code: savedAddress.postal_code || savedAddress.zip_code || zipCodeValue,
                        city: city,
                        state_name: savedAddress.state || savedAddress.state_name || stateName,
                        state: savedAddress.state || savedAddress.state_name || stateName,
                        country_code: countryCode,
                        country_name: countryName,
                    };
                    
                    console.log('Updated address at index', index, ':', addresses.value[index]); // Debug log
                    
                    // If this was a new address, remove it from the new indexes list
                    if (isNewAddress(index)) {
                        newAddressIndexes.value = newAddressIndexes.value.filter(i => i !== index);
                    }

                    // Exit edit mode — wait a tick for reactivity to flush
                    await nextTick();
                    editingAddress.value = { ...editingAddress.value, [index]: false };
                    console.log('Exited edit mode for index:', index); // Debug log
                    
                    if (isAddingNewAddress.value && index === addresses.value.length - 1) {
                        isAddingNewAddress.value = false;
                        console.log('Stopped adding new address mode'); // Debug log
                    }
                    
                    // Clear backup
                    delete addressBackups.value[index];
                    
                    console.log('Emitting address changes:', addresses.value); // Debug log
                    emitChange();
                    console.log('Address save process completed'); // Debug log
                },
                error: () => {
                    message.error('Failed to save address. Please try again.');
                },
                finally: () => {
                    console.log('finally called');
                    savingAddress.value[index] = false;
                }
            });
        };

        const cancelEditing = (index) => {
            // If we're canceling a new address that hasn't been saved yet, remove it
            if (isNewAddress(index)) {
                // Remove the address from the array
                addresses.value.splice(index, 1);
                newAddressIndexes.value = newAddressIndexes.value.filter(i => i !== index);
                
                // Update indexes for other new addresses
                const updatedNewIndexes = [];
                newAddressIndexes.value.forEach(oldIndex => {
                    if (oldIndex < index) {
                        updatedNewIndexes.push(oldIndex);
                    } else if (oldIndex > index) {
                        updatedNewIndexes.push(oldIndex - 1);
                    }
                });
                newAddressIndexes.value = updatedNewIndexes;
                
                if (isAddingNewAddress.value && index === addresses.value.length) {
                    isAddingNewAddress.value = false;
                }
            } else if (addressBackups.value[index]) {
                // Restore from backup
                addresses.value[index] = JSON.parse(addressBackups.value[index]);
            }

            // Exit edit mode
            editingAddress.value = { ...editingAddress.value, [index]: false };
            
            // Clear backup
            delete addressBackups.value[index];
            
            emitChange();
        };

        const showDeleteConfirm = (index) => {
            const addressType = addresses.value[index].address_type || 'address';
            const isDefault = addresses.value[index].is_default;
            
            let warningMessage = `Are you sure you want to delete this ${addressType} address?`;
            if (isDefault) {
                warningMessage += ` This is currently set as the default address.`;
            }

            Modal.confirm({
                title: 'Delete Address',
                icon: createVNode(ExclamationCircleOutlined),
                content: warningMessage,
                okText: 'Delete',
                okType: 'danger',
                cancelText: 'Cancel',
                centered: true,
                onOk() {
                    removeAddress(index);
                },
            });
        };

        const removeAddress = (index) => {
            if (addresses.value.length > 1) {
                const address = addresses.value[index];
                
                // If it's a new address that hasn't been saved to the database yet
                if (isNewAddress(index)) {
                    // Just remove it from the local array
                    processAddressRemoval(index);
                } 
                // If it has an ID, we need to call the API to delete it
                else if (address.id || address.xid) {
                    savingAddress.value[index] = true;
                    
                    axiosAdmin
                        .delete(`addresses/${address.xid}`)
                        .then(() => {
                            message.success('Address deleted successfully');
                            processAddressRemoval(index);
                        })
                        .catch(() => {
                            message.error('Failed to delete address. Please try again.');
                        })
                        .finally(() => {
                            savingAddress.value[index] = false;
                        });
                }
            }
        };
        
        // Helper function to process address removal from the array
        const processAddressRemoval = (index) => {
            // If removing the default address, make the first remaining address default
            const isDefault = addresses.value[index].is_default;
            
            // Remove from new addresses if needed
            if (isNewAddress(index)) {
                newAddressIndexes.value = newAddressIndexes.value.filter(i => i !== index);
            }
            
            // Remove from addresses array
            addresses.value.splice(index, 1);
            
            // Update new address indexes
            const updatedNewIndexes = [];
            newAddressIndexes.value.forEach(oldIndex => {
                if (oldIndex < index) {
                    updatedNewIndexes.push(oldIndex);
                } else if (oldIndex > index) {
                    updatedNewIndexes.push(oldIndex - 1);
                }
            });
            newAddressIndexes.value = updatedNewIndexes;
            
            if (isDefault && addresses.value.length > 0) {
                addresses.value[0].is_default = true;
            }
            
            // Update editing indexes after deletion
            const newEditingAddress = {};
            Object.keys(editingAddress.value).forEach(key => {
                const keyIndex = parseInt(key);
                if (keyIndex < index) {
                    newEditingAddress[keyIndex] = editingAddress.value[keyIndex];
                } else if (keyIndex > index) {
                    newEditingAddress[keyIndex - 1] = editingAddress.value[keyIndex];
                }
            });
            editingAddress.value = newEditingAddress;
            
            emitChange();
        };

        const setDefaultAddress = (index, checked) => {
            const address = addresses.value[index];

            // Only allow setting default when toggling to true and address is active
            if (!checked) {
                // Prevent un-setting default via the switch. Revert UI to reflect model.
                // Re-emit to ensure parent/state is consistent.
                emitChange();
                return;
            }

            if (!address.status) {
                message.error($t ? $t('address.default_requires_active') : 'Default address must be active.');
                // Revert UI to previous state
                emitChange();
                return;
            }

            if (!address.xid) {
                // If address doesn't have xid, just update locally
                addresses.value.forEach((addr, i) => {
                    addr.is_default = i === index;
                });
                emitChange();
                return;
            }

            savingAddress.value[index] = true;
            addEditRequestAdmin({
                url: `addresses/set-default/${address.xid}`,
                method: 'post',
                successMessage: 'Default address set successfully',
                success: () => {
                    addresses.value.forEach((addr, i) => {
                        addr.is_default = i === index;
                    });
                    emitChange();
                },
                error: () => {
                    message.error('Failed to set default address. Please try again.');
                },
                finally: () => {
                    savingAddress.value[index] = false;
                }
            });
        };

        const onAddressUpdate = (index, updatedAddress) => {
            addresses.value[index] = { ...addresses.value[index], ...updatedAddress };
            emitChange();
        };

        const updateAddressField = (index, field, value) => {
            addresses.value[index][field] = value;
            emitChange();
        };

        // Helper function to get field errors for specific address index
        const getFieldError = (index, fieldName) => {
            const fieldKey = `addresses.${index}.${fieldName}`;
            return props.rules && props.rules[fieldKey] ? props.rules[fieldKey].message : null;
        };

        // Helper function to get rules for AddressForm component
        const getAddressFormRules = (index) => {
            const addressRules = {};
            const fieldsToMap = [
                'address_line_1', 'postal_code', 'country_code', 'neighborhood',
                'city', 'state', 'municipality'
            ];
            
            fieldsToMap.forEach(field => {
                const fieldKey = `addresses.${index}.${field}`;
                if (props.rules && props.rules[fieldKey]) {
                    addressRules[field] = props.rules[fieldKey];
                }
            });
            
            return addressRules;
        };

        watch(() => props.modelValue, (newVal) => {
            // Only update if the value is actually different and not from our own emit
            if (newVal && newVal.length > 0 && addresses.value.length === 0) {
                // Initial load - map the addresses properly
                addresses.value = newVal.map(addr => {
                    // If zip_code is an object, use its code property
                    let zipCodeValue = addr.zip_code;
                    let city = addr.city || '';
                    let stateName = addr.state_name || '';
                    let countryCode = addr.country_code || 'MX';
                    let countryName = addr.country_name || '';
                    
                    if (addr.zip_code && typeof addr.zip_code === 'object') {
                        zipCodeValue = addr.zip_code.code || addr.zip_code;
                        if (addr.zip_code.city && !city) city = addr.zip_code.city;
                        if (addr.zip_code.state && addr.zip_code.state.name && !stateName) {
                            stateName = addr.zip_code.state.name;
                        }
                        if (addr.zip_code.state && addr.zip_code.state.country) {
                            if (!countryCode || countryCode === 'MX') {
                                countryCode = addr.zip_code.state.country.code || countryCode;
                            }
                            if (!countryName) {
                                countryName = addr.zip_code.state.country.name || '';
                            }
                        }
                    }
                    
                    return {
                        ...addr,
                        zip_code: addr.postal_code || addr.zip_code || zipCodeValue,
                        postal_code: addr.postal_code || addr.zip_code || zipCodeValue,
                        city: city,
                        state_name: addr.state || addr.state_name || stateName,
                        state: addr.state || addr.state_name || stateName,
                        country_code: countryCode,
                        country_name: countryName,
                        status: addr.status !== undefined ? addr.status : true, // Ensure status is set
                    };
                });
                // Don't set edit mode for existing addresses
                editingAddress.value = {};
            } else if (!newVal || newVal.length === 0) {
                // Only initialize if we don't have addresses yet
                if (addresses.value.length === 0) {
                    initializeAddresses();
                }
            }
        }, { deep: true, immediate: true });

        // Don't initialize on setup - let the watch handle it
        // initializeAddresses();

        return {
            addresses,
            editingAddress,
            isAddingNewAddress,
            savingAddress,
            googleMapsApiKey,
            addAddress,
            startEditing,
            saveAddress,
            cancelEditing,
            showDeleteConfirm,
            removeAddress,
            setDefaultAddress,
            onAddressUpdate,
            updateAddressField,
            getFieldError,
            getAddressFormRules,
            getCountryName,
            isNewAddress,
            emitChange,
            anyEditing,
        };
    },
});
</script>

<style scoped>
.address-manager .address-card {
    border: 1px solid #d9d9d9;
    border-radius: 6px;
}

.address-manager .address-card:last-of-type {
    margin-bottom: 0;
}

.address-display {
    padding: 8px 0;
}

.address-display :deep(.ant-descriptions-item-label) {
    color: rgba(0, 0, 0, 0.65);
    font-weight: 500;
}

.address-display :deep(.ant-descriptions-item-content) {
    color: rgba(0, 0, 0, 0.85);
}
</style>
