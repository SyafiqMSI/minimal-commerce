import {
  LayoutDashboard,
  ShoppingCart,
  Heart,
  ShoppingBag,
} from "lucide-vue-next";

export function createUserNavigationItems(basePath) {
  return [
    {
      id: "dashboard",
      label: "Dashboard",
      icon: LayoutDashboard,
      route: `${basePath}`,
    },
    {
      id: "cart",
      label: "Cart",
      icon: ShoppingCart,
      route: `${basePath}/cart`,
    },
    {
      id: "wishlist",
      label: "Wishlist",
      icon: Heart,
      route: `${basePath}/wishlist`,
    },
    {
      id: "orders",
      label: "Orders",
      icon: ShoppingBag,
      route: `${basePath}/orders`,
    },
  ];
}
