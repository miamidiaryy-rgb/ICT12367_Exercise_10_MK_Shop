<script setup>
import { ref, computed, onMounted } from 'vue'
import { API_BASE_URL } from '../config'

const categories = ref([])
const products = ref([])
const selectedCategoryId = ref(0) // 0 = ทั้งหมด
const selectedTable = ref('')
const cart = ref([])
const orderMsg = ref('')

const imageUrl = (img) => (img ? `${API_BASE_URL}/uploads/${img}` : '')

const formatPrice = (val) =>
  Number(val || 0).toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })

const loadData = async () => {
  const [catRes, prodRes] = await Promise.all([
    fetch(`${API_BASE_URL}/categories_api.php`),
    fetch(`${API_BASE_URL}/products_api.php`),
  ])
  const catJson = await catRes.json()
  const prodJson = await prodRes.json()
  if (catJson.success) categories.value = catJson.data
  if (prodJson.success) products.value = prodJson.data
}

const filteredProducts = computed(() => {
  if (!selectedCategoryId.value) return products.value
  return products.value.filter((p) => Number(p.category_id) === Number(selectedCategoryId.value))
})

const addToCart = (product) => {
  orderMsg.value = ''
  const existing = cart.value.find((c) => c.product_id === product.product_id)
  if (existing) {
    existing.qty += 1
  } else {
    cart.value.push({ ...product, qty: 1 })
  }
}

const totalAmount = computed(() =>
  cart.value.reduce((sum, item) => sum + Number(item.price) * item.qty, 0)
)

const confirmOrder = () => {
  if (!selectedTable.value) {
    orderMsg.value = 'กรุณาเลือกโต๊ะก่อนยืนยันคำสั่งซื้อ'
    return
  }
  orderMsg.value = `ส่งรายการสั่งซื้อสำหรับ ${selectedTable.value} เรียบร้อยแล้ว (รวม ${formatPrice(totalAmount.value)} บาท)`
  cart.value = []
}

onMounted(loadData)
</script>

<template>
  <div class="container mt-4 mb-5">
    <div class="text-center mb-3">
      <h4 class="fw-semibold mb-2">เมนูสินค้า</h4>
      <div class="d-inline-flex align-items-center gap-2 mb-3">
        <label class="form-label mb-0 small fw-semibold">เลือกโต๊ะ:</label>
        <select v-model="selectedTable" class="form-select form-select-sm" style="width: 150px">
          <option value="">-- เลือกโต๊ะ --</option>
          <option v-for="n in 10" :key="n" :value="`โต๊ะ ${n}`">โต๊ะ {{ n }}</option>
        </select>
      </div>

      <!-- ปุ่มเลือกดูสินค้าตามประเภท -->
      <div class="d-flex justify-content-center flex-wrap gap-2">
        <button
          v-for="cat in categories"
          :key="cat.category_id"
          type="button"
          class="btn btn-sm"
          :class="
            selectedCategoryId === cat.category_id
              ? 'btn-success'
              : 'btn-outline-secondary'
          "
          @click="selectedCategoryId = cat.category_id"
        >
          {{ cat.category_name }}
        </button>
        <button
          type="button"
          class="btn btn-sm"
          :class="selectedCategoryId === 0 ? 'btn-success' : 'btn-outline-secondary'"
          @click="selectedCategoryId = 0"
        >
          ทั้งหมด
        </button>
      </div>
    </div>

    <div v-if="orderMsg" class="alert alert-info py-2 text-center">{{ orderMsg }}</div>

    <!-- รายการการ์ดสินค้า -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3">
      <div v-for="item in filteredProducts" :key="item.product_id" class="col">
        <div class="card h-100 text-center">
          <img
            :src="imageUrl(item.image)"
            :alt="item.product_name"
            class="card-img-top"
            style="height: 135px; object-fit: cover"
          />
          <div class="card-body py-2 px-3 d-flex flex-column">
            <div class="fw-semibold small mb-1">{{ item.product_name }}</div>
            <div class="text-muted small mb-1">{{ item.category_name }}</div>
            <div class="small mb-2">{{ formatPrice(item.price) }} บาท</div>
            <button
              class="btn btn-success btn-sm mt-auto mx-auto px-3"
              @click="addToCart(item)"
            >
              สั่งซื้อ
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- สรุปรายการที่สั่งซื้อ -->
    <div v-if="cart.length > 0" class="card mt-4">
      <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <span class="fw-semibold">รายการที่เลือก ({{ cart.length }} เมนู)</span>
        <span class="fw-semibold">รวมทั้งสิ้น {{ formatPrice(totalAmount) }} บาท</span>
      </div>
      <div class="card-body py-2">
        <div
          v-for="c in cart"
          :key="c.product_id"
          class="d-flex justify-content-between small py-1 border-bottom"
        >
          <span>{{ c.product_name }} x {{ c.qty }}</span>
          <span>{{ formatPrice(Number(c.price) * c.qty) }} บาท</span>
        </div>
        <div class="text-end mt-2">
          <button class="btn btn-primary btn-sm" @click="confirmOrder">ยืนยันการสั่งซื้อ</button>
        </div>
      </div>
    </div>
  </div>
</template>
