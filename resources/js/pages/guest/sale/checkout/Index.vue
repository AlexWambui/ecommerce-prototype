<template>
  <GuestLayout>
    <div class="checkout-container">
      <h1>Checkout</h1>
      
      <div class="checkout-steps">
        <div class="step active">1. Email</div>
        <div class="step">2. Shipping</div>
        <div class="step">3. Payment</div>
      </div>
      
      <div class="cart-summary-mini">
        <p>Items: {{ cart.items_count }}</p>
        <p>Total: ${{ formatPrice(cart.total) }}</p>
      </div>
      
      <form @submit.prevent="submitEmail" class="email-form">
        <div class="form-group">
          <label for="email">Email Address</label>
          <input
            id="email"
            type="email"
            v-model="form.email"
            placeholder="you@example.com"
            required
            :disabled="loading"
          />
          <div v-if="errors.email" class="error">{{ errors.email }}</div>
        </div>
        
        <button type="submit" class="btn btn-primary" :disabled="loading">
          <span v-if="loading">Processing...</span>
          <span v-else>Continue to Shipping</span>
        </button>
      </form>
    </div>
  </GuestLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { useToast } from '@/Composables/useToast'

const props = defineProps({
  cart: {
    type: Object,
    required: true
  },
  user: {
    type: Object,
    default: null
  }
})

const toast = useToast()
const loading = ref(false)
const errors = ref({})

const form = ref({
  email: props.user?.email || ''
})

const submitEmail = async () => {
  loading.value = true
  errors.value = {}
  
  try {
    const response = await fetch(route('checkout.email'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({ email: form.value.email })
    })
    
    const data = await response.json()
    
    if (data.success) {
      router.visit(data.redirect)
    } else {
      if (data.errors) {
        errors.value = data.errors
      } else {
        toast.error(data.message || 'Something went wrong')
      }
    }
  } catch (error) {
    toast.error('Network error. Please try again.')
  } finally {
    loading.value = false
  }
}

const formatPrice = (price) => {
  return Number(price).toFixed(2)
}
</script>

<style scoped>
.checkout-container {
  max-width: 600px;
  margin: 0 auto;
  padding: 40px 20px;
}

.checkout-steps {
  display: flex;
  justify-content: space-between;
  margin: 30px 0;
  position: relative;
}

.checkout-steps::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  height: 2px;
  background: #ddd;
  transform: translateY(-50%);
  z-index: 0;
}

.step {
  background: white;
  padding: 10px 20px;
  border-radius: 20px;
  border: 2px solid #ddd;
  z-index: 1;
  font-weight: bold;
  color: #999;
}

.step.active {
  border-color: #4CAF50;
  color: #4CAF50;
  background: #f0fff0;
}

.cart-summary-mini {
  background: #f5f5f5;
  padding: 15px;
  border-radius: 8px;
  margin-bottom: 30px;
  display: flex;
  justify-content: space-between;
}

.email-form {
  background: white;
  padding: 30px;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.form-group {
  margin-bottom: 20px;
}

label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
}

input[type="email"] {
  width: 100%;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 16px;
}

input[type="email"]:focus {
  outline: none;
  border-color: #4CAF50;
}

.error {
  color: #ff4444;
  font-size: 14px;
  margin-top: 5px;
}

.btn {
  width: 100%;
  padding: 15px;
  background: #4CAF50;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 18px;
  cursor: pointer;
  transition: background 0.2s;
}

.btn:hover:not(:disabled) {
  background: #45a049;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>