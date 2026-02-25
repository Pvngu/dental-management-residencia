<template>
    <a-input-group compact :dropdownMatchSelectWidth="false">
        <a-select
            v-model:value="selectedCountry"
            show-search
            :filter-option="filterCountryOption"
            style="width: 30%"
            :placeholder="$t('user.country_code')"
            @change="onCountryChange"
            :disabled="disabled"
            :dropdownMatchSelectWidth="false"
            optionLabelProp="title"
        >
            <a-select-option
                v-for="country in countries"
                :key="country.code"
                :value="country.code"
                :title="country.dialCode"
            >
                <span style="font-weight: 500;">{{ country.dialCode }}</span> {{ country.name }}
            </a-select-option>
        </a-select>
        <a-input
            v-model:value="phoneNumber"
            style="width: 70%"
            :placeholder="$t('common.placeholder_default_text', [$t('user.phone')])"
            @input="onPhoneInput"
            @blur="onPhoneBlur"
            :disabled="disabled"
        />
    </a-input-group>
    <div v-if="errorMessage" style="color: #ff4d4f; font-size: 14px; line-height: 1.5715; margin-top: 4px;">
        {{ errorMessage }}
    </div>
</template>

<script>
import { defineComponent, ref, watch, onMounted, computed } from "vue";
import { parsePhoneNumber, isValidPhoneNumber, getCountries, getCountryCallingCode } from "libphonenumber-js";
import { useI18n } from "vue-i18n";

