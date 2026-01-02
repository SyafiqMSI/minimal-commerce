<script setup lang="ts">
import { ref, onMounted, h, computed } from "vue";
import { RouterLink } from 'vue-router'
import DataTable from "@/components/ui/datatable/DataTable.vue";
import DataTableColumnHeader from "@/components/ui/datatable/DataTableColumnHeader.vue";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from "@/components/ui/dropdown-menu";
import { Button } from "@/components/ui/button";
import { MoreVertical, Trash2, Pencil, Plus, Search, Package, CheckCircle2 } from "lucide-vue-next";
import Card from "@/components/ui/card/Card.vue";
import CardContent from "@/components/ui/card/CardContent.vue";
import CardHeader from "@/components/ui/card/CardHeader.vue";
import CardTitle from "@/components/ui/card/CardTitle.vue";
import { ColumnDef } from "@tanstack/vue-table";
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Input } from "@/components/ui/input";
import { Badge } from '@/components/ui/badge';
import { toast } from "vue-sonner";
import { useProductStore } from '@/stores/product';

const productStore = useProductStore();

const searchQuery = ref("");
const selectedCategory = ref("");
const deleteDialogOpen = ref(false);
const productToDelete = ref(null);

onMounted(async () => {
    await Promise.all([
        productStore.fetchProducts(),
        productStore.fetchCategories()
    ]);
});

const handleSearch = () => {
    productStore.setFilters({ search: searchQuery.value, category_id: selectedCategory.value })
    productStore.fetchProducts()
}

const handleCategoryChange = (value) => {
    selectedCategory.value = value
    productStore.setFilters({ category_id: value === 'all' ? '' : value })
    productStore.fetchProducts()
}

const openDeleteDialog = (product) => {
    productToDelete.value = product
    deleteDialogOpen.value = true
}

const handleDelete = async () => {
    if (!productToDelete.value) return
    
    const result = await productStore.deleteProduct(productToDelete.value.id)
    
    if (result.success) {
        toast.success('Product deleted successfully')
    } else {
        toast.error(result.message)
    }
    
    deleteDialogOpen.value = false
    productToDelete.value = null
}

const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(price)
}

const columns: ColumnDef<any>[] = [
    {
        accessorKey: 'image',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: "Image" }),
        cell: ({ row }) => {
            const imageUrl = row.original.image_url
            return h('div', { class: 'w-12 h-12 rounded-lg bg-muted overflow-hidden' }, [
                imageUrl 
                    ? h('img', { 
                        src: imageUrl, 
                        alt: row.original.name,
                        class: 'w-full h-full object-cover'
                    })
                    : h('div', { class: 'flex items-center justify-center h-full' }, [
                        h(Package, { class: 'w-5 h-5 text-muted-foreground/30' })
                    ])
            ])
        }
    },
    {
        accessorKey: 'name',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: "Name" }),
        cell: ({ row }) => h('div', { class: 'font-medium' }, row.getValue('name'))
    },
    {
        accessorKey: 'category.name',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: "Category" }),
        cell: ({ row }) => {
            const categoryName = row.original.category?.name
            return categoryName ? h(Badge, { variant: 'secondary' }, () => categoryName) : null
        }
    },
    {
        accessorKey: 'price',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: "Price" }),
        cell: ({ row }) => h('div', { class: 'font-semibold text-primary' }, formatPrice(row.getValue('price')))
    },
    {
        id: "actions",
        header: () => h('div', { class: 'text-center' }, 'Actions'),
        meta: { isActionColumn: true },
        cell: ({ row }) =>
            h('div', { class: 'flex justify-center' }, [
                h(
                    DropdownMenu,
                    {},
                    {
                        default: () => [
                            h(
                                DropdownMenuTrigger,
                                { asChild: true },
                                {
                                    default: () => h(Button, { variant: "ghost", size: "icon", class: "h-8 w-8" }, { default: () => h(MoreVertical, { class: "h-4 w-4" }) }),
                                }
                            ),
                            h(
                                DropdownMenuContent,
                                { align: "end" },
                                {
                                    default: () => [
                                        h(RouterLink, { to: `/admin/products/${row.original.id}/edit` }, () => 
                                            h(DropdownMenuItem, { class: "cursor-pointer" }, { default: () => [h(Pencil, { class: "mr-2 h-4 w-4" }), "Edit"] })
                                        ),
                                        h(DropdownMenuItem, { 
                                            class: "text-destructive focus:text-destructive cursor-pointer",
                                            onClick: () => openDeleteDialog(row.original)
                                        }, { default: () => [h(Trash2, { class: "mr-2 h-4 w-4" }), "Delete"] }),
                                    ],
                                }
                            ),
                        ],
                    }
                ),
            ]),
    },
];

const totalProducts = computed(() => productStore.products.length);
const formattedTotalProducts = computed(() => new Intl.NumberFormat('id-ID').format(totalProducts.value));

</script>

<template>
        <div>
            <h1 class="text-2xl font-bold text-foreground">Products Management</h1>
            <p class="text-muted-foreground mt-2">Manage your products inventory</p>
        </div>
        

  <section class="my-6">
    <div class="relative overflow-hidden rounded-2xl border border-border bg-card shadow-sm">
      <div class="absolute inset-0 bg-gradient-to-br from-primary/10 via-transparent to-transparent"></div>
      <div class="relative flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
          <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-primary/15 text-primary">
            <Package class="h-6 w-6" />
          </span>
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-muted-foreground">Total Products</p>
            <p class="text-sm text-muted-foreground">Total available products in store</p>
          </div>
        </div>
        <div class="text-4xl font-semibold text-foreground mr-4">{{ formattedTotalProducts }}</div>
      </div>
    </div>
  </section>

  <Card>
    <CardHeader>
      <CardTitle>List of Products</CardTitle>
    </CardHeader>
    <CardContent>
      <DataTable 
        :columns="columns" 
        :data="productStore.products" 
        :isLoading="productStore.isLoading"
      >
        <template #toolbar-actions>
            <RouterLink to="/admin/products/create">
                <Button class="gap-2">
                    <Plus class="w-4 h-4" />
                    Add Product
                </Button>
            </RouterLink>
        </template>
      </DataTable>
    </CardContent>
  </Card>

  <AlertDialog v-model:open="deleteDialogOpen">
    <AlertDialogContent>
        <AlertDialogHeader>
            <AlertDialogTitle>Delete Product</AlertDialogTitle>
            <AlertDialogDescription>
                Are you sure you want to delete "{{ productToDelete?.name }}"? This action cannot be undone.
            </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
            <AlertDialogCancel>Cancel</AlertDialogCancel>
            <AlertDialogAction class="bg-destructive text-destructive-foreground hover:bg-destructive/90" @click="handleDelete">
                Delete
            </AlertDialogAction>
        </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
