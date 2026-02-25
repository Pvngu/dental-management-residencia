import PatientFilesManagement from '../views/patient-files/index.vue';
import PatientFiles from '../views/patient-files/patient-files/index.vue';
import DocumentTypes from '../views/patient-files/document-types/index.vue';

export default [
    {
        path: '/',
        component: () => import('../../common/layouts/Admin.vue'),
        children: [
            {
                path: '/admin/patient-files',
                component: PatientFilesManagement,
                children: [
                    {
                        path: '',
                        component: PatientFiles,
                        name: 'admin.patient_files.index',
                        meta: {
                            requireAuth: true,
                            menuParent: "patient_files",
                            menuKey: "patient_files",
                            permission: "patient_files_view"
                        }
                    },
                    {
                        path: 'document-types',
                        component: DocumentTypes,
                        name: 'admin.patient_files.document_types',
                        meta: {
                            requireAuth: true,
                            menuParent: "patient_files",
                            menuKey: "patient_files",
                            permission: "document_types_view"
                        }
                    },
                ]
            },
        ]
    }
]
