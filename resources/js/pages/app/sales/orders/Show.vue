<template>
  <AuthenticatedLayout>
    <div class="order-details-container">
      <div class="order-header">
        <div>
          <h1>Order #{{ order.order_number }}</h1>
          <p class="order-date">Placed on {{ formatDate(order.created_at) }}</p>
        </div>
        <div>
          <span :class="['order-status', `status-${order.status}`]">
            {{ capitalize(order.status) }}
          </span>
        </div>
      </div>
      
      <div class="order-grid">
        <!-- Order Items -->
        <div class="order-section">
          <h3>Order Items</h3>
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
          
          <div class="order-totals">
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
        
        <!-- Shipping Information -->
        <div class="order-section">
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
          <p><strong>Email:</strong> {{ order.customer_email }}</p>
          <p><strong>Shipping Method:</strong> {{ capitalize(order.shipping_method) }}</p>
          <p v-if="order.tracking_number">
            <strong>Tracking Number:</strong> {{ order.tracking_number }}
          </p>
        </div>
        
        <!-- Payment Information -->
        <div v-if="order.payment" class="order-section">
          <h3>Payment Information</h3>
          <p><strong>Method:</strong> {{ capitalize(order.payment.provider) }}</p>
          <p><strong>Transaction ID:</strong> {{ order.payment.transaction_id }}</p>
          <p><strong>Amount:</strong> ${{ formatPrice(order.payment.amount) }}</p>
          <p><strong>Status:</strong> {{ capitalize(order.payment.status) }}</p>
          <p><strong>Date:</strong> {{ formatDate(order.payment.created_at) }}</p>
        </div>
        
        <!-- Order Timeline -->
        <div class="order-section">
          <h3>Order Timeline</h3>
          <div class="timeline">
            <div v-for="status in order.statuses" :key="status.created_at" class="timeline-item">
              <div class="timeline-dot"></div>
              <div class="timeline-content">
                <strong>{{ capitalize(status.status) }}</strong>
                <p v-if="status.notes">{{ status.notes }}</p>
                <small>{{ formatDate(status.created_at) }}</small>
                <small class="timeline-user">By: {{ status.user_name }}</small>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="order-actions">
        <Link :href="route('orders.index')" class="btn btn-secondary">
          Back to Orders
        </Link>
        
        <button 
          v-if="canCancel"
          class="btn btn-danger"
          @click="cancelOrder(order.id)"
        >
          Cancel Order
        </button>
        
        <Link 
          v-if="order.tracking_number"
          :href="route('orders.track', order.id)" 
          class="btn btn-primary"
        >
          Track Order
        </Link>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { useToast } from '@/Composables/useToast'

const props = defineProps({
  order: {
    type: Object,
    required: true
  },
  can_cancel: {
    type: Boolean,
    required: true
  }
})

const toast = useToast()

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

const capitalize = (str) => {
  if (!str) return ''
  return str.charAt(0).toUpperCase() + str.slice(1)
}

const cancelOrder = async (orderId) => {
  if (!confirm('Are you sure you want to cancel this order?')) return
  
  try {
    const response = await fetch(route('orders.cancel', orderId), {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      }
    })
    
    if (response.ok) {
      router.reload()
      toast.success('Order cancelled successfully')
    } else {
      toast.error('Failed to cancel order')
    }
  } catch (error) {
    toast.error('Network error. Please try again.')
  }
}
</script>

<style scoped>
.order-details-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 10px;
  margin-bottom: 30px;
  padding-bottom: 20px;
  border-bottom: 1px solid #ddd;
}

.order-date {
  color: #666;
  margin-top: 5px;
}

.order-status {
  padding: 6px 16px;
  border-radius: 20px;
  font-size: 16px;
  font-weight: bold;
  display: inline-block;
}

.status-pending {
  background: #ffd93d;
  color: #333;
}

.status-processing {
  background: #6c5ce7;
  color: white;
}

.status-shipped {
  background: #0984e3;
  color: white;
}

.status-delivered {
  background: #00b894;
  color: white;
}

.status-cancelled {
  background: #d63031;
  color: white;
}

.status-refunded {
  background: #636e72;
  color: white;
}

.order-grid {
  display: grid;
  gap: 30px;
}

.order-section {
  background: white;
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 20px;
}

.order-section h3 {
  margin-top: 0;
  padding-bottom: 10px;
  border-bottom: 1px solid #eee;
}

.order-item {
  display: flex;
  justify-content: space-between;
  padding: 12px 0;
  border-bottom: 1px solid #f5f5f5;
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
  background: #f5f5f5;
  padding: 2px 10px;
  border-radius: 12px;
  font-size: 12px;
}

.order-totals {
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

.timeline {
  position: relative;
  padding-left: 20px;
}

.timeline::before {
  content: '';
  position: absolute;
  left: 6px;
  top: 0;
  bottom: 0;
  width: 2px;
  background: #ddd;
}

.timeline-item {
  position: relative;
  padding: 10px 0 10px 20px;
}

.timeline-dot {
  position: absolute;
  left: -14px;
  top: 14px;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #4CAF50;
  border: 2px solid white;
}

.timeline-content {
  background: #f9f9f9;
  padding: 10px 15px;
  border-radius: 6px;
}

.timeline-content p {
  margin: 5px 0;
}

.timeline-user {
  display: block;
  color: #666;
  margin-top: 5px;
}

.order-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  margin-top: 30px;
  padding-top: 20px;
  border-top: 1px solid #ddd;
}

.btn {
  padding: 10px 20px;
  color: white;
  text-decoration: none;
  border-radius: 4px;
  border: none;
  cursor: pointer;
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

.btn-danger {
  background: #ff4444;
}

.btn-danger:hover {
  background: #cc0000;
}
</style>