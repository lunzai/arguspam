<script lang="ts">
    import * as Card from '$ui/card';
    import { Button } from '$ui/button';
    import { Pencil, Trash2 } from '@lucide/svelte';

</script>

<Card.Root class="w-full">
	<Card.Header>
		<Card.Title class="text-lg">Access Grants</Card.Title>
		<Card.Description>Asset's access grants.</Card.Description>
		<Card.Action>
			<Button
				variant="outline"
				class="transition-all duration-200 hover:bg-blue-50 hover:text-blue-500"
			>
				<Pencil class="h-4 w-4" />
				Edit
			</Button>
			<Button
				variant="outline"
				class="text-destructive border-red-200 transition-all duration-200 hover:bg-red-50 hover:text-red-500"
			>
				<Trash2 class="h-4 w-4" />
				Delete
			</Button>
		</Card.Action>
	</Card.Header>
	<Card.Content>
        Coming Soon...
    </Card.Content>
</Card.Root>