import SuperAdmin from '../layouts/SuperAdmin.vue';
import QuestionnaireTemplates from '../views/questionnaires/questionnaire-templates/index.vue';
import QuestionnaireBuilder from '../views/questionnaires/questionnaire-templates/QuestionnaireBuilder.vue';

export default [
    {
        path: '/',
        component: SuperAdmin,
        children: [
            {
                path: '/superadmin/questionnaire-templates',
                component: QuestionnaireTemplates,
                name: 'superadmin.questionnaire-templates.index',
                meta: {
                    requireAuth: true,
                    menuParent: "questionnaires",
                    menuKey: route => "questionnaire_templates",
                    permission: 'superadmin'
                }
            },
            {
                path: '/superadmin/questionnaire-templates/:xid/builder',
                component: QuestionnaireBuilder,
                name: 'superadmin.questionnaire-templates.builder',
                meta: {
                    requireAuth: true,
                    menuParent: "questionnaires",
                    menuKey: route => "questionnaire_templates",
                    permission: 'superadmin'
                }
            }
        ]
    }
];
