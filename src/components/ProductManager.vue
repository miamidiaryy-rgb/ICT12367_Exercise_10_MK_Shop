<script setup>
import { ref, computed, onMounted } from 'vue'
import { API_BASE_URL } from '../config'

const categories = ref([])
const products = ref([])
const filterCategoryId = ref('')
const statusMsg = ref('')
const errorMsg = ref('')

const showModal = ref(false)
const isEditMode = ref(false)
const deleteTarget = ref(null)

const form = ref({
  product_id: null,
  product_name: '',
  category_id: '',
  price: '',
  stock: '',
  image: '',
})

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
  if (!filterCategoryId.value) return products.value
  return products.value.filter((p) => Number(p.category_id) === Number(filterCategoryId.value))
})

const openAddModal = () => {
  statusMsg.value = ''
  errorMsg.value = ''
  isEditMode.value = false
  form.value = {
    product_id: null,
    product_name: '',
    category_id: '',
    price: '',
    stock: '',
    image: '',
  }
  showModal.value = true
}

const openEditModal = (item) => {
  statusMsg.value = ''
  errorMsg.value = ''
  isEditMode.value = true
  form.value = {
    product_id: item.product_id,
    product_name: item.product_name,
    category_id: item.category_id,
    price: item.price,
    stock: item.stock,
    image: item.image || '',
  }
  showModal.value = true
}

const saveProduct = async () => {
  errorMsg.value = ''
  const action = isEditMode.value ? 'update' : 'create'
  const res = await fetch(`${API_BASE_URL}/products_api.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      action,
      ...form.value,
      category_id: Number(form.value.category_id),
      price: Number(form.value.price || 0),
      stock: Number(form.value.stock || 0),
    }),
  })
  const result = await res.json()
  if (result.success) {
    statusMsg.value = result.message
    showModal.value = false
    await loadData()
  } else {
    errorMsg.value = result.message || 'บันทึกไม่สำเร็จ'
  }
}

const confirmDelete = async () => {
  if (!deleteTarget.value) return
  const target = deleteTarget.value
  const res = await fetch(`${API_BASE_URL}/products_api.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'delete', product_id: target.product_id }),
  })
  const result = await res.json()
  if (result.success) {
    statusMsg.value = `ลบสินค้า "${target.product_name}" เรียบร้อยแล้ว`
    deleteTarget.value = null
    await loadData()
  }
}

onMounted(loadData)
</script>

<template>
  <div class="container mt-4 mb-5">
    <h4 class="mb-3">รายการสินค้า</h4>

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
      <button class="btn btn-primary btn-sm" @click="openAddModal">เพิ่มสินค้า +</button>

      <div class="d-flex align-items-center gap-2">
        <label class="small mb-0">ประเภทสินค้า:</label>
        <select v-model="filterCategoryId" class="form-select form-select-sm" style="width: 160px">
          <option value="">ทั้งหมด</option>
          <option v-for="cat in categories" :key="cat.category_id" :value="cat.category_id">
            {{ cat.category_name }}
          </option>
        </select>
      </div>
    </div>

    <div v-if="statusMsg" class="alert alert-success py-2">{{ statusMsg }}</div>
    <div v-if="errorMsg" class="alert alert-danger py-2">{{ errorMsg }}</div>

    <div class="table-responsive">
      <table class="table table-bordered table-striped table-hover align-middle">
        <thead class="table-primary">
          <tr>
            <th class="text-center" style="width: 60px">ID</th>
            <th>ชื่อสินค้า</th>
            <th>ประเภท</th>
            <th class="text-end">ราคา</th>
            <th class="text-center">จำนวน</th>
            <th class="text-center" style="width: 95px">รูปภาพ</th>
            <th class="text-center" style="width: 140px">การจัดการ</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in filteredProducts" :key="item.product_id">
            <td class="text-center">{{ item.product_id }}</td>
            <td>{{ item.product_name }}</td>
            <td>{{ item.category_name }}</td>
            <td class="text-end">{{ formatPrice(item.price) }}</td>
            <td class="text-center">{{ item.stock }}</td>
            <td class="text-center">
              <img
                v-if="item.image"
                :src="imageUrl(item.image)"
                :alt="item.product_name"
                class="img-thumbnail"
                style="width: 58px; height: 44px; object-fit: cover"
              />
            </td>
            <td class="text-center">
              <button class="btn btn-warning btn-sm me-1" @click="openEditModal(item)">แก้ไข</button>
              <button class="btn btn-danger btn-sm" @click="deleteTarget = item">ลบ</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal เพิ่ม / แก้ไขสินค้า -->
    <div v-if="showModal" class="modal d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.45)">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEditMode ? 'แก้ไขสินค้า' : 'เพิ่มสินค้าใหม่' }}</h5>
            <button type="button" class="btn-close" @click="showModal = false"></button>
          </div>
          <form @submit.prevent="saveProduct">
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">ชื่อสินค้า</label>
                <input v-model="form.product_name" type="text" class="form-control" required />
              </div>
              <div class="mb-3">
                <label class="form-label">ประเภทสินค้า</label>
                <select v-model="form.category_id" class="form-select" required>
                  <option value="">-- เลือกประเภท --</option>
                  <option v-for="cat in categories" :key="cat.category_id" :value="cat.category_id">
                    {{ cat.category_name }}
                  </option>
                </select>
              </div>
              <div class="row mb-3">
                <div class="col-6">
                  <label class="form-label">ราคา</label>
                  <input v-model="form.price" type="number" step="0.01" min="0" class="form-control" required />
                </div>
                <div class="col-6">
                  <label class="form-label">จำนวน</label>
                  <input v-model="form.stock" type="number" min="0" class="form-control" required />
                </div>
              </div>
              <div class="mb-2">
                <label class="form-label">ชื่อไฟล์รูปภาพ</label>
                <input v-model="form.image" type="text" class="form-control" placeholder="เช่น watermelon.jpg" />
              </div>
              <div v-if="isEditMode && form.image" class="mt-2">
                <div class="small text-muted mb-1">รูปเดิม:</div>
                <img :src="imageUrl(form.image)" class="img-thumbnail" style="width: 72px; height: 54px; object-fit: cover" />
              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-success w-100">
                {{ isEditMode ? 'บันทึกการแก้ไข' : 'เพิ่มสินค้า' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal ยืนยันการลบสินค้า -->
    <div v-if="deleteTarget" class="modal d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.45)">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">ยืนยันการลบสินค้า</h5>
            <button type="button" class="btn-close" @click="deleteTarget = null"></button>
          </div>
          <div class="modal-body">
            ยืนยันการลบสินค้า <strong>{{ deleteTarget.product_name }}</strong> ใช่หรือไม่?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" @click="deleteTarget = null">ยกเลิก</button>
            <button type="button" class="btn btn-danger btn-sm" @click="confirmDelete">ยืนยันการลบ</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
