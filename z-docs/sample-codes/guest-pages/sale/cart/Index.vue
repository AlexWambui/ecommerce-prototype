<template>
  <GuestLayout>
    <div class="cart-container">
      <h1>Shopping Cart</h1>
      
      <!-- Empty Cart -->
      <div v-if="cart.items.length === 0" class="empty-cart">
        <h2>Your cart is empty</h2>
        <Link :href="route('shop.index')" class="btn btn-primary">
          Continue Shopping
        </Link>
      </div>

      <!-- Cart with Items -->
      <div v-else>
        <div class="cart-items">
          <CartItem
            v-for="item in cart.items"
            :key="item.id"
            :item="item"
            @update-quantity="updateQuantity"
            @remove="removeItem"
          />
        </div>

        <CartSummary
          :cart="cart"
          @clear="clearCart"
        />
      </div>
    </div>
  </GuestLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import CartItem from '@/Components/Cart/CartItem.vue'
import CartSummary from '@/Components/Cart/CartSummary.vue'
import { useToast } from '@/Composables/useToast'

const props = defineProps({
  cart: {
    type: Object,
    required: true
  }
})

const toast = useToast()
const loading = ref(false)

const updateQuantity = async (itemId, quantity) => {
  if (quantity < 1) return
  
  loading.value = true
  
  try {
    const response = await fetch(`/cart/item/${itemId}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({ quantity })
    })
    
    const data = await response.json()
    
    if (data.success) {
      // Refresh page to update cart
      router.reload({ preserveScroll: true })
      toast.success('Cart updated')
    } else {
      toast.error(data.message || 'Error updating cart')
    }
  } catch (error) {
    toast.error('Network error. Please try again.')
  } finally {
    loading.value = false
  }
}

const removeItem = async (itemId) => {
  if (!confirm('Remove this item from your cart?')) return
  
  loading.value = true
  
  try {
    const response = await fetch(`/cart/item/${itemId}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      }
    })
    
    const data = await response.json()
    
    if (data.success) {
      router.reload({ preserveScroll: true })
      toast.success('Item removed')
    } else {
      toast.error(data.message || 'Error removing item')
    }
  } catch (error) {
    toast.error('Network error. Please try again.')
  } finally {
    loading.value = false
  }
}

const clearCart = async () => {
  if (!confirm('Clear your entire cart?')) return
  
  loading.value = true
  
  try {
    const response = await fetch('/cart/clear', {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      }
    })
    
    const data = await response.json()
    
    if (data.success) {
      router.reload()
      toast.success('Cart cleared')
    } else {
      toast.error(data.message || 'Error clearing cart')
    }
  } catch (error) {
    toast.error('Network error. Please try again.')
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.cart-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.cart-items {
  margin-bottom: 30px;
}

.empty-cart {
  text-align: center;
  padding: 50px 0;
}

.btn {
  display: inline-block;
  padding: 12px 24px;
  background: #4CAF50;
  color: white;
  text-decoration: none;
  border-radius: 4px;
  border: none;
  cursor: pointer;
}

.btn-primary {
  background: #4CAF50;
}

.btn-primary:hover {
  background: #45a049;
}
</style>