<script lang="ts">
    import { Badge } from '$ui/badge';
    import { cn } from '$lib/utils';

    let { 
        dbms,
        class: className = '',
        captialize = true,
    }: { 
        dbms: string,
        class?: string,
        captialize?: boolean
    } = $props();

    let baseClass: string[] = [];
    if (captialize) {
        baseClass.push('uppercase');
    }

    switch (dbms.toLowerCase()) {
        case 'mysql':
            baseClass.push('bg-blue-100', 'text-blue-500');
            break;
        case 'postgresql':
            baseClass.push('bg-green-100', 'text-green-500');
            break;
        case 'sqlserver':
            baseClass.push('bg-red-100', 'text-red-500');
            break;
        case 'oracle':
            baseClass.push('bg-purple-100', 'text-purple-500');
            break;
        case 'mongodb':
            baseClass.push('bg-yellow-100', 'text-yellow-500');
            break;
        case 'redis':
            baseClass.push('bg-orange-100', 'text-orange-500');
            break;
        case 'mariadb':
            baseClass.push('bg-pink-100', 'text-pink-500');
            break;
        default:
            baseClass.push('bg-gray-100', 'text-gray-500');
    }
</script>

<Badge 
    class={cn('font-black', baseClass.join(' '), className)}
>{dbms}</Badge>