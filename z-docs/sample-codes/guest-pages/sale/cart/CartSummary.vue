<template>
  <div class="cart-summary">
    <h3>Order Summary</h3>
    
    <div class="summary-row">
      <span>Subtotal</span>
      <span>${{ formatPrice(cart.subtotal) }}</span>
    </div>
    
    <div class="summary-row">
      <span>Tax</span>
      <span>${{ formatPrice(cart.tax) }}</span>
    </div>
    
    <div class="summary-row">
      <span>Shipping</span>
      <span>${{ formatPrice(cart.shipping) }}</span>
    </div>
    
    <div v-if="cart.discount > 0" class="summary-row discount">
      <span>Discount</span>
      <span>-${{ formatPrice(cart.discount) }}</span>
    </div>
    
    <div class="summary-row total">
      <span>Total</span>
      <span>${{ formatPrice(cart.total) }}</span>
    </div>
    
    <div class="summary-actions">
      <button @click="$emit('clear')" class="btn btn-secondary">
        Clear Cart
      </button>
      
      <Link :href="route('checkout.index')" class="btn btn-primary">
        Proceed to Checkout
      </Link>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  cart: {
    type: Object,
    required: true
  }
})

defineEmits(['clear'])

const formatPrice = (price) => {
  return Number(price).toFixed(2)
}
</script>

<style scoped>
.cart-summary {
  padding: 20px;
  background: #f5f5f5;
  border-radius: 8px;
  max-width: 400px;
  margin-left: auto;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  padding: 10px 0;
  border-bottom: 1px solid #ddd;
}

.summary-row:last-child {
  border-bottom: none;
}

.summary-row.total {
  font-weight: bold;
  font-size: 20px;
  padding-top: 15px;
  border-top: 2px solid #333;
}

.summary-actions {
  margin-top: 20px;
  display: flex;
  gap: 10px;
  flex-direction: column;
}

.btn {
  display: block;
  padding: 12px 24px;
  text-align: center;
  text-decoration: none;
  border-radius: 4px;
  border: none;
  cursor: pointer;
  font-size: 16px;
  transition: background 0.2s;
}

.btn-primary {
  background: #4CAF50;
  color: white;
}

.btn-primary:hover {
  background: #45a049;
}

.btn-secondary {
  background: #666;
  color: white;
}

.btn-secondary:hover {
  background: #555;
}
</style>