import ExpenseIndex from '../views/expense-management/expense/index.vue';
import ExpenseCategoriesIndex from '../views/expense-management/expense-categories/index.vue';

export default [
    {
        path: '/',
        component: () => import('../../common/layouts/Admin.vue'),
        children: [
            {
                path: '/admin/expenses',
                component: ExpenseIndex,
                name: 'admin.expenses.index',
                meta: {
                    requireAuth: true,
                    menuParent: "expenses",
                    menuKey: "expenses",
                    permission: "expenses_view"
                }
            },
            {
                path: '/admin/expense-categories',
                component: ExpenseCategoriesIndex,
                name: 'admin.expense_categories.index',
                meta: {
                    requireAuth: true,
                    menuParent: "expense_categories",
                    menuKey: "expense_categories",
                    permission: "expense_categories_view"
                }
            },
        ]
    }
]
