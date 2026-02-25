<template>
    <div>
        <!-- Google Places Autocomplete Search -->
        <a-row :gutter="16">
            <a-col :xs="24" :sm="24" :md="24" :lg="24">
                <a-form-item
                    :label="$t('address.search_address')"
                    name="address_search"
                >
                    <a-input
                        ref="autocompleteInput"
                        v-model:value="searchQuery"
                        :placeholder="$t('address.google_search_placeholder')"
                        @input="onSearchInput"
                    >
                        <template #prefix>
                            <SearchOutlined />
                        </template>
                    </a-input>
                    <div class="mt-2" style="color: #666; font-size: 12px;">
                        {{ $t('address.google_search_hint') }}
                    </div>
                </a-form-item>
            </a-col>
        </a-row>

        <!-- Manual Address Fields (All Editable) -->
        <a-row :gutter="16">
            <a-col :xs="24" :sm="24" :md="18" :lg="18">
                <a-form-item
                    :label="$t('address.street_address')"
                    name="address_line_1"
                    :help="rules?.address_line_1 ? rules.address_line_1.message : (rules && rules['addresses.0.address_line_1'] ? rules['addresses.0.address_line_1'][0] : null)"
                    :validateStatus="rules?.address_line_1 ? 'error' : (rules && rules['addresses.0.address_line_1'] ? 'error' : null)"
                    class="required"
                >
                    <a-input
                        v-model:value="localAddress.address_line_1"
                        :placeholder="$t('common.placeholder_default_text', [$t('address.street_address')])"
                        @change="emitChange"
                    />
                </a-form-item>
            </a-col>
            <a-col :xs="24" :sm="24" :md="6" :lg="6">
                <a-form-item
                    :label="$t('address.exterior_number')"
                    name="address_line_2"
                    :help="rules?.address_line_2 ? rules.address_line_2.message : null"
                    :validateStatus="rules?.address_line_2 ? 'error' : null"
                >
                    <a-input
                        v-model:value="localAddress.address_line_2"
                        :placeholder="$t('common.placeholder_default_text', [$t('address.exterior_number')])"
                        @change="emitChange"
                    />
                </a-form-item>
            </a-col>
        </a-row>

        <a-row :gutter="16">
            <a-col :xs="24" :sm="24" :md="12" :lg="12">
                <a-form-item
                    :label="$t('address.neighborhood')"
                    name="neighborhood"
                    :help="rules?.neighborhood ? rules.neighborhood.message : null"
                    :validateStatus="rules?.neighborhood ? 'error' : null"
                >
                    <a-input
                        v-model:value="localAddress.neighborhood"
                        :placeholder="$t('common.placeholder_default_text', [$t('address.neighborhood')])"
                        @change="emitChange"
                    />
                </a-form-item>
            </a-col>
            <a-col :xs="24" :sm="24" :md="12" :lg="12">
                <a-form-item
                    :label="$t('address.zip_code')"
                    name="postal_code"
                    :help="rules?.postal_code ? rules.postal_code.message : null"
                    :validateStatus="rules?.postal_code ? 'error' : null"
                >
                    <a-input
                        v-model:value="localAddress.postal_code"
                        :placeholder="$t('common.placeholder_default_text', [$t('address.zip_code')])"
                        @change="emitChange"
                    />
                </a-form-item>
            </a-col>
        </a-row>

        <a-row :gutter="16">
            <a-col :xs="24" :sm="24" :md="8" :lg="8">
                <a-form-item
                    :label="$t('address.city')"
                    name="city"
                    :help="rules?.city ? rules.city.message : null"
                    :validateStatus="rules?.city ? 'error' : null"
                >
                    <a-input
                        v-model:value="localAddress.city"
                        :placeholder="$t('common.placeholder_default_text', [$t('address.city')])"
                        @change="emitChange"
                    />
                </a-form-item>
            </a-col>
            <a-col :xs="24" :sm="24" :md="8" :lg="8">
                <a-form-item
                    :label="$t('address.state')"
                    name="state"
                    :help="rules?.state ? rules.state.message : null"
                    :validateStatus="rules?.state ? 'error' : null"
                >
                    <a-input
                        v-model:value="localAddress.state"
                        :placeholder="$t('common.placeholder_default_text', [$t('address.state')])"
                        @change="emitChange"
                    />
                </a-form-item>
            </a-col>
            <a-col :xs="24" :sm="24" :md="8" :lg="8">
                <a-form-item
                    :label="$t('address.country')"
                    name="country_name"
                    :help="rules?.country_name ? rules.country_name.message : null"
                    :validateStatus="rules?.country_name ? 'error' : null"
                >
                    <a-input
                        v-model:value="localAddress.country_name"
                        :placeholder="$t('common.placeholder_default_text', [$t('address.country')])"
                        @change="emitChange"
                        disabled
                    />
                </a-form-item>
            </a-col>
        </a-row>
    </div>
