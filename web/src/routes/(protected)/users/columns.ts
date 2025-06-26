import type { ColumnDefinition } from '$components/data-table/types';
import type { User } from '$models/user';
import { shortDate, shortDateTime } from '$lib/utils/date';

export const columns: ColumnDefinition<User>[] = [
	{
		key: 'id',
		title: 'ID',
		sortable: true,
		align: 'left'
	},
	{
		key: 'name',
		title: 'Name',
		sortable: true,
		filterable: true,
	},
	{
		key: 'email',
		title: 'Email',
		sortable: true,
		filterable: true,
	},
    {
		key: 'email_verified_at',
		title: 'Email Verified At',
		sortable: true,
		filterable: true,
        renderer: (value: string) => {
            return value ? 'Yes' : 'No';
        }
	},
    {
		key: 'two_factor_enabled',
		title: 'Two Factor Enabled',
		sortable: true,
		filterable: true,
        renderer: (value: string) => {
            return value ? 'Yes' : 'No';
        }
	},
    {
		key: 'two_factor_confirmed_at',
		title: 'Two Factor Confirmed At',
		sortable: true,
		filterable: true,
        visible: true,
        renderer: (value: string) => {
            return value ? 'Yes' : 'No';
        }
	},
    {
		key: 'last_login_at',
		title: 'Last Login At',
		sortable: true,
		filterable: true,
        visible: true,
        renderer: (value: string) => {
            return value ? shortDateTime(value) : '-';
        }
	},
	{
        key: 'status',
        title: 'Status',
        sortable: true,
        filterable: true,
        align: 'center',
        renderer: (value: string) => {
            const variant = value === 'active' ? 'default' : 'secondary';
            return `<span class="badge badge-${variant}">${value}</span>`;
        }
    },
    {
		key: 'created_at',
		title: 'Created At',
		sortable: true,
		filterable: false,
        visible: true,
        renderer: (value: string) => {
            return value ? shortDateTime(value) : '-';
        }
	},
    {
		key: 'updated_at',
		title: 'Updated At',
		sortable: true,
		filterable: false,
        visible: false,
        renderer: (value: string) => {
            return value ? shortDateTime(value) : '';
        }
	},
];