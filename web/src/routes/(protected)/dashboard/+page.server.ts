import { DashboardService } from '$lib/services/dashboard';

export const load = async ({ locals, depends }) => {
	depends('dashboard:data');
	const { authToken, currentOrgId } = locals;
	const dashboardService = new DashboardService(authToken as string, Number(currentOrgId));
	const dashboard = await dashboardService.getDashboard();
	return {
		dashboard: dashboard.data,
		title: 'Dashboard'
	};
};
