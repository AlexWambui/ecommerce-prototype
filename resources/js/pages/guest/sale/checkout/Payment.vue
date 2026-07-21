<template>
  <GuestLayout>
    <div class="checkout-container">
      <h1>Payment</h1>
      
      <div class="checkout-steps">
        <div class="step completed">✓ Email</div>
        <div class="step completed">✓ Shipping</div>
        <div class="step active">3. Payment</div>
      </div>
      
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
          <span>${{ formatPrice(shipping_cost) }}</span>
        </div>
        <div v-if="cart.discount > 0" class="summary-row discount">
          <span>Discount</span>
          <span>-${{ formatPrice(cart.discount) }}</span>
        </div>
        <div class="summary-row total">
          <span>Total</span>
          <span>${{ formatPrice(total) }}</span>
        </div>
      </div>
      
      <form @submit.prevent="submitPayment" class="payment-form">
        <div class="form-group">
          <label>Payment Method</label>
          <div class="payment-methods">
            <label class="payment-option">
              <input
                type="radio"
                value="card"
                v-model="form.payment_method"
              />
              <span>Credit / Debit Card</span>
            </label>
            
            <label class="payment-option">
              <input
                type="radio"
                value="mpesa"
                v-model="form.payment_method"
              />
              <span>M-Pesa</span>
            </label>
            
            <label class="payment-option">
              <input
                type="radio"
                value="paypal"
                v-model="form.payment_method"
              />
              <span>PayPal</span>
            </label>
          </div>
        </div>
        
        <!-- Card Fields -->
        <div v-if="form.payment_method === 'card'" class="card-fields">
          <div class="form-group">
            <label for="card_number">Card Number</label>
            <input
              id="card_number"
              type="text"
              v-model="form.card_number"
              placeholder="1234 5678 9012 3456"
              maxlength="16"
              :disabled="loading"
            />
            <div v-if="errors.card_number" class="error">{{ errors.card_number }}</div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="card_expiry">Expiry (MM/YY)</label>
              <input
                id="card_expiry"
                type="text"
                v-model="form.card_expiry"
                placeholder="12/25"
                maxlength="5"
                :disabled="loading"
              />
              <div v-if="errors.card_expiry" class="error">{{ errors.card_expiry }}</div>
            </div>
            
            <div class="form-group">
              <label for="card_cvc">CVC</label>
              <input
                id="card_cvc"
                type="password"
                v-model="form.card_cvc"
                placeholder="123"
                maxlength="3"
                :disabled="loading"
              />
              <div v-if="errors.card_cvc" class="error">{{ errors.card_cvc }}</div>
            </div>
          </div>
        </div>
        
        <!-- M-Pesa Fields -->
        <div v-if="form.payment_method === 'mpesa'" class="mpesa-fields">
          <div class="form-group">
            <label for="mpesa_phone">M-Pesa Phone Number</label>
            <input
              id="mpesa_phone"
              type="tel"
              v-model="form.mpesa_phone"
              placeholder="0712345678"
              :disabled="loading"
            />
            <small>You will receive a prompt on your phone to confirm payment</small>
            <div v-if="errors.mpesa_phone" class="error">{{ errors.mpesa_phone }}</div>
          </div>
        </div>
        
        <button type="submit" class="btn btn-primary" :disabled="loading">
          <span v-if="loading">
            <span class="spinner"></span>
            Processing...
          </span>
          <span v-else>Place Order - ${{ formatPrice(total) }}</span>
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
  shipping_cost: {
    type: Number,
    required: true
  },
  total: {
    type: Number,
    required: true
  },
  shipping_method: {
    type: String,
    required: true
  },
  step: {
    type: Number,
    required: true
  }
})

const toast = useToast()
const loading = ref(false)
const errors = ref({})

const form = ref({
  payment_method: 'card',
  card_number: '',
  card_expiry: '',
  card_cvc: '',
  mpesa_phone: ''
})

const submitPayment = async () => {
  loading.value = true
  errors.value = {}
  
  try {
    const response = await fetch(route('checkout.process.payment'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify(form.value)
    })
    
    const data = await response.json()
    
    if (data.success) {
      router.visit(data.redirect)
    } else {
      if (data.errors) {
        errors.value = data.errors
      } else {
        toast.error(data.message || 'Payment failed. Please try again.')
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

.step.completed {
  border-color: #4CAF50;
  color: #4CAF50;
  background: #f0fff0;
}

.cart-summary {
  background: #f5f5f5;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 30px;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
}

.summary-row.total {
  font-weight: bold;
  font-size: 20px;
  padding-top: 15px;
  border-top: 2px solid #333;
  margin-top: 10px;
}

.payment-form {
  background: white;
  padding: 30px;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.payment-methods {
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
}

.payment-option {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 20px;
  border: 2px solid #ddd;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.payment-option:hover {
  border-color: #4CAF50;
}

.payment-option input[type="radio"] {
  margin: 0;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.form-group {
  margin-bottom: 20px;
}

label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
}

input, select {
  width: 100%;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 16px;
}

input:focus, select:focus {
  outline: none;
  border-color: #4CAF50;
}

.card-fields, .mpesa-fields {
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid #eee;
}

small {
  display: block;
  color: #666;
  font-size: 12px;
  margin-top: 5px;
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
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
}

.btn:hover:not(:disabled) {
  background: #45a049;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.spinner {
  display: inline-block;
  width: 20px;
  height: 20px;
  border: 3px solid rgba(255,255,255,0.3);
  border-radius: 50%;
  border-top-color: white;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

@media (max-width: 768px) {
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .payment-methods {
    flex-direction: column;
  }
}
</style>