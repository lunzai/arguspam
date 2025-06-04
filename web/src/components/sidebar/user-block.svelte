<script lang="ts">
	import * as Avatar from '$ui/avatar';
	import { generateAvatar, getInitials } from '$services/client/avatar.js';

	let { 
        user
    }: { 
        user: { 
            name: string; 
            email: string; 
            identifier: string;
        }
    } = $props();
</script>

<Avatar.Root class="size-8 rounded-lg">
    <Avatar.Image src={generateAvatar(user.identifier)} alt={user.name} />
    <Avatar.Fallback class="rounded-lg">{getInitials(user.name)}</Avatar.Fallback>
</Avatar.Root>
<div class="grid flex-1 text-left text-sm leading-tight">
    <span class="truncate font-medium">{user.name}</span>
    <span class="truncate text-xs">{user.email}</span>
</div>