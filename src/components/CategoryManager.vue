<script setup>
import { ref, onMounted } from 'vue'
import { API_BASE_URL } from '../config'

const categories = ref([])
const statusMsg = ref('')
const errorMsg = ref('')

const showModal = ref(false)
const isEditMode = ref(false)
const deleteTarget = ref(null)

const form = ref({
  category_id: null,
  category_name: '',
})

const fetchCategories = async () => {
  const res = await fetch(`${API_BASE_URL}/categories_api.php`)
  const json = await res.json()
  if (json.success) categories.value = json.data
}

const openAddModal = () => {
  statusMsg.value = ''
  errorMsg.value = ''
  isEditMode.value = false
  form.value = { category_id: null, category_name: '' }
  showModal.value = true
}

const openEditModal = (cat) => {
  statusMsg.value = ''
  errorMsg.value = ''
  isEditMode.value = true
  form.value = { category_id: cat.category_id, category_name: cat.category_name }
  showModal.value = true
}

const saveCategory = async () => {
  errorMsg.value = ''
  const action = isEditMode.value ? 'update' : 'create'
  const res = await fetch(`${API_BASE_URL}/categories_api.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action, ...form.value }),
  })
  const result = await res.json()
  if (result.success) {
    statusMsg.value = result.message
    showModal.value = false
    await fetchCategories()
  } else {
    errorMsg.value = result.message || 'บันทึกไม่สำเร็จ'
  }
}

const confirmDelete = async () => {
  if (!deleteTarget.value) return
  const target = deleteTarget.value
  const res = await fetch(`${API_BASE_URL}/categories_api.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'delete', category_id: target.category_id }),
  })
  const result = await res.json()
  if (result.success) {
    statusMsg.value = `ลบประเภทสินค้า "${target.category_name}" เรียบร้อยแล้ว`
    deleteTarget.value = null
    await fetchCategories()
  } else {
    errorMsg.value = result.message || 'ลบไม่สำเร็จ'
    deleteTarget.value = null
  }
}

onMounted(fetchCategories)
</script>

<template>
  <div class="container mt-4 mb-5" style="max-width: 780px">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="mb-0">จัดการข้อมูลประเภทสินค้า</h4>
      <button class="btn btn-primary btn-sm" @click="openAddModal">+ เพิ่มประเภทสินค้า</button>
    </div>

    <div v-if="statusMsg" class="alert alert-success py-2">{{ statusMsg }}</div>
    <div v-if="errorMsg" class="alert alert-danger py-2">{{ errorMsg }}</div>

    <div class="table-responsive">
      <table class="table table-bordered table-striped table-hover align-middle">
        <thead class="table-primary">
          <tr>
            <th class="text-center" style="width: 110px">รหัสประเภท</th>
            <th>ชื่อประเภทสินค้า</th>
            <th class="text-center" style="width: 150px">การจัดการ</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="cat in categories" :key="cat.category_id">
            <td class="text-center">{{ cat.category_id }}</td>
            <td>{{ cat.category_name }}</td>
            <td class="text-center">
              <button class="btn btn-warning btn-sm me-1" @click="openEditModal(cat)">แก้ไข</button>
              <button class="btn btn-danger btn-sm" @click="deleteTarget = cat">ลบ</button>
            </td>
          </tr>
          <tr v-if="categories.length === 0">
            <td colspan="3" class="text-center text-muted py-3">ไม่มีข้อมูลประเภทสินค้า</td>
          </tr>
        </tbody>
      </table>
    </div>

    <p class="text-muted small mb-0">จำนวนประเภทสินค้าทั้งหมด {{ categories.length }} ประเภท</p>

    <!-- Modal เพิ่ม / แก้ไขประเภทสินค้า -->
    <div v-if="showModal" class="modal d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.45)">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEditMode ? 'แก้ไขประเภทสินค้า' : 'เพิ่มประเภทสินค้าใหม่' }}</h5>
            <button type="button" class="btn-close" @click="showModal = false"></button>
          </div>
          <form @submit.prevent="saveCategory">
            <div class="modal-body">
              <div class="mb-2">
                <label class="form-label">ชื่อประเภทสินค้า <span class="text-danger">*</span></label>
                <input
                  v-model="form.category_name"
                  type="text"
                  class="form-control"
                  placeholder="เช่น ของหวาน"
                  required
                />
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary btn-sm" @click="showModal = false">ยกเลิก</button>
              <button type="submit" class="btn btn-success btn-sm">
                {{ isEditMode ? 'บันทึกการแก้ไข' : 'บันทึกข้อมูล' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal ยืนยันการลบประเภทสินค้า -->
    <div v-if="deleteTarget" class="modal d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.45)">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">ยืนยันการลบประเภทสินค้า</h5>
            <button type="button" class="btn-close" @click="deleteTarget = null"></button>
          </div>
          <div class="modal-body">
            ต้องการลบประเภทสินค้า <strong>{{ deleteTarget.category_name }}</strong> ใช่หรือไม่?
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