</template>

<script>
import { defineComponent, ref, watch, onMounted, nextTick } from 'vue';
import { SearchOutlined } from '@ant-design/icons-vue';
import { loadGoogleMapsScript } from '../../../utils/googleMaps';

export default defineComponent({
    name: 'AddressForm',
    components: {
        SearchOutlined,
    },
    props: {
        modelValue: { type: Object, required: true },
        rules: { type: Object, default: () => ({}) },
        googleApiKey: { type: String, default: '' },
    },
    emits: ['update:modelValue'],
    setup(props, { emit }) {
        const localAddress = ref({ ...props.modelValue });
        const searchQuery = ref('');
        const autocompleteInput = ref(null);
        let autocomplete = null;

        watch(() => props.modelValue, (val) => { 
            console.log('AddressForm - received modelValue:', val); // Debug log
            localAddress.value = { ...val }; 
        }, { deep: true });

        const emitChange = () => { 
            emit('update:modelValue', { ...localAddress.value }); 
        };

        const onSearchInput = () => {
            // This is just for v-model binding; Google Places handles the search
        };

        const extractCountryCode = (longName) => {
            const countryCodeMap = {
                'Mexico': 'MX',
                'México': 'MX',
                'United States': 'US',
                'Canada': 'CA',
                'España': 'ES',
                'Spain': 'ES',
                'Argentina': 'AR',
                'Colombia': 'CO',
            };
            return countryCodeMap[longName] || longName.substring(0, 2).toUpperCase();
        };

        const handlePlaceSelect = () => {
            if (!autocomplete) return;

            const place = autocomplete.getPlace();
            
            if (!place.geometry || !place.address_components) {
                console.warn('No details available for selected place');
                return;
            }

            // Extract address components
            const components = {};
            place.address_components.forEach(component => {
                const types = component.types;
                if (types.includes('street_number')) {
                    components.street_number = component.long_name;
                }
                if (types.includes('route')) {
                    components.route = component.long_name;
                }
                if (types.includes('sublocality') || types.includes('sublocality_level_1')) {
                    components.neighborhood = component.long_name;
                }
                if (types.includes('locality')) {
                    components.city = component.long_name;
                }
                if (types.includes('administrative_area_level_1')) {
                    components.state = component.long_name;
                }
                if (types.includes('country')) {
                    components.country = component.long_name;
                    components.country_code = component.short_name;
                }
                if (types.includes('postal_code')) {
                    components.postal_code = component.long_name;
                }
            });

            // Build address line 1 (street + number)
            const addressParts = [];
            if (components.route) addressParts.push(components.route);
            if (components.street_number) {
                localAddress.value.address_line_2 = components.street_number;
            }
            
            localAddress.value.address_line_1 = addressParts.join(' ') || place.name || '';
            localAddress.value.neighborhood = components.neighborhood || '';
            localAddress.value.postal_code = components.postal_code || '';
            localAddress.value.city = components.city || '';
            localAddress.value.state = components.state || '';
            localAddress.value.country_name = components.country || '';
            localAddress.value.country_code = components.country_code ? components.country_code.toUpperCase() : '';

            // Capture coordinates
            if (place.geometry.location) {
                localAddress.value.latitude = place.geometry.location.lat();
                localAddress.value.longitude = place.geometry.location.lng();
            }

            // Clear search query
            searchQuery.value = place.formatted_address || '';

            emitChange();
        };

        const initializeAutocomplete = async () => {
            try {
                // Load Google Maps script
                await loadGoogleMapsScript(props.googleApiKey);

                // Wait for next tick to ensure input is rendered
                await nextTick();

                if (!autocompleteInput.value) {
                    console.error('Autocomplete input ref not found');
                    return;
                }

                const inputElement = autocompleteInput.value.$el?.querySelector('input') || autocompleteInput.value;

                // Initialize Google Places Autocomplete
                autocomplete = new google.maps.places.Autocomplete(inputElement, {
                    types: ['address'],
                    fields: ['address_components', 'geometry', 'name', 'formatted_address'],
                });

                // Add place_changed listener
                autocomplete.addListener('place_changed', handlePlaceSelect);

            } catch (error) {
                console.error('Error initializing Google Places Autocomplete:', error);
            }
        };

        onMounted(() => {
            initializeAutocomplete();
        });

        return { 
            localAddress, 
            searchQuery,
            autocompleteInput,
            emitChange,
            onSearchInput,
            rules: props.rules 
        };
    },
});
</script>

<style scoped>
.pac-container {
    z-index: 10000 !important;
}
</style>
