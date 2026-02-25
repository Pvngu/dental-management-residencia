import axios from 'axios';
import { message } from "ant-design-vue";

var axiosAdmin = axios.create({
	baseURL: window.config.path + '/api/v1'
});

// Axios default headers
axiosAdmin.defaults.headers['Accept'] = 'application/json';
axiosAdmin.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axiosAdmin.defaults.headers.common['X-CSRF-TOKEN'] = document.head.querySelector('meta[name="csrf-token"]').content;

// Axios jwt token by default
if (localStorage.getItem('auth_token')) {
	axiosAdmin.defaults.headers.common['Authorization'] = 'Bearer ' + localStorage.getItem('auth_token');
}

// Add socket ID to all requests for broadcast filtering
axiosAdmin.interceptors.request.use(function (config) {
	if (window.Echo && window.Echo.socketId()) {
		config.headers['X-Socket-ID'] = window.Echo.socketId();
	}

	// Add clinic_id context
	const currentClinicId = localStorage.getItem('current_clinic_id');
	if (currentClinicId && currentClinicId !== 'null' && currentClinicId !== 'undefined') {
		// Always add as custom header (backend can choose to use it)
		config.headers['X-Clinic-Id'] = currentClinicId;
	}

	return config;
});

// Axios error listener
axiosAdmin.interceptors.response.use(function (response) {
	return response.data;
}, function (error) {
	if(error.request.responseType === 'blob' &&
        error.response.data instanceof Blob &&
        error.response.data.type &&
        error.response.data.type.toLowerCase().indexOf('json') != -1
	) {
		return new Promise((resolve, reject) => {
			let reader = new FileReader();
			reader.onload = () => {
				error.response.data = JSON.parse(reader.result);
				if(error.response.data.error && error.response.data.error.message) {
					message.error(error.response.data.error.message);
				}
				reject(error);
			};

			reader.onerror = () => {
				reject(error);
			};

			reader.readAsText(error.response.data);
		});
	}
	const errorCode = error.response.status;

	if (errorCode === 401) {
		// If error 401 redirect to login
		window.location.href = window.config.path + '/admin/login';
		delete window.axiosAdmin.defaults.headers.common.Authorization;
		localStorage.removeItem('auth_token');
		localStorage.setItem('auth_user', null);
		// throw new Error('Unauthorized');
	} else if (errorCode === 400) {
		var errMessage = error.response.data?.error?.message || 'Bad Request';

        if(errMessage === 'UNAUTHORIZED EXCEPTION') {
            window.location.href = window.config.path + '/admin/login';
            delete window.axiosAdmin.defaults.headers.common.Authorization;
            localStorage.removeItem('auth_token');
            localStorage.setItem('auth_user', null);
            return Promise.reject(error.response);
        }

        if (!error.response.data?.is_warning) {
            message.error(errMessage);
        }
    } else if (errorCode === 403) {
		var errMessage = error.response.data?.error?.message || 'Forbidden';
		
        if (error.response.data?.error?.error_code === 'subscription_expired') {
            import('../composable/subscriptionState').then(({ default: subscriptionState }) => {
                const { setPlanExpired } = subscriptionState();
                setPlanExpired(error.response.data.error);
            });
        } else {
            message.error(errMessage);
        }
	} else if (errorCode === 404) {
		var errMessage = error.response.data?.error?.message || 'Not Found';
		message.error(errMessage);
	}

	return Promise.reject(error.response);
});

/**
 * Set global so you don't have to import it
 */
window.axiosAdmin = axiosAdmin;
