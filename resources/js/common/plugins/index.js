import moment from "moment";
import './axiosBase';
import './axiosAdmin';
import './axiosFront';
import './echo';

moment.suppressDeprecationWarnings = true;
window.moment = moment;