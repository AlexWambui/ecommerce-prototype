import { ref, reactive, computed, type Ref, type UnwrapRef } from 'vue';
import { router } from '@inertiajs/vue3';

// ============ TYPES ============

export interface CartItem {
    id: number;
    product_id: number;
    product_name: string;
    product_sku: string;
    unit_price: number;
    quantity: number;
    attributes: Record<string, any> | null;
    subtotal: number;
    total: number;
    product?: {
        id: number;
        name: string;
        slug: string;
        image: string | null;
        stock: number;
    } | null;
}

export interface CartData {
    id: number | null;
    items: CartItem[];
    subtotal: number;
    tax: number;
    shipping: number;
    discount: number;
    total: number;
    items_count: number;
    coupon_code: string | null;
}

export interface ApiResponse<T = any> {
    success: boolean;
    message?: string;
    data?: T;
    errors?: Record<string, string[]>;
}

// ============ COMPOSABLE ============

export function useCart() {
    const loading = ref(false);
    
    // Reactive cart state
    const cart = reactive<CartData>({
        id: null,
        items: [],
        subtotal: 0,
        tax: 0,
        shipping: 0,
        discount: 0,
        total: 0,
        items_count: 0,
        coupon_code: null,
    });

    // ============ COMPUTED ============
    
    const cartTotal = computed(() => cart.total);
    const cartItemsCount = computed(() => cart.items_count);
    const isEmpty = computed(() => cart.items.length === 0);
    const itemCount = computed(() => cart.items.reduce((sum, item) => sum + item.quantity, 0));

    // ============ API METHODS ============

    /**
     * Fetch the current cart from the server
     */
    const fetchCart = async (): Promise<void> => {
        loading.value = true;
        
        try {
            const response = await fetch('/cart/data');
            const data: ApiResponse<CartData> = await response.json();
            
            if (data.success && data.data) {
                Object.assign(cart, data.data);
            } else {
                console.error('Failed to fetch cart:', data.message);
            }
        } catch (error) {
            console.error('Failed to fetch cart:', error);
        } finally {
            loading.value = false;
        }
    };

    /**
     * Add an item to the cart
     */
    const addItem = async (
        productId: number, 
        quantity: number = 1, 
        attributes: Record<string, any> = {}
    ): Promise<boolean> => {
        loading.value = true;
        
        try {
            const csrfToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content;
            
            const response = await fetch('/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                },
                body: JSON.stringify({ 
                    product_id: productId, 
                    quantity, 
                    attributes 
                })
            });
            
            const data: ApiResponse<CartData> = await response.json();
            
            if (data.success && data.data) {
                Object.assign(cart, data.data);
                return true;
            } else {
                return false;
            }
        } catch (error) {
            console.error('Error adding item:', error);
            return false;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Update item quantity
     */
    const updateItem = async (itemId: number, quantity: number): Promise<boolean> => {
        loading.value = true;
        
        try {
            const csrfToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content;
            
            const response = await fetch(`/cart/item/${itemId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                },
                body: JSON.stringify({ quantity })
            });
            
            const data: ApiResponse<CartData> = await response.json();
            
            if (data.success && data.data) {
                Object.assign(cart, data.data);
                return true;
            } else {
                return false;
            }
        } catch (error) {
            console.error('Error updating item:', error);
            return false;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Remove an item from the cart
     */
    const removeItem = async (itemId: number): Promise<boolean> => {
        loading.value = true;
        
        try {
            const csrfToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content;
            
            const response = await fetch(`/cart/item/${itemId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken || '',
                }
            });
            
            const data: ApiResponse<CartData> = await response.json();
            
            if (data.success && data.data) {
                Object.assign(cart, data.data);
                return true;
            } else {
                return false;
            }
        } catch (error) {
            console.error('Error removing item:', error);
            return false;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Clear the entire cart
     */
    const clearCart = async (): Promise<boolean> => {
        loading.value = true;
        
        try {
            const csrfToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content;
            
            const response = await fetch('/cart/clear', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken || '',
                }
            });
            
            const data: ApiResponse<CartData> = await response.json();
            
            if (data.success && data.data) {
                Object.assign(cart, data.data);
                return true;
            } else {
                return false;
            }
        } catch (error) {
            console.error('Error clearing cart:', error);
            return false;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Get the total number of items in cart
     */
    const getCount = (): number => cart.items_count;

    /**
     * Get the cart total
     */
    const getTotal = (): number => cart.total;

    /**
     * Check if a product is already in the cart
     */
    const hasProduct = (productId: number): boolean => {
        return cart.items.some(item => item.product_id === productId);
    };

    /**
     * Get the quantity of a specific product in cart
     */
    const getProductQuantity = (productId: number): number => {
        const item = cart.items.find(item => item.product_id === productId);
        return item?.quantity || 0;
    };

    return {
        // State
        cart,
        loading,
        
        // Computed
        cartTotal,
        cartItemsCount,
        isEmpty,
        itemCount,
        
        // Methods
        fetchCart,
        addItem,
        updateItem,
        removeItem,
        clearCart,
        getCount,
        getTotal,
        hasProduct,
        getProductQuantity,
    };
}