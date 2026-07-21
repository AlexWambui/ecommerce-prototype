<template>
  <AuthenticatedLayout>
    <div class="orders-container">
      <h1>My Orders</h1>
      
      <div v-if="orders.data.length === 0" class="empty-orders">
        <p>You haven't placed any orders yet.</p>
        <Link :href="route('shop.index')" class="btn btn-primary">
          Start Shopping
        </Link>
      </div>
      
      <div v-else>
        <div v-for="order in orders.data" :key="order.id" class="order-card">
          <div class="order-header">
            <div>
              <strong>Order #{{ order.order_number }}</strong>
              <br>
              <small>{{ formatDate(order.created_at) }}</small>
            </div>
            <div>
              <span :class="['order-status', `status-${order.status}`]">
                {{ capitalize(order.status) }}
              </span>
            </div>
          </div>
          
          <div class="order-body">
            <p><strong>Items:</strong> {{ order.items.length }}</p>
            <p class="order-total">Total: ${{ formatPrice(order.total) }}</p>
          </div>
          
          <div class="order-actions">
            <Link :href="route('orders.show', order.id)" class="btn btn-primary">
              View Details
            </Link>
            
            <button 
              v-if="canCancel(order)"
              class="btn btn-danger"
              @click="cancelOrder(order.id)"
            >
              Cancel Order
            </button>
            
            <Link 
              v-if="order.tracking_number"
              :href="route('orders.track', order.id)" 
              class="btn btn-secondary"
            >
              Track Order
            </Link>
          </div>
        </div>
        
        <!-- Pagination -->
        <div class="pagination">
          <button 
            v-for="page in orders.last_page" 
            :key="page"
            class="page-btn"
            :class="{ active: page === orders.current_page }"
            @click="goToPage(page)"
          >
            {{ page }}
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { useToast } from '@/Composables/useToast'

const props = defineProps({
  orders: {
    type: Object,
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
    day: 'numeric'
  })
}

const capitalize = (str) => {
  return str.charAt(0).toUpperCase() + str.slice(1)
}

const canCancel = (order) => {
  return ['pending', 'processing'].includes(order.status)
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

const goToPage = (page) => {
  router.get(route('orders.index', { page }))
}
</script>

<style scoped>
.orders-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.empty-orders {
  text-align: center;
  padding: 50px 0;
}

.order-card {
  background: white;
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 10px;
}

.order-status {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 14px;
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

.order-body {
  margin: 15px 0;
}

.order-total {
  font-size: 18px;
  font-weight: bold;
}

.order-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.btn {
  padding: 8px 16px;
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

.pagination {
  display: flex;
  gap: 5px;
  justify-content: center;
  margin-top: 30px;
  flex-wrap: wrap;
}

.page-btn {
  padding: 8px 16px;
  border: 1px solid #ddd;
  background: white;
  cursor: pointer;
  border-radius: 4px;
  transition: all 0.2s;
}

.page-btn:hover {
  background: #f5f5f5;
}

.page-btn.active {
  background: #4CAF50;
  color: white;
  border-color: #4CAF50;
}
</style>