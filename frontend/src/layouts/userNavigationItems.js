import {
  LayoutDashboard,
  Package,
  ShoppingCart,
  Heart,
  ShoppingBag,
} from "lucide-vue-next";
import { useCartStore } from '@/stores/cart';
import { useWishlistStore } from '@/stores/wishlist';
import { computed } from 'vue';

export function createUserNavigationItems(basePath) {
  const cartStore = useCartStore();
  const wishlistStore = useWishlistStore();

  return [
    {
      id: "dashboard",
      label: "Dashboard",
      icon: LayoutDashboard,
      route: `${basePath}`,
    },
    {
      id: "products",
      label: "Products",
      icon: Package,
      route: `${basePath}/products`,
    },
    {
      id: "cart",
      label: "Cart",
      icon: ShoppingCart,
      route: `${basePath}/cart`,
      badge: computed(() => cartStore.totalItems > 0 ? cartStore.totalItems : null),
    },
    {
      id: "wishlist",
      label: "Wishlist",
      icon: Heart,
      route: `${basePath}/wishlist`,
      badge: computed(() => wishlistStore.items.length > 0 ? wishlistStore.items.length : null),
    },
    {
      id: "orders",
      label: "Orders",
      icon: ShoppingBag,
      route: `${basePath}/orders`,
    },
  ];
}
