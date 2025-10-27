export interface Dashboard {
	user_count: number;
	user_group_count: number;
	asset_count: number;
	pending_request_count: number;
	request_count: number;
	scheduled_session_count: number;
	active_session_count: number;
	session_count: number;

	request_status_count: LineChartSeries;
	session_status_count: LineChartSeries;
	session_flag_count: LineChartSeries;

	asset_distribution: BarChartSeries;
	request_scope_distribution: BarChartSeries;
	request_approver_risk_rating_distribution: BarChartSeries;
	session_audit_flag_distribution: BarChartSeries;
}

export type LineChartSeries = Array<LineChartSeriesData>;

export type LineChartSeriesData = {
	name: string;
	data: Array<{
		x: string;
		y: number;
	}>;
};

export type BarChartSeries = {
	data: Array<{
		x: string;
		y: number;
	}>;
};
