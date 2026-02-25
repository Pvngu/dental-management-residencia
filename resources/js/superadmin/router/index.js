import dashboard from "./dashboard";
import settings from "./settings";
import company from "./company";
import paymentTranscation from "./paymentTranscation";
import users from "./users";
import sellers from "./sellers";
import subscriptionPlan from "./subscriptionPlan";
import emailQueries from "./emailQueries";
import questionnaires from "./questionnaires";

export default [
    ...dashboard,
    ...settings,
    ...company,
    ...paymentTranscation,
    ...users,
    ...sellers,
    ...subscriptionPlan,
    ...emailQueries,
    ...questionnaires,
];