export default defineComponent({
    props: {
        value: {
            type: String,
            default: "",
        },
        countryCode: {
            type: String,
            default: "US",
        },
        disabled: {
            type: Boolean,
            default: false,
        },
        validateOnBlur: {
            type: Boolean,
            default: true,
        },
    },
    emits: ["onChange", "onCountryChange", "onUpdate"],
    setup(props, { emit }) {
        const { t } = useI18n();
        const selectedCountry = ref(props.countryCode || "US");
        const phoneNumber = ref("");
        const errorMessage = ref("");

        // Common countries with their names and dial codes
        const countries = ref([
            { code: "US", name: "United States", dialCode: "+1" },
            { code: "GB", name: "United Kingdom", dialCode: "+44" },
            { code: "CA", name: "Canada", dialCode: "+1" },
            { code: "AU", name: "Australia", dialCode: "+61" },
            { code: "DE", name: "Germany", dialCode: "+49" },
            { code: "FR", name: "France", dialCode: "+33" },
            { code: "IT", name: "Italy", dialCode: "+39" },
            { code: "ES", name: "Spain", dialCode: "+34" },
            { code: "MX", name: "Mexico", dialCode: "+52" },
            { code: "BR", name: "Brazil", dialCode: "+55" },
            { code: "AR", name: "Argentina", dialCode: "+54" },
            { code: "CL", name: "Chile", dialCode: "+56" },
            { code: "CO", name: "Colombia", dialCode: "+57" },
            { code: "PE", name: "Peru", dialCode: "+51" },
            { code: "VE", name: "Venezuela", dialCode: "+58" },
            { code: "IN", name: "India", dialCode: "+91" },
            { code: "CN", name: "China", dialCode: "+86" },
            { code: "JP", name: "Japan", dialCode: "+81" },
            { code: "KR", name: "South Korea", dialCode: "+82" },
            { code: "RU", name: "Russia", dialCode: "+7" },
            { code: "AE", name: "United Arab Emirates", dialCode: "+971" },
            { code: "SA", name: "Saudi Arabia", dialCode: "+966" },
            { code: "ZA", name: "South Africa", dialCode: "+27" },
            { code: "EG", name: "Egypt", dialCode: "+20" },
            { code: "NG", name: "Nigeria", dialCode: "+234" },
            { code: "PH", name: "Philippines", dialCode: "+63" },
            { code: "TH", name: "Thailand", dialCode: "+66" },
            { code: "MY", name: "Malaysia", dialCode: "+60" },
            { code: "SG", name: "Singapore", dialCode: "+65" },
            { code: "ID", name: "Indonesia", dialCode: "+62" },
            { code: "VN", name: "Vietnam", dialCode: "+84" },
            { code: "PK", name: "Pakistan", dialCode: "+92" },
            { code: "BD", name: "Bangladesh", dialCode: "+880" },
            { code: "TR", name: "Turkey", dialCode: "+90" },
            { code: "PL", name: "Poland", dialCode: "+48" },
            { code: "NL", name: "Netherlands", dialCode: "+31" },
            { code: "BE", name: "Belgium", dialCode: "+32" },
            { code: "SE", name: "Sweden", dialCode: "+46" },
            { code: "NO", name: "Norway", dialCode: "+47" },
            { code: "DK", name: "Denmark", dialCode: "+45" },
            { code: "FI", name: "Finland", dialCode: "+358" },
            { code: "CH", name: "Switzerland", dialCode: "+41" },
            { code: "AT", name: "Austria", dialCode: "+43" },
            { code: "GR", name: "Greece", dialCode: "+30" },
            { code: "PT", name: "Portugal", dialCode: "+351" },
            { code: "IE", name: "Ireland", dialCode: "+353" },
            { code: "NZ", name: "New Zealand", dialCode: "+64" },
            { code: "IL", name: "Israel", dialCode: "+972" },
        ]);

        const currentDialCode = computed(() => {
            const country = countries.value.find(c => c.code === selectedCountry.value);
            return country ? country.dialCode : "+1";
        });

        const filterCountryOption = (input, option) => {
            const countryData = countries.value.find(c => c.code === option.value);
            if (!countryData) return false;
            
            const searchStr = input.toLowerCase();
            return (
                countryData.name.toLowerCase().includes(searchStr) ||
                countryData.dialCode.includes(searchStr) ||
                countryData.code.toLowerCase().includes(searchStr)
            );
        };

        const onCountryChange = (value) => {
            selectedCountry.value = value;
            errorMessage.value = "";
            emit("onCountryChange", value);
            emitFullPhoneNumber();
            emitUpdate();
        };

        const onPhoneInput = () => {
            errorMessage.value = "";
            emitFullPhoneNumber();
            emitUpdate();
        };

        const onPhoneBlur = () => {
            if (props.validateOnBlur && phoneNumber.value) {
                validatePhoneNumber();
            }
        };

        const validatePhoneNumber = () => {
            if (!phoneNumber.value) {
                errorMessage.value = "";
                return true;
            }

            try {
                const fullNumber = currentDialCode.value + phoneNumber.value.replace(/^0+/, "");
                const isValid = isValidPhoneNumber(fullNumber, selectedCountry.value);
                
                if (!isValid) {
                    errorMessage.value = t("common.invalid_phone_number");
                    return false;
                }
                
                errorMessage.value = "";
                return true;
            } catch (error) {
                errorMessage.value = t("common.invalid_phone_number");
                return false;
            }
        };

        const emitFullPhoneNumber = () => {
            if (!phoneNumber.value) {
                emit("onChange", "");
                return;
            }

            try {
                const cleanNumber = phoneNumber.value.replace(/^0+/, "");
                const fullNumber = currentDialCode.value + cleanNumber;
                emit("onChange", fullNumber);
            } catch (error) {
                emit("onChange", phoneNumber.value);
            }
        };

        const emitUpdate = () => {
            emit("onUpdate", {
                phone: phoneNumber.value ? currentDialCode.value + phoneNumber.value.replace(/^0+/, "") : "",
                country_code: selectedCountry.value
            });
        };

        const parseInitialValue = (value) => {
            if (!value) return;

            try {
                const parsed = parsePhoneNumber(value);
                if (parsed) {
                    selectedCountry.value = parsed.country || selectedCountry.value;
                    phoneNumber.value = parsed.nationalNumber;
                } else {
                    phoneNumber.value = value;
                }
            } catch (error) {
                phoneNumber.value = value;
            }
        };

        watch(() => props.value, (newValue) => {
            if (newValue && newValue !== currentDialCode.value + phoneNumber.value) {
                parseInitialValue(newValue);
            } else if (!newValue) {
                phoneNumber.value = "";
            }
        });

        watch(() => props.countryCode, (newValue) => {
            if (newValue && newValue !== selectedCountry.value) {
                selectedCountry.value = newValue;
            }
        });

        onMounted(() => {
            if (props.value) {
                parseInitialValue(props.value);
            }
        });

        return {
            selectedCountry,
            phoneNumber,
            countries,
            errorMessage,
            currentDialCode,
            filterCountryOption,
            onCountryChange,
            onPhoneInput,
            onPhoneBlur,
            validatePhoneNumber,
            emitUpdate,
        };
    },
});
</script>

<style scoped>
/* Phone select styles */
</style>
