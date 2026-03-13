<script lang="ts">
    import type { Table } from "@tanstack/table-core";
    import * as DropdownMenu from "$ui/dropdown-menu";
    import { Button } from "$ui/button";
    import { Settings2 } from "@lucide/svelte";

    type ColumnSelectorProps = {
        table: Table<any>;
    };

    let { table }: ColumnSelectorProps = $props();
</script>

<DropdownMenu.Root>
    <DropdownMenu.Trigger>
        {#snippet child({ props })}
            <Button {...props} variant="outline" class="ms-auto">
                <Settings2 class="h-4 w-4" />
                Columns
            </Button>
        {/snippet}
    </DropdownMenu.Trigger>
    <DropdownMenu.Content align="end" class="min-w-56">
        {#each table
            .getAllColumns()
            .filter((col) => col.getCanHide()) as column (column.id)}
            <DropdownMenu.CheckboxItem
            closeOnSelect={false}
            class="capitalize"
            bind:checked={
                () => column.getIsVisible(), (v) => column.toggleVisibility(!!v)
            }
            >
            {column.columnDef.header}
            </DropdownMenu.CheckboxItem>
        {/each}
    </DropdownMenu.Content>
</DropdownMenu.Root>