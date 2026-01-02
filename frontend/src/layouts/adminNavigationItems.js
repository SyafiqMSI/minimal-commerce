import {
  LayoutDashboard,
  ShoppingBag,
  PlusCircle,
  Package,
  ClipboardList,
  FolderOpen,
  Tags
} from "lucide-vue-next";

export function createAdminNavigationItems(basePath) {
  return [
    {
      id: "dashboard",
      label: "Dashboard",
      icon: LayoutDashboard,
      route: `${basePath}`,
    },
    {
      id: "orders",
      label: "Orders",
      icon: ClipboardList,
      route: `${basePath}/orders`,
    },
    {
      id: "categories",
      label: "Categories",
      icon: Tags,
      children: [
        {
          id: "all-categories",
          label: "All Categories",
          icon: FolderOpen,
          route: `${basePath}/categories`,
        },
        {
          id: "create-category",
          label: "Add Category",
          icon: PlusCircle,
          route: `${basePath}/categories/create`,
        },
      ],
    },
    {
      id: "products",
      label: "Products",
      icon: ShoppingBag,
      children: [
        {
          id: "all-products",
          label: "All Products",
          icon: Package,
          route: `${basePath}/products`,
        },
        {
          id: "create-product",
          label: "Add Product",
          icon: PlusCircle,
          route: `${basePath}/products/create`,
        },
      ],
    },
  ];
}
