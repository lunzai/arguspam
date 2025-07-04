/**
 * Convert a camelCase string to snake_case
 * @param str - The camelCase string to convert
 * @returns The converted snake_case string
 * @example
 * camelToSnake('helloWorld') // returns 'hello_world'
 * camelToSnake('thisIsALongVariableName') // returns 'this_is_a_long_variable_name'
 * camelToSnake('is2faEnable') // returns 'is_2fa_enable'
 * camelToSnake('isMp3Song') // returns 'is_mp3_song'
 */
export function camelToSnake(str: string): string {
	return (
		str
			// Insert underscore before numbers
			.replace(/([a-z])([0-9])/g, '$1_$2')
			// Insert underscore before uppercase letters
			.replace(/[A-Z]/g, (letter) => `_${letter.toLowerCase()}`)
	);
}

/**
 * Convert a snake_case string to camelCase
 * @param str - The snake_case string to convert
 * @returns The converted camelCase string
 * @example
 * snakeToCamel('hello_world') // returns 'helloWorld'
 * snakeToCamel('this_is_a_long_variable_name') // returns 'thisIsALongVariableName'
 * snakeToCamel('is_2fa_enable') // returns 'is2faEnable'
 * snakeToCamel('is_mp3_song') // returns 'isMp3Song'
 */
export function snakeToCamel(str: string): string {
	return str
		.toLowerCase()
		.replace(/([-_][a-z])/g, (group) => group.toUpperCase().replace('-', '').replace('_', ''));
}

/**
 * Interpolate a string with parameters
 * @param template - The template string to interpolate
 * @param params - The parameters to interpolate
 * @returns The interpolated string
 * @example
 * interpolate('Showing {from} to {to} of {total} results', { from: 1, to: 10, total: 100 }) // returns 'Showing 1 to 10 of 100 results'
 */
export function interpolate(template: string, params: Record<string, any>): string {
	return template.replace(/{(\w+)}/g, (_, key) => params[key]?.toString() ?? `{${key}}`);
}

/**
 * Capitalize the first letter of each word in a string
 * @param str - The string to capitalize
 * @returns The capitalized string
 * @example
 * capitalizeWords('hello world') // returns 'Hello World'
 * capitalizeWords('this is a long variable name') // returns 'This Is A Long Variable Name'
 * capitalizeWords('is2faEnable') // returns 'Is2faEnable'
 * capitalizeWords('isMp3Song') // returns 'IsMp3Song'
 */
export function capitalizeWords(str: string): string {
    return str.split(' ')
              .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
              .join(' ');
}
