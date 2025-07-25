import type { BaseModel } from '$models/base-model.js';

export interface Asset extends BaseModel {
	org_id: number;
	name: string;
	description: string;
	status: 'active' | 'inactive';
	host: string;
	port: number;
	dbms: 'mysql' | 'postgresql' | 'sqlserver' | 'oracle' | 'mongodb' | 'redis' | 'mariadb';
}
