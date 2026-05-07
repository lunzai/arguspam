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
            <Button {...props} variant="outline" size="lg" class="ms-auto bg-white">
                <Settings2 />
                Columns
            </Button>
        {/snippet}
    </DropdownMenu.Trigger>
    <DropdownMenu.Content align="end" class="min-w-56 p-3">
        {#each table
            .getAllColumns()
            .filter((col) => col.getCanHide()) as column (column.id)}
            <DropdownMenu.CheckboxItem
                closeOnSelect={false}
                class="capitalize py-1.5"
                bind:checked={
                    () => column.getIsVisible(), (v) => column.toggleVisibility(!!v)
                }
            >
                {column.columnDef.header}
            </DropdownMenu.CheckboxItem>
        {/each}
    </DropdownMenu.Content>
</DropdownMenu.Root>