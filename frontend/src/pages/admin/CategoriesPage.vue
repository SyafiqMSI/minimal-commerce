<script setup>
import { onMounted, h } from 'vue'
import { useRouter } from 'vue-router'
import { useCategoryStore } from '@/stores/category'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { toast } from 'vue-sonner'
import { Plus, Pencil, Trash2, FolderOpen, MoreVertical } from 'lucide-vue-next'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import DataTable from '@/components/ui/datatable/DataTable.vue'
import DataTableColumnHeader from '@/components/ui/datatable/DataTableColumnHeader.vue'
import { confirmModal } from '@/components/ui/confirmation-dialog'

const router = useRouter()
const categoryStore = useCategoryStore()

onMounted(async () => {
    await categoryStore.fetchCategories()
})

const handleCreate = () => {
    router.push('/admin/categories/create')
}

const handleEdit = (category) => {
    router.push(`/admin/categories/${category.id}/edit`)
}

const handleDelete = async (category) => {
    const confirmed = await confirmModal('Delete Category', `Are you sure you want to delete "${category.name}"? This action cannot be undone.`)
    if (confirmed) {
        const result = await categoryStore.deleteCategory(category.id)
        if (result.success) {
            toast.success('Category deleted successfully')
        } else {
            toast.error(result.message)
        }
    }
}

const columns = [
    {
        accessorKey: 'name',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Name' }),
        cell: ({ row }) => h('span', { class: 'font-medium' }, row.getValue('name'))
    },
    {
        accessorKey: 'slug',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Slug' }),
        cell: ({ row }) => h(Badge, { variant: 'secondary' }, () => row.getValue('slug'))
    },
    {
        accessorKey: 'description',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Description' }),
        cell: ({ row }) => h('span', { class: 'max-w-xs truncate text-muted-foreground' }, row.getValue('description') || '-')
    },
    {
        accessorKey: 'products_count',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Products' }),
        cell: ({ row }) => h('div', { class: 'text-center' }, [
            h(Badge, { variant: 'outline' }, () => row.original.products_count || 0)
        ])
    },
    {
        id: 'actions',
        header: () => h('div', { class: 'text-center' }, 'Actions'),
        meta: { isActionColumn: true },
        cell: ({ row }) => h('div', { class: 'flex justify-center' }, [
            h(DropdownMenu, {}, {
                default: () => [
                    h(DropdownMenuTrigger, { asChild: true }, {
                        default: () => h(Button, { variant: 'ghost', size: 'icon', class: 'h-8 w-8' }, {
                            default: () => h(MoreVertical, { class: 'h-4 w-4' })
                        })
                    }),
                    h(DropdownMenuContent, { align: 'end' }, {
                        default: () => [
                            h(DropdownMenuItem, { 
                                class: 'cursor-pointer',
                                onClick: () => handleEdit(row.original)
                            }, {
                                default: () => [h(Pencil, { class: 'mr-2 h-4 w-4' }), 'Edit']
                            }),
                            h(DropdownMenuSeparator),
                            h(DropdownMenuItem, { 
                                class: 'text-destructive focus:text-destructive cursor-pointer',
                                disabled: row.original.products_count > 0,
                                onClick: () => {
                                    if (row.original.products_count === 0) {
                                        handleDelete(row.original)
                                    }
                                }
                            }, {
                                default: () => [h(Trash2, { class: 'mr-2 h-4 w-4' }), 'Delete']
                            })
                        ]
                    })
                ]
            })
        ])
    }
]
</script>

<template>
    <div>
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-foreground">Categories</h1>
                <p class="text-muted-foreground mt-1">Manage product categories</p>
            </div>
        </div>

        <section class="mb-6">
            <div class="relative overflow-hidden rounded-2xl border border-border bg-card shadow-sm">
                <div class="absolute inset-0 bg-gradient-to-br from-primary/10 via-transparent to-transparent"></div>
                <div class="relative flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4">
                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-primary/15 text-primary">
                            <FolderOpen class="h-6 w-6" />
                        </span>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-muted-foreground">Total Categories</p>
                            <p class="text-sm text-muted-foreground">Total available categories</p>
                        </div>
                    </div>
                    <div class="text-4xl font-semibold text-foreground mr-4">{{ categoryStore.categories.length }}</div>
                </div>
            </div>
        </section>

        <Card class="border-border/50">
            <CardHeader>
                <div class="flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between">
                    <div>
                        <CardTitle>All Categories</CardTitle>
                        <CardDescription>{{ categoryStore.categories.length }} categories total</CardDescription>
                    </div>
                </div>
            </CardHeader>
            <CardContent>
                <DataTable
                    :columns="columns"
                    :data="categoryStore.categories"
                    :isLoading="categoryStore.isLoading"
                >
                    <template #toolbar-actions>
                        <Button @click="handleCreate" class="gap-2">
                            <Plus class="w-4 h-4" />
                            Add Category
                        </Button>
                    </template>
                </DataTable>
            </CardContent>
        </Card>

    </div>
</template>
