import { Badge } from '$ui/badge';
import type { Org } from '$models/org';
import type { ColumnDef } from '@tanstack/table-core';
import { format } from 'date-fns';

export const OrgColumnDef: ColumnDef<Org>[] = [
	{
		accessorKey: 'name',
		header: 'Name'
	},
	// {
	//   accessorKey: "description",
	//   header: "Description",
	// },
	{
		accessorKey: 'status',
		header: 'Status',
		cell: ({ getValue }) => {
			const value = getValue() as string;
			return value;
		}
	},
	{
		accessorKey: 'created_at',
		header: 'Created At',
		cell: ({ getValue }) => {
			return format(new Date(getValue() as string), 'dd/MM/yyyy HH:mm');
		}
	},
	{
		accessorKey: 'updated_at',
		header: 'Updated At',
		cell: ({ getValue }) => {
			return format(new Date(getValue() as string), 'dd/MM/yyyy HH:mm');
		}
	}
];
