import { nextTick } from 'vue';
import { createI18n } from 'vue-i18n';
import locales from './locales';

export function setupI18n(options = { locale: 'en', warnHtmlMessage: false }) {
	const i18n = createI18n({
		...options,
		messages: locales, // Load translations directly
	});
	setI18nLanguage(i18n, options.locale);
	return i18n;
}

export function setI18nLanguage(i18n, locale) {
	if (i18n.mode === 'legacy') {
		i18n.global.locale = locale
	} else {
		i18n.global.locale.value = locale
	}
	/**
	 * NOTE:
	 * If you need to specify the language setting for headers, such as the `fetch` API, set it here.
	 * The following is an example for axios.
	 *
	 * axios.defaults.headers.common['Accept-Language'] = locale
	 */
	document.querySelector('html').setAttribute('lang', locale)
}

// No longer needed - translations are loaded directly in setupI18n
export async function loadLocaleMessages(i18n, locale) {
	// Translations are already loaded in setupI18n
	return Promise.resolve();
}

export function setLangsLocaleMessage(res, i18n, locale) {
	// Legacy function - kept for compatibility but not used
	return nextTick();
}