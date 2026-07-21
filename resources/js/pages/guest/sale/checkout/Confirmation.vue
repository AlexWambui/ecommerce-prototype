<template>
  <GuestLayout>
    <div class="confirmation-container">
      <div class="success-icon">✓</div>
      
      <h1>Thank You for Your Order!</h1>
      <p>Your order has been placed successfully.</p>
      
      <div class="order-info">
        <p><strong>Order Number:</strong> {{ order.order_number }}</p>
        <p><strong>Order Date:</strong> {{ formatDate(order.created_at) }}</p>
      </div>
      
      <div class="order-details">
        <h3>Order Summary</h3>
        
        <div v-for="item in order.items" :key="item.id" class="order-item">
          <div class="item-info">
            <strong>{{ item.product_name }}</strong>
            <br>
            <small>SKU: {{ item.product_sku }}</small>
            <div v-if="item.product_attributes" class="item-attributes">
              <span v-for="(value, key) in item.product_attributes" :key="key">
                {{ key }}: {{ value }}
              </span>
            </div>
          </div>
          <div class="item-total">
            {{ item.quantity }} × ${{ formatPrice(item.unit_price) }}
            <br>
            <strong>${{ formatPrice(item.total) }}</strong>
          </div>
        </div>
        
        <div class="totals">
          <div class="total-row">Subtotal: ${{ formatPrice(order.subtotal) }}</div>
          <div class="total-row">Tax: ${{ formatPrice(order.tax) }}</div>
          <div class="total-row">Shipping: ${{ formatPrice(order.shipping_cost) }}</div>
          <div v-if="order.discount > 0" class="total-row discount">
            Discount: -${{ formatPrice(order.discount) }}
          </div>
          <div class="total-row grand-total">
            Total: ${{ formatPrice(order.total) }}
          </div>
        </div>
      </div>
      
      <div class="shipping-info">
        <h3>Shipping Information</h3>
        <p>
          {{ order.customer_first_name }} {{ order.customer_last_name }}<br>
          {{ order.shipping_address_line1 }}<br>
          <span v-if="order.shipping_address_line2">{{ order.shipping_address_line2 }}<br></span>
          {{ order.shipping_city }},
          <span v-if="order.shipping_state">{{ order.shipping_state }}, </span>
          {{ order.shipping_postal_code }}<br>
          {{ order.shipping_country }}
        </p>
        <p>We'll send order updates to <strong>{{ order.customer_email }}</strong></p>
      </div>
      
      <div class="actions">
        <Link :href="route('orders.index')" class="btn btn-primary">
          View My Orders
        </Link>
        <Link :href="route('shop.index')" class="btn btn-secondary">
          Continue Shopping
        </Link>
      </div>
    </div>
  </GuestLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'

defineProps({
  order: {
    type: Object,
    required: true
  }
})

const formatPrice = (price) => {
  return Number(price).toFixed(2)
}

const formatDate = (date) => {
  return new Date(date).toLocaleString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>

<style scoped>
.confirmation-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 40px 20px;
  text-align: center;
}

.success-icon {
  font-size: 80px;
  color: #4CAF50;
}

.order-info {
  margin: 20px 0;
}

.order-details {
  text-align: left;
  background: #f5f5f5;
  padding: 30px;
  border-radius: 8px;
  margin: 30px 0;
}

.order-item {
  display: flex;
  justify-content: space-between;
  padding: 15px 0;
  border-bottom: 1px solid #ddd;
}

.order-item:last-child {
  border-bottom: none;
}

.item-attributes {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  margin-top: 5px;
}

.item-attributes span {
  background: white;
  padding: 2px 10px;
  border-radius: 12px;
  font-size: 12px;
}

.totals {
  margin-top: 20px;
  padding-top: 20px;
  border-top: 2px solid #ddd;
}

.total-row {
  display: flex;
  justify-content: space-between;
  padding: 5px 0;
}

.grand-total {
  font-weight: bold;
  font-size: 20px;
  margin-top: 10px;
  padding-top: 10px;
  border-top: 2px solid #333;
}

.shipping-info {
  text-align: left;
  background: white;
  padding: 20px;
  border-radius: 8px;
  margin: 20px 0;
  border: 1px solid #ddd;
}

.actions {
  display: flex;
  gap: 15px;
  justify-content: center;
  flex-wrap: wrap;
  margin-top: 30px;
}

.btn {
  padding: 12px 24px;
  color: white;
  text-decoration: none;
  border-radius: 4px;
  display: inline-block;
  transition: background 0.2s;
}

.btn-primary {
  background: #4CAF50;
}

.btn-primary:hover {
  background: #45a049;
}

.btn-secondary {
  background: #666;
}

.btn-secondary:hover {
  background: #555;
}
</style>